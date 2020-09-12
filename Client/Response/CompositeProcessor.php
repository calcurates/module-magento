<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;

class CompositeProcessor implements ResponseProcessorInterface
{
    /**
     * @var ResponseProcessorInterface[]
     */
    private $processors;

    public function __construct(array $processors)
    {
        $this->processors = $processors;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array $response, CartInterface $quote): void
    {
        foreach ($this->getProcessors() as $processor) {
            $processor->process($result, $response, $quote);
        }
    }

    /**
     * @return ResponseProcessorInterface[]
     */
    public function getProcessors(): array
    {
        return $this->processors;
    }
}
