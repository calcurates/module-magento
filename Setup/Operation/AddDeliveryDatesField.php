<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Setup\Operation;

use Calcurates\ModuleMagento\Api\Data\CustomSalesAttributesInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class AddDeliveryDatesField
{
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * AddQuoteAndOrderOriginField constructor.
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(QuoteSetupFactory $quoteSetupFactory, SalesSetupFactory $salesSetupFactory)
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $options = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false];
        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        /** @var \Magento\Sales\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);

        $quoteSetup->addAttribute('quote', CustomSalesAttributesInterface::DELIVERY_DATES, $options);
        $salesSetup->addAttribute('order', CustomSalesAttributesInterface::DELIVERY_DATES, $options);
    }
}
