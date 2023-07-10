<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Block\Adminhtml\Order\View;

use Magento\InventoryShippingAdminUi\Block\Adminhtml\Order\View\ShipButton as BaseBlock;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\InventoryShippingAdminUi\Model\IsOrderSourceManageable;
use Magento\InventoryShippingAdminUi\Model\IsWebsiteInMultiSourceMode;
use Calcurates\ModuleMagento\Model\Config;

class ShipButton extends BaseBlock
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var IsWebsiteInMultiSourceMode
     */
    private $isWebsiteInMultiSourceMode;

    /**
     * @var IsOrderSourceManageable
     */
    private $isOrderSourceManageable;

    /**
     * @var Config
     */
    private $config;

    /**
     * ShipButton constructor.
     * @param Context $context
     * @param Registry $registry
     * @param IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode
     * @param Config $config
     * @param array $data
     * @param IsOrderSourceManageable|null $isOrderSourceManageable
     */
    public function __construct(
        Context $context,
        Registry $registry,
        IsWebsiteInMultiSourceMode $isWebsiteInMultiSourceMode,
        Config $config,
        array $data = [],
        IsOrderSourceManageable $isOrderSourceManageable = null
    ) {
        parent::__construct($context, $registry, $isWebsiteInMultiSourceMode, $data, $isOrderSourceManageable);
        $this->config = $config;
        $this->registry = $registry;
        $this->isWebsiteInMultiSourceMode = $isWebsiteInMultiSourceMode;
        $this->isOrderSourceManageable = $isOrderSourceManageable ??
            ObjectManager::getInstance()->get(IsOrderSourceManageable::class);
    }

    /**
     * @return $this|BaseBlock
     */
    protected function _prepareLayout()
    {
        $buttonItems = $this->buttonList->getItems();
        foreach ($buttonItems as $index => $buttons) {
            foreach ($buttons as $buttonName => $button) {
                if ($buttonName == 'order_ship') {
                    $onclickData = $button->getData('onclick');
                }
            }
        }
        parent::_prepareLayout();
        $order = $this->registry->registry('current_order');
        $websiteId = (int)$order->getStore()->getWebsiteId();
        if ($this->isWebsiteInMultiSourceMode->execute($websiteId)
            && $this->isOrderSourceManageable->execute($order)
            && $this->config->isAutomaticSourceSelectionEnabled()
            && isset($onclickData)
        ) {
            $this->buttonList->update(
                'order_ship',
                'onclick',
                $onclickData
            );
        }
        return $this;
    }
}
