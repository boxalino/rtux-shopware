<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Framework\Request;

use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\Context\ListingContextInterface;
use \Boxalino\RealTimeUserExperienceApi\Framework\Request\ListingContextAbstract as ApiListingContextAbstract;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\ParameterFactoryInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestDefinitionInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestTransformerInterface;
use Doctrine\DBAL\Connection;

/**
 * Boxalino Listing Request handler
 * Allows to set the nr of subphrases and products returned on each subphrase hit
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
abstract class ListingContextAbstract
    extends ApiListingContextAbstract
    implements ShopwareApiContextInterface, ListingContextInterface
{
    use ContextTrait;
    use ListingContextFilterablePropertiesTrait;

    public function __construct(
        RequestTransformerInterface $requestTransformer,
        ParameterFactoryInterface $parameterFactory,
        Connection $connection
    ) {
        parent::__construct($requestTransformer, $parameterFactory);
        $this->connection = $connection;
    }

    
}
