<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Processor;

use Calcurates\ModuleMagento\Client\Response\MetadataPoolInterface;
use Calcurates\ModuleMagento\Client\Response\ResponseProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Shipping\Model\Rate\Result;
use Psr\Log\LoggerInterface;

class MetadataProcessor implements ResponseProcessorInterface
{
    /**
     * @var MetadataPoolInterface
     */
    private $metadataPool;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param MetadataPoolInterface $metadataPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        MetadataPoolInterface $metadataPool,
        ObjectManager $objectManager,
        LoggerInterface $logger
    ) {
        $this->metadataPool = $metadataPool;
        $this->objectManager = $objectManager;
        $this->logger = $logger;
    }

    /**
     * @param Result $result
     * @param array $response
     * @param CartInterface $quote
     * @return void
     */
    public function process(Result $result, array &$response, CartInterface $quote): void
    {
        try {
            foreach ($this->metadataPool->getMetadataTypes() as $entity => $metadataType) {
                $data = $this->metadataPool->getHydrator($entity)->extract($response);
                if (!empty($data)) {
                    $entityObject = $this->objectManager->create($entity);
                    $entityObject = $this->metadataPool->getHydrator($entity)->hydrate($entityObject, $data);
                    $this->metadataPool->setMetadata($metadataType, $entityObject);
                }
            }
        } catch (LocalizedException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
