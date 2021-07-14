<?php
namespace Boxalino\RealTimeUserExperience\Framework;

use Boxalino\RealTimeUserExperienceApi\Framework\ApiPropertyTrait;
use Boxalino\RealTimeUserExperienceApi\Service\Api\Request\RequestInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Trait FilterablePropertyTrait
 * Common context functions to get the names for the filterable properties
 *
 * @package Boxalino\RealTimeUserExperience\Framework\Request
 */
trait FilterablePropertyTrait
{

    use ApiPropertyTrait;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param RequestInterface $request
     * @return array
     */
    protected function getStoreFilterablePropertiesByRequest(RequestInterface $request) : array
    {
        return array_diff($this->getFilterablePropertyGroupNames(), $this->getSelectedFacetsByRequest($request));
    }

    /**
     * @return array
     */
    public function getFilterablePropertyGroupNames() : array
    {
        return $this->getFilterablePropertyGroupSQL()->execute()->fetchAll(FetchMode::COLUMN);
    }

    /**
     * Query to access all filterable properties from the setup
     * And the name (as exported to Boxalino)
     *
     * @return QueryBuilder
     */
    protected function getFilterablePropertyGroupSQL() : QueryBuilder
    {
        $field = $this->getPropertySQLReplaceCondition("pgt.name");
        $query = $this->connection->createQueryBuilder();
        $query->select(["$field"])
            ->from("property_group", 'pg')
            ->leftJoin("pg", "property_group_translation", "pgt",
                "pg.id = pgt.property_group_id AND pgt.language_id=:defaultLanguageId"
            )
            ->andWhere("pg.filterable = 1")
            ->addGroupBy('pg.id')
            ->setParameter('defaultLanguageId', Uuid::fromHexToBytes($this->getDefaultSalesChannelLanguageId()), ParameterType::BINARY);

        return $query;
    }

    /**
     * @return string
     */
    abstract function getDefaultSalesChannelLanguageId() : string;


}
