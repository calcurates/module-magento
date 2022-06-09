<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Strategy;

use Magento\Framework\ObjectManagerInterface;

class RatesStrategyFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    protected $classByType = [
        0 => \Calcurates\ModuleMagento\Client\Response\Strategy\CommonCheckoutStrategy::class,
        1 => \Calcurates\ModuleMagento\Client\Response\Strategy\SplitCheckoutStrategy::class
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param int $type
     * @return RatesStrategyInterface
     */
    public function create(int $type): RatesStrategyInterface
    {
        if (!isset($this->classByType[$type])) {
            throw new \InvalidArgumentException($type . ' is unknown type');
        }

        return $this->objectManager->create($this->classByType[$type]);
    }
}
