<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Resolver;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CalcuratesConfigResolver implements ResolverInterface
{
    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(Config $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $store = $context->getExtensionAttributes()->getStore();
        $storeId = $store->getId();
        return [
            'is_shipping_on_product_enabled' => $this->configProvider->isShippingOnProductEnabled($storeId),
            'shipping_on_product_fallback_message' => $this->configProvider->getShippingOnProductFallbackMessage(
                $storeId
            ),
            'is_google_autocomplete_enabled' => $this->configProvider->isGoogleAddressAutocompleteEnabled($storeId),
            'google_places_api_key' => $this->configProvider->getGooglePlacesApiKey($storeId),
            'google_places_input_title' => $this->configProvider->getGooglePlacesInputTitle($storeId),
            'google_places_input_placeholder' => $this->configProvider->getGooglePlacesInputPlaceholder($storeId)
        ];
    }
}
