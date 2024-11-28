<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Block\Adminhtml\InventoryShippingAdminUi\Order\View;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutInterface;

class ShipButtonPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * ShipButtonPlugin constructor.
     * @param Registry $registry
     * @param DataPersistorInterface $dataPersistor
     * @param ModuleManager $moduleManager
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(
        Registry $registry,
        DataPersistorInterface $dataPersistor,
        ModuleManager $moduleManager,
        ObjectManagerInterface $objectManager,
        Config $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->registry = $registry;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @param BlockInterface $subject
     * @param LayoutInterface $layout
     * @return array
     */
    public function beforeSetLayout(BlockInterface $subject, LayoutInterface $layout): array
    {
        if ($this->moduleManager->isEnabled('Magento_InventoryShippingAdminUi')) {
            $order = $this->registry->registry('current_order');
            $websiteId = (int) $order->getStore()->getWebsiteId();
            $isWebsiteInMultiSourceMode = $this->objectManager
                ->get(\Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode::class);
            $isOrderSourceManageable = $this->objectManager
                ->get(\Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable::class);
            if ($order
                && $isWebsiteInMultiSourceMode->execute($websiteId)
                && $isOrderSourceManageable->execute($order)
                && $this->config->isAutomaticSourceSelectionEnabled()
            ) {
                $this->dataPersistor->set('automated_source_selection', true);
            }
        }

        return [$layout];
    }
}
