<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\ViewModel\Adminhtml\Shipment;

use Calcurates\ModuleMagento\Model\Catalog\Product\Attribute\Resolver\HsCodeAttributeResolver;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order\Shipment;

class HsCodeFieldRenderer implements ArgumentInterface
{
    const HS_CODE_ATTRIBUTE_FRONTEND_ALLOWED_TYPES = ['text', 'select'];

    /**
     * @var Shipment
     */
    private $shipment;

    /**
     * @var HsCodeAttributeResolver
     */
    private $hsCodeAttributeResolver;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param HsCodeAttributeResolver $hsCodeAttributeResolver
     * @param LayoutInterface $layout
     * @param Escaper $escaper
     */
    public function __construct(
        HsCodeAttributeResolver $hsCodeAttributeResolver,
        LayoutInterface $layout,
        Escaper $escaper
    ) {
        $this->hsCodeAttributeResolver = $hsCodeAttributeResolver;
        $this->layout = $layout;
        $this->escaper = $escaper;
    }

    /**
     * @return Shipment
     * @throws LocalizedException
     */
    public function getShipment(): Shipment
    {
        if ($this->shipment === null) {
            throw new LocalizedException(__('Shipment object should be set for HS Code field rendering'));
        }
        return $this->shipment;
    }

    /**
     * @param Shipment $shipment
     */
    public function setShipment(Shipment $shipment): void
    {
        $this->shipment = $shipment;
    }

    /**
     * @param ProductInterface $product
     * @return string
     * @throws LocalizedException
     */
    public function render(ProductInterface $product): string
    {
        $output = '';
        if (!$this->isFieldRenderable()) {
            return $output;
        }

        $hsCodeAttribute = $this->resolveHsCodeAttribute();
        $hsCodeAttributeValue = $product->getData($hsCodeAttribute->getAttributeCode());
        if ($hsCodeAttribute->getFrontendInput() === 'text') {
            $output = '<input type="text"' .
                ' name="hs_code_value"' .
                ' id="hs_code_value_id"' .
                ' class="input-text admin__control-text"' .
                ' size="10"' .
                ' value="' . $this->escaper->escapeHtmlAttr($hsCodeAttributeValue) . '"' .
                ' />';

        } elseif ($hsCodeAttribute->getFrontendInput() === 'select') {
            $select = $this->layout->createBlock(
                Select::class
            )->setData(
                [
                    'id' => 'hs_code_value_id',
                    'name' => 'hs_code_value',
                    'class' => 'select admin__control-select',
                    'value' => $hsCodeAttributeValue,
                    'size' => 10,
                ]
            )->setOptions(
                $this->formatAttributeOptions($hsCodeAttribute->getOptions())
            );
            $output = $select->toHtml();
        }

        return $output;
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function isFieldRenderable(): bool
    {
        $hsCodeAttribute = $this->resolveHsCodeAttribute();
        if (!$hsCodeAttribute) {
            return false;
        }
        if (!in_array($hsCodeAttribute->getFrontendInput(), self::HS_CODE_ATTRIBUTE_FRONTEND_ALLOWED_TYPES, true)) {
            return false;
        }
        return true;
    }

    /**
     * @return AttributeInterface|null
     * @throws LocalizedException
     */
    private function resolveHsCodeAttribute(): ?AttributeInterface
    {
        return $this->hsCodeAttributeResolver->resolveAttribute((int)$this->getShipment()->getStoreId());
    }

    /**
     * @param Option[] $options
     * @return array
     */
    private function formatAttributeOptions(array $options): array
    {
        $formattedOptions = [];
        foreach ($options as $key => $option) {
            $formattedOptions[$key] = $option->toArray();
        }
        return $formattedOptions;
    }
}
