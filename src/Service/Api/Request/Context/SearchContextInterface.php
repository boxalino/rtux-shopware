<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request\Context;

use Boxalino\RealTimeUserExperience\Framework\Request\SearchContextAbstract;
use Boxalino\RealTimeUserExperience\Service\Api\Request\ContextInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface SearchContextInterface
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface SearchContextInterface extends ContextInterface
{

    /**
     * @return int|null
     */
    public function getSubPhrasesCount(): ?int;

    /**
     * @param int $subPhrasesCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesCount(int $subPhrasesCount): SearchContextAbstract;

    /**
     * @return int|null
     */
    public function getSubPhrasesProductsCount(): ?int;

    /**
     * @param int $subPhrasesProductsCount
     * @return SearchContextAbstract
     */
    public function setSubPhrasesProductsCount(int $subPhrasesProductsCount): SearchContextAbstract;
}
