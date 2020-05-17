<?php declare(strict_types=1);
namespace Boxalino\RealTimeUserExperience\Service\Api\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestTransformerInterface
 *
 * Adapts the framework request to a boxalino data contract
 * Sets request variables dependent on the channel
 * (account, credentials, environment details -- language, dev, test, session, header parameters, etc)
 *
 * @package Boxalino\RealTimeUserExperience\Service\Api\Request
 */
interface RequestTransformerInterface
{

    /**
     * Sets context parameters (credentials, server, etc)
     * Adds parameters per request query elements
     *
     * @param Request $request
     * @return RequestDefinitionInterface
     */
    public function transform(Request $request): RequestDefinitionInterface;

    /**
     * @param Request $request
     * @return string
     */
    public function getCustomerId(Request $request) : string;

    /**
     * The value stored in the CEMS cookie
     */
    public function getSessionId(Request $request);

    /**
     * The value stored in the CEMV cookie
     */
    public function getProfileId(Request $request);

    /**
     * @param RequestDefinitionInterface $requestDefinition
     * @return $this
     */
    public function setRequestDefinition(RequestDefinitionInterface $requestDefinition);

    /**
     * @return RequestDefinitionInterface
     */
    public function getRequestDefinition() : RequestDefinitionInterface;

}
