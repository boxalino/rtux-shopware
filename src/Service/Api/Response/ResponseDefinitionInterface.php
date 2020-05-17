<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Response;

use Boxalino\RealTimeUserExperience\Service\Api\Response\Accessor\AccessorInterface;
use Boxalino\RealTimeUserExperience\Service\Api\Util\AccessorHandlerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

interface ResponseDefinitionInterface
{

    /**
     * @return int
     */
    public function getHitCount() : int;

    /**
     * @return string|null
     */
    public function getRedirectUrl() : ?string;

    /**
     * @return bool
     */
    public function isCorrectedSearchQuery() : bool;

    /**
     * @return string|null
     */
    public function getCorrectedSearchQuery() : ?string;

    /**
     * @return bool
     */
    public function hasSearchSubPhrases() : bool;

    /**
     * @return string
     */
    public function getRequestId() : string;

    /**
     * @return string
     */
    public function getGroupBy() : string;

    /**
     * @return string
     */
    public function getVariantId() : string;

    /**
     * @return \ArrayIterator
     */
    public function getBlocks() : \ArrayIterator;

    /**
     * Debug and performance information
     *
     * @return array
     */
    public function getAdvanced() : array;

    /**
     * @return Response
     */
    public function getResponse() : Response;

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response);

    /**
     * @return string
     */
    public function getJson() : string;

    /**
     * @param \StdClass $data
     * @param AccessorInterface $model
     * @return mixed
     */
    public function toObject(\StdClass $data, AccessorInterface $model);

    /**
     * @return AccessorHandlerInterface
     */
    public function getAccessorHandler(): AccessorHandlerInterface;

    /**
     * Adding response positions for your integration
     * (ex: top, left, bottom, right,..)
     * This will make your content accessible in a structured way as the default "blocks" are accessed
     *
     * @param array $positions
     * @return mixed
     */
    public function addResponseSegments(array $positions);

    /**
     * @return array|string[]
     */
    public function getResponseSegments() : array;

}
