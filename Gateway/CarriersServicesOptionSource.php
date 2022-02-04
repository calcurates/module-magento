<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Gateway;

use Calcurates\ModuleMagento\Client\Command\GetShippingOptionsCommand;
use Calcurates\ModuleMagento\Client\Http\ApiException;

class CarriersServicesOptionSource
{
    /**
     * @var GetShippingOptionsCommand
     */
    private $getShippingOptionsCommand;

    public function __construct(GetShippingOptionsCommand $getShippingOptionsCommand)
    {
        $this->getShippingOptionsCommand = $getShippingOptionsCommand;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getOptions(int $storeId): array
    {
        try {
            $carriersWithOptions = $this->getShippingOptionsCommand->get(
                $storeId,
                GetShippingOptionsCommand::TYPE_CARRIERS
            );
        } catch (ApiException $e) {
            return [];
        }

        $shippingCarriers = [];
        foreach ($carriersWithOptions as $item) {
            $shippingCarrier = [
                'value' => $item['id'],
                'label' => $item['carrierName'],
                'options' => []
            ];

            foreach ($item['services'] as $service) {
                $shippingCarrier['options'][] = [
                    'value' => $service['id'],
                    'label' => $service['name']
                ];
            }
            if ($shippingCarrier['options']) {
                $shippingCarriers[] = $shippingCarrier;
            }
        }

        return $shippingCarriers;
    }
}
