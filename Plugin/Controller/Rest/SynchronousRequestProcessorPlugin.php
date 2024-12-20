<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Plugin\Controller\Rest;

use Calcurates\ModuleMagento\Model\Config;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Webapi\Controller\Rest\SynchronousRequestProcessor;

class SynchronousRequestProcessorPlugin
{
    /**
     * @var RestResponse
     */
    private $response;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Config
     */
    private $config;

    /**
     * SynchronousRequestProcessorPlugin constructor.
     * @param RestResponse $response
     * @param ProductMetadataInterface $productMetadata
     * @param Config $config
     */
    public function __construct(
        RestResponse $response,
        ProductMetadataInterface $productMetadata,
        Config $config
    ) {
        $this->config = $config;
        $this->productMetadata = $productMetadata;
        $this->response = $response;
    }

    /**
     * @param SynchronousRequestProcessor $subject
     * @param Request $request
     * @return array
     */
    public function beforeProcess(
        SynchronousRequestProcessor $subject,
        Request $request
    ) {
        if ($request->getUri() && false !== strpos($request->getUri()->getPath(), 'calcurates')) {
            $composerData = $this->config->getComposerData();
            $this->response->setHeader(
                'X-Magento-Version',
                sprintf(
                    'Magento-%1$s-%2$s',
                    $this->productMetadata->getEdition(),
                    $this->productMetadata->getVersion()
                )
            );
            $this->response->setHeader(
                'X-Calcurates-Version',
                $composerData['name'] . '/' . $composerData['version']
            );
        }
        return [$request];
    }
}
