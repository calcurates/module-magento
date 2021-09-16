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
use Calcurates\ModuleMagento\Client\Command\DownloadContentCommand;
use Calcurates\ModuleMagento\Model\ArchiveDataHandlerInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;
use Zend_Pdf;

class PrintCalcuratesLabel extends Action
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
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var DownloadContentCommand
     */
    private $downloadContentCommand;

    /**
     * @var ArchiveDataHandlerInterface
     */
    private $archiveDataHandler;

    /**
     * PrintCalcuratesLabel constructor.
     * @param Context $context
     * @param ShippingLabelRepositoryInterface $shippingLabelRepository
     * @param LabelGenerator $labelGenerator
     * @param FileFactory $fileFactory
     * @param LoggerInterface $logger
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param DownloadContentCommand $downloadContentCommand
     * @param ArchiveDataHandlerInterface $archiveDataHandler
     */
    public function __construct(
        Context $context,
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        LabelGenerator $labelGenerator,
        FileFactory $fileFactory,
        LoggerInterface $logger,
        ShipmentRepositoryInterface $shipmentRepository,
        DownloadContentCommand $downloadContentCommand,
        ArchiveDataHandlerInterface $archiveDataHandler
    ) {
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->labelGenerator = $labelGenerator;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        $this->shipmentRepository = $shipmentRepository;
        $this->downloadContentCommand = $downloadContentCommand;
        $this->archiveDataHandler = $archiveDataHandler;
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
                    $pdf = new Zend_Pdf();
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

                $labelData = $shippingLabel->getLabelData();
                if (isset($labelData['formDownload'])) {
                    $shipment = $this->shipmentRepository->get($shippingLabel->getShipmentId());
                    $formContent = $this->downloadAdditionalContent(
                        $labelData['formDownload'],
                        (int)$shipment->getStoreId()
                    );

                    if (!empty($formContent)) {
                        $archiveName = sprintf('ShippingLabel(%s).zip', $shippingLabel->getId());
                        $archiveContent = $this->archiveDataHandler->prepareDataArchive(
                            [
                                sprintf('shipping-label-%s.pdf', $shippingLabel->getId()) => $pdfContent,
                                sprintf('custom-form-%s.pdf', $shippingLabel->getId()) => $formContent
                            ],
                            $archiveName
                        );
                        return $this->fileFactory->create(
                            $archiveName,
                            $archiveContent,
                            DirectoryList::VAR_DIR,
                        );
                    }
                }

                return $this->fileFactory->create(
                    sprintf('ShippingLabel(%s).pdf', $shippingLabel->getId()),
                    $pdfContent,
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(__('An error occurred while creating shipping label.'));
        }
        $this->_redirect(
            'adminhtml/order_shipment/view',
            ['shipment_id' => $this->getRequest()->getParam('shipment_id')]
        );
    }

    /**
     * @param string $url
     * @param int $storeId
     * @return string
     */
    private function downloadAdditionalContent(string $url, int $storeId): string
    {
        try {
            $content = $this->downloadContentCommand->download($url, $storeId);
        } catch (Exception $e) {
            $content = '';
        }
        return $content;
    }
}
