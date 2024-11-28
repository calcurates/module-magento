<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\ResourceModel\Manifest;

use Calcurates\ModuleMagento\Model\Manifest;
use Calcurates\ModuleMagento\Model\ResourceModel\Manifest as ManifestResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Init models
     */
    protected function _construct(): void
    {
        $this->_init(Manifest::class, ManifestResource::class);
    }

    /**
     * Assign parent items on after collection load
     *
     * @return Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        foreach ($this as $item) {
            $this->_resource->unserializeFields($item);
        }
        return $this;
    }
}
