<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Controller\Adminhtml\Order\Shipment;

use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;

class PrintCalcuratesLabel extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param ShippingLabelRepositoryInterface $shippingLabelRepository
     * @param LabelGenerator $labelGenerator
     * @param FileFactory $fileFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        LabelGenerator $labelGenerator,
        FileFactory $fileFactory,
        LoggerInterface $logger
    ) {
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->labelGenerator = $labelGenerator;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Print label for one specific shipment
     *
     * @return ResponseInterface|void
     */
    public function execute()
    {
        try {
            $shippingLabel = $this->shippingLabelRepository->getById(
                (int)$this->getRequest()->getParam('shipping_label_id')
            );
            $labelContent = $shippingLabel->getLabelContent();
            if ($labelContent) {
                $pdfContent = null;
                if (stripos($labelContent, '%PDF-') !== false) {
                    $pdfContent = $labelContent;
                } else {
                    $pdf = new \Zend_Pdf();
                    $page = $this->labelGenerator->createPdfPageFromImageString($labelContent);
                    if (!$page) {
                        $this->messageManager->addErrorMessage(
                            __(
                                'We don\'t recognize or support the file extension in this shipping label: %1.',
                                $shippingLabel->getId()
                            )
                        );
                    }
                    $pdf->pages[] = $page;
                    $pdfContent = $pdf->render();
                }

                return $this->fileFactory->create(
                    'ShippingLabel(' . $shippingLabel->getId() . ').pdf',
                    $pdfContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An error occurred while creating shipping label.'));
        }
        $this->_redirect(
            'adminhtml/order_shipment/view',
            ['shipment_id' => $this->getRequest()->getParam('shipment_id')]
        );
    }
}
