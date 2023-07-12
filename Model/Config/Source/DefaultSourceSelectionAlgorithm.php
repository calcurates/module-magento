<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model\Config\Source;

use Magento\Framework\ObjectManagerInterface;
use Calcurates\ModuleMagento\Model\Source\SourceServiceContext;
use Magento\InventorySourceSelectionApi\Api\GetSourceSelectionAlgorithmListInterface;

class DefaultSourceSelectionAlgorithm implements \Magento\Framework\Data\OptionSourceInterface
{
    public const NOT_AVAILABLE_SOURCE_SELECTION = 'not-available';

    /**
     * @var mixed
     */
    private $sourceSelectionAlgorithmList;

    /**
     * DefaultSourceSelectionAlgorithm constructor.
     * @param ObjectManagerInterface $objectManager
     * @param SourceServiceContext $sourceServiceContext
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SourceServiceContext $sourceServiceContext
    ) {
        if ($sourceServiceContext->isInventoryEnabled()
            && $sourceServiceContext->isSourceSelectionEnabled()
        ) {
            $this->sourceSelectionAlgorithmList = $objectManager
                ->get(GetSourceSelectionAlgorithmListInterface::class);
        }
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $algorithms = [];
        if ($this->sourceSelectionAlgorithmList) {
            $algorithms = $this->sourceSelectionAlgorithmList->execute();
            $result = [];
        } else {
            $result = [
                [
                    'label' => "Source Selection Is Not Available",
                    'value' => self::NOT_AVAILABLE_SOURCE_SELECTION
                ]
            ];
        }
        foreach ($algorithms as $algorithm) {
            $result[] = [
                'label' => $algorithm->getTitle(),
                'value' => $algorithm->getCode()
            ];
        }
        return $result;
    }
}
