<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Controller\Shipping\Adminhtml\Order\Shipment;

use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Client\Command\DownloadContentCommand;
use Calcurates\ModuleMagento\Model\ArchiveDataHandlerInterface;
use Calcurates\ModuleMagento\Model\Carrier;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\PrintLabel as PrintLabelOrigin;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Psr\Log\LoggerInterface;
use Zend_Pdf;

class PrintLabel
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

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
     * @param RequestInterface $request
     * @param MessageManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ShippingLabelRepositoryInterface $shippingLabelRepository
     * @param LabelGenerator $labelGenerator
     * @param FileFactory $fileFactory
     * @param LoggerInterface $logger
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param DownloadContentCommand $downloadContentCommand
     * @param ArchiveDataHandlerInterface $archiveDataHandler
     */
    public function __construct(
        RequestInterface $request,
        MessageManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ShippingLabelRepositoryInterface $shippingLabelRepository,
        LabelGenerator $labelGenerator,
        FileFactory $fileFactory,
        LoggerInterface $logger,
        ShipmentRepositoryInterface $shipmentRepository,
        DownloadContentCommand $downloadContentCommand,
        ArchiveDataHandlerInterface $archiveDataHandler
    ) {
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->shippingLabelRepository = $shippingLabelRepository;
        $this->labelGenerator = $labelGenerator;
        $this->fileFactory = $fileFactory;
        $this->logger = $logger;
        $this->shipmentRepository = $shipmentRepository;
        $this->downloadContentCommand = $downloadContentCommand;
        $this->archiveDataHandler = $archiveDataHandler;
    }

    /**
     * @param PrintLabelOrigin $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundExecute(
        PrintLabelOrigin $subject,
        callable $proceed
    ) {
        try {
            $shipmentId = $this->request->getParam('shipment_id');

            $shipment = $this->shipmentRepository->get($shipmentId);
            if (!$shipment instanceof ShipmentInterface) {
                return $proceed();
            }

            $order = $shipment->getOrder();
            if (!$order instanceof OrderInterface) {
                return $proceed();
            }

            if ($order->getIsVirtual() || !$order->getData('shipping_method')) {
                return $proceed();
            }

            $shippingMethod = $order->getShippingMethod(true);
            if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
                return $proceed();
            }

            $shippingLabel = $this->shippingLabelRepository->getLastByShipmentId(
                (int)$shipment->getId()
            );
            $labelContent = $shippingLabel->getLabelContent();
            if ($labelContent) {
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
                            DirectoryList::VAR_DIR
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

        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setPath(
            'adminhtml/order_shipment/view',
            ['shipment_id' => $this->request->getParam('shipment_id')]
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
