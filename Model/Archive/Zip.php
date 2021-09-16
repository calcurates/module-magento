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
     */
    public function pack($source, $destination, $sourceAlias = null)
    {
        $zip = new \ZipArchive();
        $zip->open($destination, \ZipArchive::CREATE);
        $zip->addFile($source, $sourceAlias);
        $zip->close();
        return $destination;
    }
}
