<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Client\Http;

use Magento\Framework\HTTP\ClientFactory;

// @TODO: service class can't contain setters and logic together! Refactor that
class HttpClient
{
    public const TYPE_GET = 'GET';
    public const TYPE_POST = 'POST';

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var array
     */
    private $additionalHeaders = [];

    /**
     * @var int|null
     */
    private $timeout;

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
     * @return $this
     */
    public function addHeader($name, $value)
    {
        $this->additionalHeaders[$name] = $value;

        return $this;
    }

    /**
     * @param string $type
     * @param string $url
     * @param array|string|null $requestData
     * @return string
     * @throws ApiException
     * @throws \InvalidArgumentException
     */
    public function request(string $type, string $url, $requestData = null)
    {
        $client = $this->httpClientFactory->create();
        if ($this->getTimeout()) {
            $client->setTimeout($this->getTimeout());
        }
        foreach ($this->additionalHeaders as $headerName => $headerValue) {
            $client->addHeader($headerName, $headerValue);
        }

        // compression
        if (defined('CURLOPT_ENCODING')) {
            $client->setOption(CURLOPT_ENCODING, '');
        }

        $client->addHeader('Content-Type', 'application/json');
        $client->addHeader('Expect', '');
        switch ($type) {
            case self::TYPE_GET:
                try {
                    $client->get($url);
                } catch (\Throwable $e) {
                    throw new ApiException($e->getMessage(), $e->getCode(), $e);
                }
                break;
            case self::TYPE_POST:
                try {
                    $client->post($url, $requestData);
                } catch (\Throwable $e) {
                    throw new ApiException($e->getMessage(), $e->getCode(), $e);
                }
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
    public function get(string $url): string
    {
        return $this->request(self::TYPE_GET, $url);
    }

    /**
     * @param string $url
     * @param string|array $data
     * @return string
     * @throws ApiException
     */
    public function post(string $url, $data)
    {
        return $this->request(self::TYPE_POST, $url, $data);
    }

    /**
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * @param int|null $timeout
     * @return $this
     */
    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }
}
