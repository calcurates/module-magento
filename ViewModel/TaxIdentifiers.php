<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\ViewModel;

use Calcurates\ModuleMagento\Model\ResourceModel\TaxIdentifier\CollectionFactory;
use Calcurates\ModuleMagento\Model\TaxIdentifier;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class TaxIdentifiers implements ArgumentInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return TaxIdentifier[]
     */
    public function getTaxIdentifiers(): array
    {
        return $this->collectionFactory->create()->getItems();
    }
}
