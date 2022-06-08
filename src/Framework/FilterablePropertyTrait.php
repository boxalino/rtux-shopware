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
     * @return array
     */
    public function getFilterablePropertyNames() : array
    {
        return $this->getFilterablePropertyGroupNames();
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
                "pg.id = pgt.property_group_id"
            )
            ->leftJoin("pg", "sales_channel", "sc", "sc.id=:contextId")
            ->andWhere("pg.filterable = 1")
            ->andWhere("pgt.language_id=sc.language_id")
            ->addGroupBy('pg.id')
            ->setParameter('contextId', Uuid::fromHexToBytes($this->getContextId()), ParameterType::BINARY);

        return $query;
    }

    /**
     * @return string
     */
    abstract function getContextId() : string;


}
