<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Setup;

use Calcurates\ModuleMagento\Setup\Operation\AddQuoteAndOrderOriginField;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var AddQuoteAndOrderOriginField
     */
    private $addQuoteAndOrderOriginField;

    /**
     * UpgradeData constructor.
     * @param AddQuoteAndOrderOriginField $addQuoteAndOrderOriginField
     */
    public function __construct(AddQuoteAndOrderOriginField $addQuoteAndOrderOriginField)
    {
        $this->addQuoteAndOrderOriginField = $addQuoteAndOrderOriginField;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.3.3', '<')) {
            $this->addQuoteAndOrderOriginField->execute($setup);
        }
        $setup->endSetup();
    }
}
