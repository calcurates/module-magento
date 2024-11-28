<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Shipment;

use Calcurates\ModuleMagento\Client\Command\GetCarriersSettingsCommand;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class CarriersSettingsProvider
{
    public const CARRIERS_SETTINGS_DATA_CODE = 'carriers_settings';

    /**
     * @var GetCarriersSettingsCommand
     */
    private $getCarriersSettingsCommand;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GetCarriersSettingsCommand $getCarriersSettingsCommand
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GetCarriersSettingsCommand $getCarriersSettingsCommand,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager
    ) {
        $this->getCarriersSettingsCommand = $getCarriersSettingsCommand;
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int|null $storeId
     * @return array
     */
    public function get(int $storeId = null): array
    {
        $carriersSettings = $this->dataPersistor->get(self::CARRIERS_SETTINGS_DATA_CODE);
        if ($carriersSettings === null) {
            try {
                if ($storeId === null) {
                    $storeId = $this->storeManager->getStore()->getId();
                }
                $carriersSettings = $this->getCarriersSettingsCommand->get($storeId);
            } catch (LocalizedException $e) {
                $carriersSettings = [];
            }
            $this->dataPersistor->set(self::CARRIERS_SETTINGS_DATA_CODE, $carriersSettings);
        }
        return $carriersSettings;
    }
}
