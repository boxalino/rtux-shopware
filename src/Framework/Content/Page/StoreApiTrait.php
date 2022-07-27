<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Content\Page;

use Boxalino\RealTimeUserExperienceApi\Framework\Content\Page\ApiLoaderInterface;

/**
 * Class StoreApiTrait
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Content\Page
 */
trait StoreApiTrait 
{
    /**
     * @var string
     */
    protected $storeApiEndpoint = "store-api";

    /**
     * @return bool
     */
    public function isStoreApiRequest() : bool
    {
        try{
            $userUrl = $this->getRequest()->getUserUrl();
            if(strpos($userUrl, $this->getStoreApiEndpoint()) > -1)
            {
                return true;
            }
        } catch (\Throwable $exception)
        {
            return false;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getStoreApiEndpoint(): string
    {
        return $this->storeApiEndpoint;
    }

    /**
     * @param string $storeApiEndpoint
     * @return ApiLoaderInterface
     */
    public function setStoreApiEndpoint(string $storeApiEndpoint): ApiLoaderInterface
    {
        $this->storeApiEndpoint = $storeApiEndpoint;
        return $this;
    }


}
