<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Http;

use Magento\Framework\HTTP\ClientFactory;

class HttpClient
{
    const TYPE_GET = 'GET';
    const TYPE_POST = 'POST';

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var array
     */
    private $additionalHeaders = [];

    /**
     * HttpClient constructor.
     * @param ClientFactory $httpClientFactory
     */
    public function __construct(ClientFactory $httpClientFactory)
    {
        $this->httpClientFactory = $httpClientFactory;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addHeader($name, $value)
    {
        $this->additionalHeaders[$name] = $value;
    }

    /**
     * @param string $type
     * @param string $url
     * @param array|string|null $requestData
     * @return string
     * @throws ApiException
     * @throws \InvalidArgumentException
     */
    public function request($type, $url, $requestData = null)
    {
        $client = $this->httpClientFactory->create();
        foreach ($this->additionalHeaders as $headerName => $headerValue) {
            $client->addHeader($headerName, $headerValue);
        }

        $client->addHeader('Content-Type', 'application/json');
        switch ($type) {
            case self::TYPE_GET:
                $client->get($url);
                break;
            case self::TYPE_POST:
                $client->post($url, $requestData);
                break;
            default:
                throw new \InvalidArgumentException('Incorrect Request Type');
                break;
        }

        if ($client->getStatus() >= 400) {
            throw new ApiException($client->getBody(), $client->getStatus());
        }

        return $client->getBody();
    }

    /**
     * @param string $url
     * @return string
     * @throws ApiException
     */
    public function get($url)
    {
        return $this->request(self::TYPE_GET, $url);
    }

    /**
     * @param string $url
     * @param string|array $data
     * @return string
     * @throws ApiException
     */
    public function post($url, $data)
    {
        return $this->request(self::TYPE_POST, $url, $data);
    }
}
