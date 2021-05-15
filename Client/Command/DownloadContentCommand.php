<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Command;

use Calcurates\ModuleMagento\Client\ApiClientProvider;

class DownloadContentCommand
{
    /**
     * @var ApiClientProvider
     */
    private $apiClientProvider;

    public function __construct(ApiClientProvider $apiClientProvider)
    {
        $this->apiClientProvider = $apiClientProvider;
    }

    /**
     * @param string $url
     * @param int $storeId
     * @return string
     * @throws \Calcurates\ModuleMagento\Client\Http\ApiException
     */
    public function download(string $url, int $storeId): string
    {
        $httpClient = $this->apiClientProvider->getClient($storeId);

        return $httpClient->get($url);
    }
}
