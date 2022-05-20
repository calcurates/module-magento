<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor\SplitCheckout;

use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;
use Calcurates\ModuleMagento\Model\Data\MetaRateData;

class OriginProcessor implements ResponseProcessorInterface
{
    /**
     * @var MetaRateData
     */
    private $metaRateData;

    /**
     * @param MetaRateData $metaRateData
     */
    public function __construct(
        MetaRateData $metaRateData
    ) {
        $this->metaRateData = $metaRateData;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        $origin['id'] = $response['origin']['id'];
        $origin['name'] = $response['origin']['name'];
        $origin['code'] = $response['origin']['syncedTargetOriginCode'];

        $this->metaRateData->setOriginData($response['origin']['id'], $origin);
    }
}
