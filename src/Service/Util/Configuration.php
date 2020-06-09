<?php
namespace Boxalino\RealTimeUserExperience\Service\Util;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Psr\Log\LoggerInterface;

/**
 * Class Configuration
 * General Boxalino configuration accessor
 *
 * @package Boxalino\RealTimeUserExperience\Service\Util
 */
class Configuration
{
    CONST BOXALINO_FRAMEWORK_CONFIG_KEY = "BoxalinoRealTimeUserExperience";

    /**
     * @var SystemConfigService
     */
    protected $systemConfigService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param SystemConfigService $systemConfigService
     * @param \Psr\Log\LoggerInterface $boxalinoLogger
     */
    public function __construct(
        SystemConfigService $systemConfigService,
        Connection $connection,
        LoggerInterface $boxalinoLogger
    ) {
        $this->systemConfigService = $systemConfigService;
        $this->connection = $connection;
        $this->logger = $boxalinoLogger;
    }

    public function getPluginConfigByChannelId($id)
    {
        if(empty($this->config) || !isset($this->config[$id]))
        {
            $allConfig = $this->systemConfigService->all($id);
            $this->config[$id] = $allConfig[self::BOXALINO_FRAMEWORK_CONFIG_KEY]['config'];
        }

        return $this->config[$id];
    }

    /**
     * @param string $languageId
     * @return string
     */
    public function getLanguageCode(string $languageId) : string
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(["locale.code AS 'code'"])
            ->from('language')
            ->leftJoin(
                'language',
                'locale',
                'locale',
                'language.locale_id = locale.id'
            )
            ->addGroupBy('language.locale_id')
            ->andWhere('language.id = :languageId')
            ->setParameter('languageId', Uuid::fromHexToBytes($languageId), ParameterType::BINARY);

        $code = $query->execute()->fetchColumn();

        return substr($code, 0, 2);
    }

}
