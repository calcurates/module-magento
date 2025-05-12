<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Catalog\Product\Attribute\Resolver;

use Calcurates\ModuleMagento\Model\Shipment\CarriersSettingsProvider;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class HsCodeAttributeResolver
{
    public const CARRIERS_SETTING_MAPPED_HS_CODE_ATTRIBUTE = 'harmonizedTariffCodeCustomCode';

    /**
     * @var AttributeInterface
     */
    private $attribute;

    /**
     * @var CarriersSettingsProvider
     */
    private $carriersSettingsProvider;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param CarriersSettingsProvider $carriersSettingsProvider
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        CarriersSettingsProvider $carriersSettingsProvider,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->carriersSettingsProvider = $carriersSettingsProvider;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param int|null $storeId
     * @return AttributeInterface|null
     */
    public function resolveAttribute(?int $storeId = null): ?AttributeInterface
    {
        if ($this->attribute === null) {
            $hsCodeAttributeName = $this->resolveAttributeName($storeId);
            if (!$hsCodeAttributeName) {
                return null;
            }

            try {
                $hsCodeAttribute = $this->attributeRepository->get(Product::ENTITY, $hsCodeAttributeName);
            } catch (NoSuchEntityException $e) {
                return null;
            }
            $this->attribute = $hsCodeAttribute;
        }

        return $this->attribute;
    }

    /**
     * @param int|null $storeId
     * @return string|null
     */
    private function resolveAttributeName(?int $storeId = null): ?string
    {
        $carriersSettings = $this->carriersSettingsProvider->get($storeId);
        if (empty($carriersSettings)
            || !isset($carriersSettings[self::CARRIERS_SETTING_MAPPED_HS_CODE_ATTRIBUTE])
            || empty($carriersSettings[self::CARRIERS_SETTING_MAPPED_HS_CODE_ATTRIBUTE])
        ) {
            return null;
        }
        return $carriersSettings[self::CARRIERS_SETTING_MAPPED_HS_CODE_ATTRIBUTE];
    }
}
