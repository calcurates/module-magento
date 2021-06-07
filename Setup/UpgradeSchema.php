<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Setup;

use Calcurates\ModuleMagento\Setup\Operation\AddCalcuratesLabelTable;
use Calcurates\ModuleMagento\Setup\Operation\AddCalcuratesManifestTable;
use Calcurates\ModuleMagento\Setup\Operation\AddCalcuratesOrderTable;
use Calcurates\ModuleMagento\Setup\Operation\AddCalcuratesQuoteTable;
use Calcurates\ModuleMagento\Setup\Operation\AddCarrierCodesToLabelsTable;
use Calcurates\ModuleMagento\Setup\Operation\AddManifestIdToLabelTable;
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
     * @var AddManifestIdToLabelTable
     */
    private $addManifestIdToLabelTable;

    /**
     * @var AddCalcuratesManifestTable
     */
    private $addCalcuratesManifestTable;

    /**
     * @var AddCalcuratesQuoteTable
     */
    private $addCalcuratesQuoteTable;

    /**
     * @var AddCalcuratesOrderTable
     */
    private $addCalcuratesOrderTable;

    /**
     * UpgradeSchema constructor.
     * @param AddCalcuratesLabelTable $addCalcuratesLabelTable
     * @param AddCarrierCodesToLabelsTable $addCarrierCodesToLabelsTable
     * @param AddManifestIdToLabelTable $addManifestIdToLabelTable
     * @param AddCalcuratesManifestTable $addCalcuratesManifestTable
     * @param AddCalcuratesQuoteTable $addCalcuratesQuoteTable
     * @param AddCalcuratesOrderTable $addCalcuratesOrderTable
     */
    public function __construct(
        AddCalcuratesLabelTable $addCalcuratesLabelTable,
        AddCarrierCodesToLabelsTable $addCarrierCodesToLabelsTable,
        AddManifestIdToLabelTable $addManifestIdToLabelTable,
        AddCalcuratesManifestTable $addCalcuratesManifestTable,
        AddCalcuratesQuoteTable $addCalcuratesQuoteTable,
        AddCalcuratesOrderTable $addCalcuratesOrderTable
    ) {
        $this->addCalcuratesLabelTable = $addCalcuratesLabelTable;
        $this->addCarrierCodesToLabelsTable = $addCarrierCodesToLabelsTable;
        $this->addManifestIdToLabelTable = $addManifestIdToLabelTable;
        $this->addCalcuratesManifestTable = $addCalcuratesManifestTable;
        $this->addCalcuratesQuoteTable = $addCalcuratesQuoteTable;
        $this->addCalcuratesOrderTable = $addCalcuratesOrderTable;
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

        if (version_compare($context->getVersion(), '1.30.0', '<')) {
            $this->addCalcuratesManifestTable->execute($setup);
            $this->addManifestIdToLabelTable->execute($setup);
        }

        if (version_compare($context->getVersion(), '1.32.0', '<')) {
            $this->addCalcuratesQuoteTable->execute($setup);
            $this->addCalcuratesOrderTable->execute($setup);
        }

        $setup->endSetup();
    }
}
