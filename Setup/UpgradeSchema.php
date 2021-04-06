<?php

namespace Calcurates\ModuleMagento\Setup;

use Calcurates\ModuleMagento\Setup\Operation\AddCalcuratesLabelTable;
use Calcurates\ModuleMagento\Setup\Operation\AddCarrierCodesToLabelsTable;
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
     * @var AddCarrierCodesToLabelsTable
     */
    private $addCarrierCodesToLabelsTable;

    /**
     * UpgradeSchema constructor.
     * @param AddCalcuratesLabelTable $addCalcuratesLabelTable
     * @param AddCarrierCodesToLabelsTable $addCarrierCodesToLabelsTable
     */
    public function __construct(
        AddCalcuratesLabelTable $addCalcuratesLabelTable,
        AddCarrierCodesToLabelsTable $addCarrierCodesToLabelsTable
    ) {
        $this->addCalcuratesLabelTable = $addCalcuratesLabelTable;
        $this->addCarrierCodesToLabelsTable = $addCarrierCodesToLabelsTable;
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

        if (version_compare($context->getVersion(), '1.28.0', '<')) {
            $this->addCarrierCodesToLabelsTable->execute($setup);
        }

        $setup->endSetup();
    }
}
