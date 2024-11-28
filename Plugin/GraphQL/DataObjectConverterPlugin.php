<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\GraphQL;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Reflection\DataObjectProcessor;

class DataObjectConverterPlugin
{
    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    public function __construct(DataObjectProcessor $dataObjectProcessor)
    {
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    public function afterToFlatArray(
        ExtensibleDataObjectConverter $subject,
        $result,
        ExtensibleDataInterface $dataObject,
        $skipCustomAttributes = [],
        $dataObjectType = null
    ) {
        if ($dataObject instanceof \Magento\Quote\Api\Data\ShippingMethodInterface) {
            $calcuratesData = $dataObject->getExtensionAttributes()->getCalcuratesData();
            if (!$calcuratesData) {
                return $result;
            }

            $result['calcurates_data'] = $this->dataObjectProcessor->buildOutputDataArray(
                $calcuratesData,
                \Calcurates\ModuleMagento\Api\Data\RateDataInterface::class
            );
        }

        return $result;
    }
}
