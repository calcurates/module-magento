<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Archive;

use Magento\Framework\Archive\Zip as ZipArchiveMagento;
use Magento\Framework\Exception\FileSystemException;

/**
 * Zip compressed file archive.
 */
class Zip extends ZipArchiveMagento
{
    /**
     * Pack file.
     *
     * @param string $source
     * @param string $destination
     * @param null $sourceAlias
     * @return string
     * @throws FileSystemException
     */
    public function pack($source, $destination, $sourceAlias = null)
    {
        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZipArchive::CREATE)) {
            throw new FileSystemException(
                __('\'%1\' destination source could not been open', $destination)
            );
        }
        $zip->addFile($source, $sourceAlias);
        $zip->close();
        return $destination;
    }
}
