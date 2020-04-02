<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Setup;

use Calcurates\ModuleMagento\Setup\Operation\AddQuoteAndOrderItemSourceField;
use Calcurates\ModuleMagento\Setup\Operation\AddQuoteAndOrderOriginField;
use Calcurates\ModuleMagento\Setup\Operation\RemoveQuoteAndOrderOriginField;
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
     * @var RemoveQuoteAndOrderOriginField
     */
    private $removeQuoteAndOrderOriginField;

    /**
     * @var AddQuoteAndOrderItemSourceField
     */
    private $addQuoteAndOrderItemSourceField;

    /**
     * UpgradeData constructor.
     * @param AddQuoteAndOrderOriginField $addQuoteAndOrderOriginField
     * @param RemoveQuoteAndOrderOriginField $removeQuoteAndOrderOriginField
     * @param AddQuoteAndOrderItemSourceField $addQuoteAndOrderItemSourceField
     */
    public function __construct(
        AddQuoteAndOrderOriginField $addQuoteAndOrderOriginField,
        RemoveQuoteAndOrderOriginField $removeQuoteAndOrderOriginField,
        AddQuoteAndOrderItemSourceField $addQuoteAndOrderItemSourceField
    ) {
        $this->addQuoteAndOrderOriginField = $addQuoteAndOrderOriginField;
        $this->removeQuoteAndOrderOriginField = $removeQuoteAndOrderOriginField;
        $this->addQuoteAndOrderItemSourceField = $addQuoteAndOrderItemSourceField;
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

        if (version_compare($context->getVersion(), '1.8.0', '<')) {
            $this->removeQuoteAndOrderOriginField->execute($setup);
            $this->addQuoteAndOrderItemSourceField->execute($setup);
        }

        $setup->endSetup();
    }
}
