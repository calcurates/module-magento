<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\CheckoutConfigProvider;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

class CalcuratesConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $calcuratesConfig;

    /**
     * @param Config $calcuratesConfig
     */
    public function __construct(Config $calcuratesConfig)
    {
        $this->calcuratesConfig = $calcuratesConfig;
    }


    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'calcurates' => [
                Config::INFO_MESSAGE_DISPLAY_POSITION => $this->calcuratesConfig->getInfoMessagePosition(),
            ]
        ];
    }
}
