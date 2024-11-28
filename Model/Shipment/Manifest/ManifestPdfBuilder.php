<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment\Manifest;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\Shipping\Model\Shipping\LabelGenerator;

class ManifestPdfBuilder
{
    /**
     * @var PrintValidator
     */
    private $validator;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * @var GetManifests
     */
    private $getManifests;

    public function __construct(
        PrintValidator $validator,
        LabelGenerator $labelGenerator,
        GetManifests $getManifests
    ) {
        $this->validator = $validator;
        $this->labelGenerator = $labelGenerator;
        $this->getManifests = $getManifests;
    }

    /**
     * @param Collection $shipmentsCollection
     * @return \Zend_Pdf
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPdfForShipments(Collection $shipmentsCollection): \Zend_Pdf
    {
        $this->validator->validate($shipmentsCollection);

        $manifests = $this->getManifests->getManifestsByShipmentIds($shipmentsCollection->getAllIds());

        $contents = [];
        foreach ($manifests as $manifest) {
            $contents[] = $manifest->getPdfContent();
        }

        return $this->labelGenerator->combineLabelsPdf($contents);
    }
}
