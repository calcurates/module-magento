<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Block\Adminhtml\Shipping;

use Calcurates\ModuleMagento\Api\Data\ShippingLabelInterface;
use Calcurates\ModuleMagento\Model\Shipment\LabelDataParser;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class LabelDataRenderer extends Template
{
    /**
     * @var LabelDataParser
     */
    private $labelDataParser;

    /**
     * @var array
     */
    private $labelData;

    /**
     * @var ShippingLabelInterface
     */
    private $shippingLabel;

    /**
     * LabelDataRenderer constructor.
     * @param Context $context
     * @param LabelDataParser $labelDataParser
     * @param array $data
     */
    public function __construct(Context $context, LabelDataParser $labelDataParser, array $data = [])
    {
        parent::__construct($context, $data);
        $this->labelDataParser = $labelDataParser;
    }

    /**
     * @param ShippingLabelInterface $shippingLabel
     * @return string
     */
    public function render(ShippingLabelInterface $shippingLabel): string
    {
        $this->shippingLabel = $shippingLabel;
        $this->labelData = $this->labelDataParser->parse($shippingLabel->getLabelData());

        return $this->toHtml();
    }

    /**
     * @return ShippingLabelInterface
     */
    public function getShippingLabel(): ShippingLabelInterface
    {
        return $this->shippingLabel;
    }

    /**
     * @return array
     */
    public function getLabelData(): array
    {
        return $this->labelData;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        $cacheKeyInfo[] = $this->getShippingLabel()->getId();

        return $cacheKeyInfo;
    }
}
