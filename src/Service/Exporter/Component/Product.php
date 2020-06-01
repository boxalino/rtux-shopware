<?php
namespace Boxalino\RealTimeUserExperience\Service\Exporter\Component;

use Boxalino\RealTimeUserExperience\Service\Exporter\ExporterScheduler;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\ItemsAbstract;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Manufacturer;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Category;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Media;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Option;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Price;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\PriceAdvanced;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Property;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Translation;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Url;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Review;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Visibility;
use Boxalino\RealTimeUserExperience\Service\Exporter\Util\Configuration;
use Boxalino\RealTimeUserExperience\Service\Exporter\Item\Tag;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Google\Auth\Cache\Item;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class Product
 * Product component exporting logic
 *
 * @package Boxalino\RealTimeUserExperience\Service\Exporter\Component
 */
class Product extends ExporterComponentAbstract
{

    CONST EXPORTER_LIMIT = 10000000;
    CONST EXPORTER_STEP = 10000;
    CONST EXPORTER_DATA_SAVE_STEP = 1000;

    CONST EXPORTER_COMPONENT_MAIN_FILE = "products.csv";
    CONST EXPORTER_COMPONENT_TYPE = "products";
    CONST EXPORTER_COMPONENT_ID_FIELD = "id";

    protected $lastExport;
    protected $exportedProductIds = [];
    protected $deltaIds = [];

    /**
     * @var Category
     */
    protected $categoryExporter;

    /**
     * @var Property
     */
    protected $facetExporter;

    /**
     * @var Option
     */
    protected $optionExporter;

    /**
     * @var Media
     */
    protected $imagesExporter;

    /**
     * @var Manufacturer
     */
    protected $manufacturerExporter;

    /**
     * @var Price
     */
    protected $priceExporter;

    /**
     * @var PriceAdvanced
     */
    protected $priceAdvancedExporter;

    /**
     * @var Url
     */
    protected $urlExporter;

    /**
     * @var Review
     */
    protected $reviewsExporter;

    /**
     * @var Translation
     */
    protected $translationExporter;

    /**
     * @var Tag
     */
    protected $tagExporter;

    /**
     * @var Visibility
     */
    protected $visibilityExporter;

    /**
     * @var \ArrayObject
     */
    protected $itemExportersList;

    public function __construct(
        ComponentResource $resource,
        Connection $connection,
        LoggerInterface $boxalinoLogger,
        Configuration $exporterConfigurator,
        Category $categoryExporter,
        Property $facetExporter,
        Option $optionExporter,
        Media $imagesExporter,
        Manufacturer $manufacturerExporter,
        Price $priceExporter,
        PriceAdvanced $priceAdvanced,
        Url $urlExporter,
        Review $reviewsExporter,
        Translation $translationExporter,
        Tag $tagExporter,
        Visibility $visibilityExporter
    ){
        $this->itemExportersList = new \ArrayObject();
        $this->optionExporter = $optionExporter;
        $this->priceAdvancedExporter = $priceAdvanced;
        $this->categoryExporter = $categoryExporter;
        $this->facetExporter = $facetExporter;
        $this->imagesExporter = $imagesExporter;
        $this->manufacturerExporter = $manufacturerExporter;
        $this->priceExporter = $priceExporter;
        $this->urlExporter = $urlExporter;
        $this->reviewsExporter = $reviewsExporter;
        $this->translationExporter = $translationExporter;
        $this->tagExporter = $tagExporter;
        $this->visibilityExporter = $visibilityExporter;

        parent::__construct($resource, $connection, $boxalinoLogger, $exporterConfigurator);
    }


    public function exportComponent()
    {
        /** defaults */
        $header = true; $data = []; $totalCount = 0; $page = 1; $exportFields=[]; $startExport = microtime(true);
        $this->logger->info("BxIndexLog: Preparing products - MAIN.");
        $properties = $this->getFields();
        $rootCategoryId = $this->config->getChannelRootCategoryId($this->getAccount());
        $defaultLanguageId = $this->config->getChannelDefaultLanguageId($this->getAccount());

        while (self::EXPORTER_LIMIT > $totalCount + self::EXPORTER_STEP)
        {
            $query = $this->connection->createQueryBuilder();
            $query->select($properties)
                ->from('product', 'p')
                ->leftJoin("p", 'product', 'parent',
                    'p.parent_id = parent.id AND p.parent_version_id = parent.version_id')
                ->leftJoin('p', 'tax', 'tax', 'tax.id = p.tax_id')
                ->leftJoin('p', 'delivery_time_translation', 'delivery_time_translation',
                    'p.delivery_time_id = delivery_time_translation.delivery_time_id AND delivery_time_translation.language_id = :defaultLanguage')
                ->leftJoin('p', 'unit_translation', 'unit_translation', 'unit_translation.unit_id = p.unit_id AND unit_translation.language_id = :defaultLanguage')
                ->leftJoin('p', 'currency', 'currency', "JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(p.price, '$.*.currencyId'),'$[0]')) = LOWER(HEX(currency.id))")
                ->andWhere('p.version_id = :live')
                ->andWhere("JSON_SEARCH(p.category_tree, 'one', :channelRootCategoryId) IS NOT NULL")
                ->addGroupBy('p.id')
                ->orderBy('p.created_at', 'DESC')
                ->setParameter('live', Uuid::fromHexToBytes(Defaults::LIVE_VERSION), ParameterType::BINARY)
                ->setParameter('channelRootCategoryId', $rootCategoryId, ParameterType::STRING)
                ->setParameter('defaultLanguage', Uuid::fromHexToBytes($defaultLanguageId), ParameterType::BINARY)
                ->setFirstResult(($page - 1) * self::EXPORTER_STEP)
                ->setMaxResults(self::EXPORTER_STEP);

            if ($this->getIsDelta()) {
                $query->andWhere('p.updated_at > :lastExport')
                    ->setParameter('lastExport', $this->getLastExport());
            }
            $count = $query->execute()->rowCount();
            $totalCount+=$count;
            if($totalCount == 0)
            {
                break; #return false;
            }
            $results = $this->processExport($query);
            foreach($results as $row)
            {
                if ($this->getIsDelta() && !isset($this->deltaIds[$row['id']])) {
                    $this->deltaIds[$row['id']] = $row['id'];
                }

                $this->exportedProductIds[] = $row['id'];
                $row['purchasable'] = $this->getProductPurchasableValue($row);
                $row['immediate_delivery'] = $this->getProductImmediateDeliveryValue($row);
                if($header)
                {
                    $exportFields = array_keys($row); $this->setHeaderFields($exportFields); $data[] = $exportFields; $header = false;
                }
                $data[] = $row;
                if(count($data) > self::EXPORTER_DATA_SAVE_STEP)
                {
                    $this->getFiles()->savePartToCsv($this->getComponentMainFile(), $data);
                    $data = [];
                }
            }

            $this->getFiles()->savePartToCsv($this->getComponentMainFile(), $data);
            $data = []; $page++;
            if($totalCount < self::EXPORTER_STEP - 1) { break;}
        }

        $endExport =  (microtime(true) - $startExport) * 1000;
        $this->logger->info("BxIndexLog: MAIN PRODUCT DATA EXPORT TOOK: $endExport ms, memory: " . memory_get_usage(true));
        if($page==0)
        {
            $this->logger->info("BxIndexLog: NO PRODUCTS WERE FOUND FOR THE EXPORT.");
            $this->setSuccessOnComponentExport(false);
            return $this;
        }

        $this->defineProperties($exportFields);

        $this->logger->info("BxIndexLog: -- Main product after memory: " . memory_get_usage(true));
        $this->logger->info("BxIndexLog: Finished products - main.");

        $this->setSuccessOnComponentExport(true);
        $this->exportItems();
    }

    /**
     * Export other product elements and properties (categories, translations, etc)
     *
     * @return void
     * @throws \Exception
     */
    public function exportItems() : void
    {
        if (!$this->getSuccessOnComponentExport())
        {
            return;
        }

        $this->_exportExtra("categories", $this->categoryExporter);
        $this->_exportExtra("translations", $this->translationExporter);
        $this->_exportExtra("manufacturers", $this->manufacturerExporter);
        $this->_exportExtra("facets", $this->facetExporter);
        $this->_exportExtra("options", $this->optionExporter);
        $this->_exportExtra("prices", $this->priceExporter);
        $this->_exportExtra("advancedPrices", $this->priceAdvancedExporter);
        $this->_exportExtra("reviews", $this->reviewsExporter);
        $this->_exportExtra("tags", $this->tagExporter);
        $this->_exportExtra("visibility", $this->visibilityExporter);

        if ($this->config->exportProductImages($this->getAccount()))
        {
            $this->_exportExtra("media", $this->imagesExporter);
        }

        if ($this->config->exportProductUrl($this->getAccount()))
        {
            $this->_exportExtra("urls", $this->urlExporter);
        }

        /** @var ItemsAbstract $itemExporter */
        foreach($this->itemExportersList as $itemExporter)
        {
            $this->_exportExtra($itemExporter->getPropertyName(), $itemExporter);
        }
    }

    /**
     * Contains the logic for exporting individual items describing the product component
     * (categories, translations, prices, reviews, etc..)
     * @param $step
     * @param $exporter
     */
    protected function _exportExtra($step, $exporter)
    {
        $this->logger->info("BxIndexLog: Preparing products - {$step}.");
        $exporter->setAccount($this->getAccount())
            ->setFiles($this->getFiles())
            ->setLibrary($this->getLibrary())
            ->setExportedProductIds($this->exportedProductIds);
        $exporter->export();
        $this->logger->info("BxIndexLog: MEMORY AFTER {$step}: " . memory_get_usage(true));
    }

    /**
     * @param array $properties
     * @return void
     * @throws \Exception
     */
    public function defineProperties(array $properties) : void
    {
        $mainSourceKey = $this->getLibrary()->addMainCSVItemFile($this->getFiles()->getPath($this->getComponentMainFile()), $this->getComponentIdField());
        $this->getLibrary()->addSourceStringField($mainSourceKey, 'bx_purchasable', 'purchasable');
        $this->getLibrary()->addSourceStringField($mainSourceKey, 'immediate_delivery', 'immediate_delivery');
        $this->getLibrary()->addSourceStringField($mainSourceKey, 'bx_type', $this->getComponentIdField());
        $this->getLibrary()->addFieldParameter($mainSourceKey, 'bx_type', 'pc_fields', '"product"  AS final_value');
        $this->getLibrary()->addFieldParameter($mainSourceKey, 'bx_type', 'multiValued', 'false');

        foreach ($properties as $property)
        {
            if ($property == $this->getComponentIdField()) {
                continue;
            }

            if (in_array($property, $this->getNumberFields())) {
                $this->getLibrary()->addSourceNumberField($mainSourceKey, $property, $property);
                $this->getLibrary()->addFieldParameter($mainSourceKey, $property, 'multiValued', 'false');
                continue;
            }

            $this->getLibrary()->addSourceStringField($mainSourceKey, $property, $property);
            if (in_array($property, $this->getSingleValuedFields()))
            {
                $this->getLibrary()->addFieldParameter($mainSourceKey, $property, 'multiValued', 'false');
            }
        }
    }

    /**
     * @return array|string[]
     */
    public function getNumberFields() : array
    {
        return ["available_stock", "stock", "rating_average", "child_count", "purchasable", "immediate_delivery8"];
    }

    /**
     * @return array|string[]
     */
    public function getSingleValuedFields() : array
    {
        return ["parent_id", "release_date", "created_at", "updated_at", "product_number", "manufacturer_number", "ean", "group_id", "mark_as_topseller"];
    }

    /**
     * Getting a list of product attributes and the table it comes from
     * To be used in the general SQL select
     *
     * @return array
     * @throws \Exception
     */
    public function getFields() : array
    {
        return $this->getRequiredProperties();
    }

    /**
     * In order to ensure transparency for the CDAP integration
     *
     * @return array
     */
    public function getRequiredProperties(): array
    {
        return [
            'LOWER(HEX(p.id)) AS id', 'p.auto_increment', 'p.product_number', 'p.active', 'LOWER(HEX(p.parent_id)) AS parent_id',
            'IF(p.parent_id IS NULL, p.active, parent.active) AS bx_parent_active',
            'LOWER(HEX(p.tax_id)) AS tax_id',
            'LOWER(HEX(p.delivery_time_id)) AS delivery_time_id', 'LOWER(HEX(p.product_media_id)) AS product_media_id',
            'LOWER(HEX(p.cover)) AS cover', 'LOWER(HEX(p.unit_id)) AS unit_id', 'p.category_tree', 'p.option_ids',
            'p.property_ids',
            'IF(p.parent_id IS NULL, p.manufacturer_number, IF(p.manufacturer_number IS NULL, parent.manufacturer_number, p.manufacturer_number)) AS manufactuere_number',
            'IF(p.parent_id IS NULL, p.ean, IF(p.ean IS NULL, parent.ean, p.ean)) AS ean',
            'p.stock', 'p.available_stock', 'p.available',
            'IF(p.parent_id IS NULL, p.restock_time, IF(p.restock_time IS NULL, parent.restock_time, p.restock_time)) AS restock_time',
            'IF(p.parent_id IS NULL, p.is_closeout, IF(p.is_closeout IS NULL, parent.is_closeout, p.is_closeout)) AS is_closeout',
            'p.purchase_steps', 'p.max_purchase', 'p.min_purchase', 'p.purchase_unit', 'p.reference_unit',
            'IF(p.parent_id IS NULL, p.shipping_free, IF(p.shipping_free IS NULL, parent.shipping_free, p.shipping_free)) AS shipping_free',
            'IF(p.parent_id IS NULL, p.purchase_price, IF(p.purchase_price IS NULL, parent.purchase_price, p.purchase_price)) AS purchase_price',
            'IF(p.parent_id IS NULL, p.mark_as_topseller, IF(p.mark_as_topseller IS NULL, parent.mark_as_topseller, IF(p.available = 1, p.mark_as_topseller, parent.mark_as_topseller))) AS mark_as_topseller',
            'p.weight', 'p.height', 'p.length',
            'IF(p.parent_id IS NULL, p.release_date, IF(p.release_date IS NULL, parent.release_date, p.release_date)) AS release_date',
            'p.whitelist_ids', 'p.blacklist_ids', 'p.configurator_group_config', 'p.created_at', 'p.updated_at',
            'IF(p.parent_id IS NULL, p.rating_average, parent.rating_average) AS rating_average', 'p.display_group', 'p.child_count',
            'currency.iso_code AS currency', 'currency.factor AS currency_factor',
            'tax.tax_rate', 'delivery_time_translation.name AS delivery_time_name',
            'unit_translation.name AS unit_name', 'unit_translation.short_code AS unit_short_code',
            'IF(p.parent_id IS NULL, LOWER(HEX(p.id)), LOWER(HEX(p.parent_id))) AS group_id'
        ];
    }

    /**
     * @return string
     */
    protected function getLastExport()
    {
        if (empty($this->lastExport))
        {
            $this->lastExport = date("Y-m-d H:i:s", strtotime("-1 day"));
            $query = $this->connection->createQueryBuilder();
            $query->select(['export_date'])
                ->from('boxalino_export')
                ->andWhere('account = :account')
                ->andWhere('status = :status')
                ->orderBy('created_at', 'DESC')
                ->setMaxResults(1)
                ->setParameter('account', $this->getAccount(), ParameterType::STRING)
                ->setParameter('status', ExporterScheduler::BOXALINO_EXPORTER_STATUS_SUCCESS);
            $latestExport = $query->execute();
            if($latestExport['export_date'])
            {
                $this->lastExport = $latestExport['export_date'];
            }
        }

        return $this->lastExport;
    }

    /**
     * Product purchasable logic depending on the default filter
     *
     * @param $row
     * @return int
     */
    public function getProductPurchasableValue($row) : int
    {
        if($row['is_closeout'] == 1 && $row['stock'] == 0)
        {
            return 0;
        }

        return 1;
    }

    /**
     * Product immediate delivery logic as per default facet handler logic
     *
     * @see Shopware\Bundle\SearchBundleDBAL\FacetHandler\ImmediateDeliveryFacetHandler
     * @param $row
     * @return int
     */
    public function getProductImmediateDeliveryValue($row) : int
    {
        if($row['available_stock'] >= $row['min_purchase'])
        {
            return 1;
        }

        return 0;
    }

    /**
     * Group product value per solr logic
     *
     * @param $row
     * @return mixed
     */
    public function getProductGroupValue($row)
    {
        if(is_null($row['parent_id']))
        {
            return $row['id'];
        }

        return $row['parent_id'];
    }

    public function addItemExporter(ItemsAbstract $extraExporter)
    {
        $this->itemExportersList->append($extraExporter);
        return $this;
    }

}
