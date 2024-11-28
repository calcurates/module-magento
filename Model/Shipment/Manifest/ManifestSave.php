<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Shipment\Manifest;

use Calcurates\ModuleMagento\Api\Data\ManifestInterface;
use Calcurates\ModuleMagento\Api\ManifestSaveInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\Manifest;
use Magento\Framework\Exception\CouldNotSaveException;

class ManifestSave implements ManifestSaveInterface
{
    /**
     * @var Manifest
     */
    private $resource;

    public function __construct(Manifest $resource)
    {
        $this->resource = $resource;
    }

    public function save(ManifestInterface $manifest): ManifestInterface
    {
        try {
            $this->resource->save($manifest);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Something went wrong with saving manifest'), $e);
        }
        return $manifest;
    }
}
