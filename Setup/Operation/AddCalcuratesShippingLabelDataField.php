<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Setup\SalesSetupFactory;

class AddCalcuratesShippingLabelDataField
{
    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * AddQuoteAndOrderOriginField constructor.
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(SalesSetupFactory $salesSetupFactory)
    {
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false];
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $salesSetup->addAttribute('shipment', CustomSalesAttributesInterface::LABEL_DATA, $options);
    }
}
