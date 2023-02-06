<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Api\Client\CalcuratesClientInterface;
use Calcurates\ModuleMagento\Model\Config;
use Psr\Log\LoggerInterface;
use Calcurates\ModuleMagento\Client\Request\OrderInfoRequestBuilder;
use Magento\Sales\Api\Data\OrderInterface;

class SendOrderInformationCommand
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Framework\App\ScopeInterface|int|string|null
     */
    private $store;

    /**
     * @var CalcuratesClientInterface
     */
    private $calcuratesClient;

    /**
     * @var OrderInfoRequestBuilder
     */
    private $orderInfoRequestBuilder;

    /**
     * SendOrderIfnoramtionCommand constructor.
     * @param LoggerInterface $logger
     * @param Config $config
     * @param CalcuratesClientInterface $calcuratesClient
     * @param OrderInfoRequestBuilder $orderInfoRequestBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config,
        CalcuratesClientInterface $calcuratesClient,
        OrderInfoRequestBuilder $orderInfoRequestBuilder
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->calcuratesClient = $calcuratesClient;
        $this->orderInfoRequestBuilder = $orderInfoRequestBuilder;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function execute(OrderInterface $order): bool
    {
        $apiRequestBody = $this->orderInfoRequestBuilder->build($order);
        $result = false;
        if (is_array($apiRequestBody) && $apiRequestBody) {
            $debugData = [
                'request' => $apiRequestBody,
                'type' => 'populateOrderInfo'
            ];
            $this->setStore($order->getStoreId());
            try {
                $response = $this->calcuratesClient
                    ->populateOrderInfo($apiRequestBody, $order->getStoreId());
                $result = true;
                $debugData['result'] = [
                    'success' => true,
                    'response' =>$response
                ];
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            } finally {
                $this->debug($debugData);
            }
        }
        return $result;
    }


    /**
     * @param int $store
     */
    public function setStore(int $store)
    {
        $this->store = $store;
    }

    /**
     * @return int
     */
    private function getStore(): int
    {
        return $this->store;
    }

    /**
     * @return bool
     */
    private function isDebug(): bool
    {
        return $this->config->isDebug($this->getStore());
    }

    /**
     * Log debug data to file
     *
     * @TODO: move to debugger service
     *
     * @param mixed $debugData
     * @return void
     */
    protected function debug($debugData)
    {
        if ($this->isDebug()) {
            $this->logger->debug(var_export($debugData, true));
        }
    }
}
