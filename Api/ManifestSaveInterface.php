<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api;

use Calcurates\ModuleMagento\Api\Data\ManifestInterface;
use Magento\Framework\Exception\CouldNotSaveException;

interface ManifestSaveInterface
{
    /**
     * @param ManifestInterface $manifest
     * @return ManifestInterface
     * @throws CouldNotSaveException
     */
    public function save(ManifestInterface $manifest): ManifestInterface;
}
