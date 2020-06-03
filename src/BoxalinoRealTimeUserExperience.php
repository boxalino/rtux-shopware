<?php
namespace Boxalino\RealTimeUserExperience;

use Boxalino\RealTimeUserExperienceApi\BoxalinoRealTimeUserExperienceApi;
use Shopware\Core\Framework\Parameter\AdditionalBundleParameters;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;

/**
 * Class BoxalinoRealTimeUserExperience - framework starter
 *
 * @package Boxalino
 */
class BoxalinoRealTimeUserExperience extends Plugin
{

    public function install(InstallContext $installContext): void
    {
    }

    /**
     * Adding the Boxalino API bundle
     *
     * @param AdditionalBundleParameters $parameters
     * @return array
     */
    public function getAdditionalBundles(AdditionalBundleParameters $parameters): array
    {
        return array_merge(parent::getAdditionalBundles($parameters), [ new BoxalinoRealTimeUserExperienceApi()]);
    }

    public function activate(ActivateContext $context) : void
    {
    }

    public function update(UpdateContext $updateContext): void
    {
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->removeConfiguration($uninstallContext->getContext());
    }

    protected function removeConfiguration(Context $context): void
    {
        /** @var EntityRepositoryInterface $systemConfigRepository */
        $systemConfigRepository = $this->container->get('system_config.repository');
        $criteria = (new Criteria())->addFilter(new ContainsFilter('configurationKey', $this->getName() . '.config.'));
        $idSearchResult = $systemConfigRepository->searchIds($criteria, $context);

        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $idSearchResult->getIds());

        if ($ids === []) {
            return;
        }

        $systemConfigRepository->delete($ids, $context);
    }

}

