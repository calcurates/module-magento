<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Api\Data;

interface ManifestInterface
{
    public const MANIFEST_ID = 'manifest_id';
    public const PDF_CONTENT = 'pdf_content';
    public const MANIFEST_DATA = 'manifest_data';
    public const CREATED_AT = 'created_at';

    /**
     * @return int
     */
    public function getManifestId(): int;

    /**
     * @param int $id
     */
    public function setManifestId(int $id): void;

    /**
     * @return string
     */
    public function getPdfContent(): string;

    /**
     * @param string $pdfContent
     */
    public function setPdfContent(string $pdfContent): void;

    /**
     * @return array
     */
    public function getManifestData(): array;

    /**
     * @param array $manifestData
     */
    public function setManifestData(array $manifestData): void;
}
