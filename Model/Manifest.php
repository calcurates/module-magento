<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Data\ManifestInterface;

class Manifest extends \Magento\Framework\Model\AbstractModel implements ManifestInterface
{
    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init(\Calcurates\ModuleMagento\Model\ResourceModel\Manifest::class);
    }

    public function getManifestId(): int
    {
        return (int)$this->getData(self::MANIFEST_ID);
    }

    public function setManifestId(int $id): void
    {
        $this->setData(self::MANIFEST_ID, $id);
    }

    public function getPdfContent(): string
    {
        return (string)$this->getData(self::PDF_CONTENT);
    }

    public function setPdfContent(string $pdfContent): void
    {
        $this->setData(self::PDF_CONTENT, $pdfContent);
    }

    public function getManifestData(): array
    {
        return $this->getData(self::MANIFEST_DATA);
    }

    public function setManifestData(array $manifestData): void
    {
        $this->setData(self::MANIFEST_DATA, $manifestData);
    }
}
