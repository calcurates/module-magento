<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Block\Adminhtml\Shipping;

use Calcurates\ModuleMagento\Model\Shipment\LabelDataParser;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class LabelDataRenderer extends Template
{
    /**
     * @var string
     */
    private $labelDataString;

    /**
     * @var string
     */
    private $printUrl;

    /**
     * @var LabelDataParser
     */
    private $labelDataParser;

    /**
     * @var array
     */
    private $labelData;

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
     * @param string $labelDataString
     * @param string $printUrl
     * @return string
     */
    public function render(string $labelDataString, string $printUrl): string
    {
        $this->labelDataString = $labelDataString;
        $this->printUrl = $printUrl;
        $this->labelData = $this->labelDataParser->parse($labelDataString);

        return $this->toHtml();
    }

    /**
     * @return array
     */
    public function getLabelData(): array
    {
        return $this->labelData;
    }

    /**
     * @return string
     */
    public function getLabelDataString(): string
    {
        return $this->labelDataString;
    }

    /**
     * @return string
     */
    public function getPrintUrl(): string
    {
        return $this->printUrl;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();
        $cacheKeyInfo[] = $this->getLabelDataString();
        $cacheKeyInfo[] = $this->getPrintUrl();

        return $cacheKeyInfo;
    }
}
