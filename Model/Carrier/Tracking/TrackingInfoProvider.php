<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Carrier\Tracking;

use Calcurates\ModuleMagento\Api\ShippingLabelRepositoryInterface;
use Calcurates\ModuleMagento\Client\Command\GetTrackingInfoCommand;
use Calcurates\ModuleMagento\Client\Http\ApiException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Shipping\Model\Order\Track;
use Zend_Json_Exception;

class TrackingInfoProvider
{
    /**
     * @var GetTrackingInfoCommand
     */
    private $getTrackingInfoCommand;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\StatusFactory
     */
    private $trackStatusFactory;

    /**
     * @var \Magento\Shipping\Model\Tracking\Result\ErrorFactory
     */
    private $trackErrorFactory;

    /**
     * @var ShippingLabelRepositoryInterface
     */
    private $shippingLabelRepository;

    public function __construct(
        GetTrackingInfoCommand $getTrackingInfoCommand,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        ShippingLabelRepositoryInterface $shippingLabelRepository
    ) {
        $this->getTrackingInfoCommand = $getTrackingInfoCommand;
        $this->trackStatusFactory = $trackStatusFactory;
        $this->trackErrorFactory = $trackErrorFactory;
        $this->shippingLabelRepository = $shippingLabelRepository;
    }

    /**
     * @param Track $track
     * @return \Magento\Framework\Phrase|\Magento\Shipping\Model\Tracking\Result\Error|\Magento\Shipping\Model\Tracking\Result\Status
     * @throws Zend_Json_Exception
     */
    public function getTrackingInfo(Track $track)
    {
        try {
            $shippingLabel = $this->shippingLabelRepository->getByShipmentIdAndTrackingNumber(
                (int)$track->getParentId(),
                $track->getTrackNumber()
            );

            $trackingInfoArray = $this->getTrackingInfoCommand->get(
                (string)$shippingLabel->getCarrierCode(),
                (string)$shippingLabel->getCarrierProviderCode(),
                (string)$track->getTrackNumber(),
                (int)$track->getStore()->getId()
            );

            return $this->parseTrackingData($track, $trackingInfoArray);
        } catch (ApiException | NoSuchEntityException $exception) {
            $error = $this->trackErrorFactory->create();
            $error->setCarrier($track->getCarrierCode());
            $error->setCarrierTitle($track->getTitle());
            $error->setTracking($track->getTrackNumber());
            $error->setErrorMessage(__('Tracking getting error'));
            return $error;
        }
    }

    /**
     * @param Track $track
     * @param array $response
     * @return \Magento\Shipping\Model\Tracking\Result\Error|\Magento\Shipping\Model\Tracking\Result\Status
     */
    protected function parseTrackingData(Track $track, array $response)
    {
        $carrierTitle = $track->getTitle();
        if (!empty($response['trackingNumber'])) {
            $tracking = $this->trackStatusFactory->create();
            $tracking->setCarrier($track->getCarrierCode());
            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setTracking($response['trackingNumber']);
            $tracking->addData($this->processTrackingDetails($response));

            return $tracking;
        }

        $error = $this->trackErrorFactory->create();
        $error->setCarrier($track->getCarrierCode());
        $error->setCarrierTitle($carrierTitle);
        $error->setTracking($track->getTrackNumber());
        $error->setErrorMessage(!empty($response['message']) ? $response['message'] : __('Tracking getting error'));

        return $error;
    }

    /**
     * @param array $response
     * @return array
     */
    private function processTrackingDetails(array $response): array
    {
        $result = [
            'shippedDate' => null,
            'deliverydate' => null,
            'deliverytime' => null,
            'deliverylocation' => null,
            'weight' => null,
            'progressdetail' => [],
        ];
        $datetime = $this->parseDate(!empty($response['shipDate']) ? $response['shipDate'] : null);
        if ($datetime) {
            $result['shippedDate'] = gmdate('Y-m-d', $datetime->getTimestamp());
        }

        $field = 'estimatedDeliveryDate';
        // if delivered - get actual date
        if (!empty($response['statusCode']) && $response['statusCode'] === 'DE') {
            $field = 'actualDeliveryDate';
        }
        $datetime = $this->parseDate(!empty($response[$field]) ? $response[$field] : null);
        if ($datetime) {
            $result['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
            $result['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
        }

        if (!empty($response['events']) && is_array($response['events'])) {
            foreach ($response['events'] as $event) {
                $item = [
                    'activity' => !empty($event['description']) ? (string)$event['description'] : '',
                    'deliverydate' => null,
                    'deliverytime' => null,
                    'deliverylocation' => null
                ];
                $datetime = $this->parseDate(!empty($event['occurredAt']) ? $event['occurredAt'] : null);
                if ($datetime) {
                    $item['deliverydate'] = gmdate('Y-m-d', $datetime->getTimestamp());
                    $item['deliverytime'] = gmdate('H:i:s', $datetime->getTimestamp());
                }

                $result['progressdetail'][] = $item;
            }
        }

        return $result;
    }

    /**
     * Parses datetime string
     *
     * @param string $timestamp
     * @return null|\DateTime
     */
    private function parseDate($timestamp): ?\DateTime
    {
        if ($timestamp === null) {
            return null;
        }
        return \DateTime::createFromFormat(\DateTime::RFC3339, $timestamp);
    }
}
