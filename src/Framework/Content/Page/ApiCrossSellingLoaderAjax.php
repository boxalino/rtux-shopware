<?php
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperience\Framework\Content\BxAttributeElement;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiCrosssellingModel;
use Boxalino\RealTimeUserExperience\Framework\Content\Listing\ApiEntityCollectionModel;
use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\ApiCallServiceInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\Accessor\Block;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Response\ApiResponseViewInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Util\ConfigurationInterface;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\CrossSelling\AbstractProductCrossSellingRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ApiCrossSellingLoaderAjax
 *
 * The default ApiLoader is extended in order to allow further development&transformation to process a cross-selling integration
 * to be used as base for the subscriber
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
class ApiCrossSellingLoaderAjax extends ApiGenericCrossSellingLoader
    implements ApiLoaderInterface
{

    private AbstractProductCrossSellingRoute $crossSellingRoute;

    public function __construct(
        ApiCallServiceInterface $apiCallService,
        ConfigurationInterface $configuration,
        SalesChannelRepository $productRepository,
        AbstractProductCrossSellingRoute $crossSellingRoute
    )
    {
        parent::__construct($apiCallService, $configuration, $productRepository);
        $this->crossSellingRoute = $crossSellingRoute;
    }

    /**
     * - Make the API request
     * - Load all products required for the FE
     * - Create the product collection based on each tabs` hitIds
     * - Return content
     *
     * @return ApiLoaderInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function load(): ApiLoaderInterface
    {
        try {
            $this->addItemContextOnApiContext();
            $this->addAvailableCrossSellToApiRequest();
            $this->call();
            $this->prepareCrossSellingResponseCollection();
            $this->prepareCrossellingResultByApiResponse();
        } catch (\Throwable $exception) {
            throw $exception;
        }

        return $this;
    }

    /**
     * @return ApiResponseViewInterface|null
     */
    public function getApiResponsePage(): ?ApiResponseViewInterface
    {
        if(!$this->apiResponsePage)
        {
            $this->apiResponsePage = new ApiCrosssellingModel();
        }

        return $this->apiResponsePage;
    }

    /**
     * Create the SW6.* Crossselling collections (version-dependent)
     * (The products have all been priorly loaded in a generic collection to optimize the process)
     */
    protected function prepareCrossellingResultByApiResponse() : void
    {
        foreach ($this->getApiResponse()->getBlocks() as $order => $block) {
            /**
             * the logic on validating the child block
             * if the cross-selling narrative is properly structured - the ProductsCollection and Model with Entities
             * will be valid content
             */
            /** @var Block $block */
            if (property_exists($block, "model")
                && $block->getModel() instanceof ApiEntityCollectionModel
                && property_exists($block, "bxHits")
            ) {
                $type = $block->getType();
                if (is_array($type)) {
                    $type = $type[0];
                }
                $productCollection = $this->getCrossSellCollectionByType($type);
                if (is_null($productCollection)) {
                    $this->getApiResponse()->getBlocks()->offsetUnset($order);
                    continue;
                }

                if ($productCollection->count() > 0)
                {
                    $block->set("collection", $productCollection);
                }

                /** This is added as to be compliant with the other integration guidelines template changes */
                $block->set("bxAttributeElement", new BxAttributeElement($block->getBxAttributes()));
            }
        }
    }

    /**
     * Loading the product`s cross-selling
     * The route is cached so it should be production-efficient
     */
    protected function addAvailableCrossSellToApiRequest() : void
    {
        if($this->getApiContext()->useConfiguredProductsAsContextParameters())
        {
            $crossSellings = $this->crossSellingRoute->load($this->getApiContext()->getProductId(), new Request(), $this->getSalesChannelContext(), new Criteria());
            $this->updateApiContextByCrosssellingCollection($crossSellings->getResult());
        }
    }


}
