<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Listing;

use Boxalino\RealTimeUserExperience\Framework\FilterablePropertyTrait;
use Boxalino\RealTimeUserExperience\Framework\SalesChannelContextTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorFacetModelInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\AccessorModelInterface;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Listing\ApiFacetModelAbstract;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Class ApiFacetModel
 *
 * Item refers to any data model/logic that is desired to be rendered/displayed
 * The integrator can decide to either use all data as provided by the Narrative API,
 * or to design custom data layers to represent the fetched content
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Listing
 */
class ApiFacetModel extends ApiFacetModelAbstract
    implements AccessorFacetModelInterface
{
    use SalesChannelContextTrait;
    use FilterablePropertyTrait;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $filterableProperties = [];


    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    /**
     * Preparing element for API preview (ex: pwa context)
     */
    public function load(): void
    {
        $this->_loadAccessors();
        $this->loadPropertiesToObject(
            $this,
            ["salesChannelContext", "contextId", "defaultSalesChannelLanguageId"],
            ["getLabel", "addSelectedFacet", "getByPosition", "_loadAccessors", "getFacetsPrefix", "getValue", "facetRequiresPrefix"],
            true
        );
    }

    /**
     * @param $facet
     * @return bool
     */
    protected function facetRequiresPrefix($facet) : bool
    {
        if(empty($this->filterableProperties))
        {
            $this->filterableProperties = $this->getFilterablePropertyNames();
        }

        if(in_array($facet->getField(), $this->filterableProperties))
        {
            return false;
        }

        return true;
    }

    /**
     * Sets the facets
     * Sets the accesor handler to be able to run toObject construct
     *
     * @param null | AccessorInterface $context
     * @return AccessorModelInterface
     */
    public function addAccessorContext(?AccessorInterface $context = null): AccessorModelInterface
    {
        $this->setSalesChannelContext($context->getAccessorHandler()->getSalesChannelContext());
        return parent::addAccessorContext($context);
    }

    /**
     * Accessing translation for the property name from DB
     *
     * @param string $propertyName
     * @return string
     */
    public function getLabel(string $propertyName) : string
    {
        $this->getDefaultLanguageId();
        $channelSelectedLanguage = $this->getSalesChannelContext()->getContext()->getLanguageId();
        $propertyId = $this->getPropertyIdByFieldName($propertyName);
        if(!$propertyId)
        {
            if(strpos($propertyName, $this->getFacetPrefix())===0)
            {
                $propertyName = substr($propertyName, strlen($this->getFacetPrefix()), strlen($propertyName));
            }

            return ucwords(str_replace("_", " ", $propertyName));
        }

        $query = $this->connection->createQueryBuilder()
            ->select(["IF(property_group_translation.name IS NULL, pgt.name, property_group_translation.name) AS name"])
            ->from("property_group_translation")
            ->leftJoin("property_group_translation", "property_group_translation", "pgt",
                "property_group_translation.property_group_id = pgt.property_group_id AND pgt.language_id=:defaultLanguageId")
            ->where("property_group_translation.language_id = :languageId")
            ->andWhere('property_group_translation.property_group_id = :propertyId')
            ->groupBy("property_group_translation.property_group_id")
            ->setParameter("languageId", Uuid::fromHexToBytes($channelSelectedLanguage), ParameterType::BINARY)
            ->setParameter("defaultLanguageId", Uuid::fromHexToBytes($this->getDefaultLanguageId()), ParameterType::BINARY)
            ->setParameter("propertyId", Uuid::fromHexToBytes($propertyId), ParameterType::BINARY)
            ->setMaxResults(1);

        return $query->executeQuery()->fetchOne();
    }

    /**
     * Accessing the property ID for the default channel
     *
     * @param string $propertyName
     * @return false|string
     * @throws \Shopware\Core\Framework\Uuid\Exception\InvalidUuidException
     */
    protected function getPropertyIdByFieldName(string $propertyName)
    {
        $condition = $this->getPropertySQLReplaceCondition("name");
        $propertyIdQuery = $this->connection->createQueryBuilder()
            ->select(["LOWER(HEX(property_group_id))"])
            ->from("property_group_translation")
            ->where("language_id = :defaultLanguageId")
            ->andWhere("$condition = :propertyName")
            ->setParameter("defaultLanguageId", Uuid::fromHexToBytes($this->getDefaultLanguageId()), ParameterType::STRING)
            ->setParameter("propertyName", $propertyName)
            ->setMaxResults(1);

        return $propertyIdQuery->executeQuery()->fetchOne();
    }

    /**
     * Accessing the sales channel default language to get the property name
     *
     * @return string
     * @throws \Shopware\Core\Framework\Uuid\Exception\InvalidUuidException
     */
    protected function getDefaultLanguageId() : string
    {
        if(is_null($this->defaultLanguageId))
        {
            $query = $this->connection->createQueryBuilder()
                ->select(["LOWER(HEX(language_id)) as language_id"])
                ->from('sales_channel')
                ->where('id = :channelId')
                ->setParameter("channelId", Uuid::fromHexToBytes($this->getSalesChannelContext()->getSalesChannel()->getId()), ParameterType::STRING)
                ->setMaxResults(1);

            $this->defaultLanguageId = $query->executeQuery()->fetchOne();
        }

        return $this->defaultLanguageId;
    }


}
