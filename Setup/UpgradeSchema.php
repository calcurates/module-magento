<?php

namespace Calcurates\ModuleMagento\Setup;

use Calcurates\ModuleMagento\Setup\Operation\AddCalcuratesLabelTable;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var AddCalcuratesLabelTable
     */
    private $addCalcuratesLabelTable;

    /**
     * UpgradeSchema constructor.
     * @param AddCalcuratesLabelTable $addCalcuratesLabelTable
     */
    public function __construct(AddCalcuratesLabelTable $addCalcuratesLabelTable)
    {
        $this->addCalcuratesLabelTable = $addCalcuratesLabelTable;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.16.0', '<')) {
            $this->addCalcuratesLabelTable->execute($setup);
        }
        $setup->endSetup();
    }
}
