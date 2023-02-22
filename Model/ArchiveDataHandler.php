<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Model\Archive\Zip;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class ArchiveDataHandler implements ArchiveDataHandlerInterface
{
    /**
     * @var string
     */
    private $workingDirectoryPath = 'calcurates/';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Zip
     */
    private $archiver;

    /**
     * @param Filesystem $filesystem
     * @param Zip $archiver
     */
    public function __construct(
        Filesystem $filesystem,
        Zip $archiver
    ) {
        $this->filesystem = $filesystem;
        $this->archiver = $archiver;
    }

    /**
     * @param string[] $sources
     * @param string $dataArchiveName
     * @return string
     * @throws FileSystemException
     */
    public function prepareDataArchive(array $sources, string $dataArchiveName): string
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        try {
            $directory->delete($this->getArchiveDirectoryRelativePath());

            $archiveDirectoryAbsolutePath = $directory->getAbsolutePath($this->getArchiveDirectoryRelativePath());
            $archiveAbsolutePath = $this->prepareFileDirectory(
                $directory,
                $this->getArchiveRelativePath($dataArchiveName)
            );

            foreach ($sources as $fileName => $source) {
                $fileAbsolutePath = $archiveDirectoryAbsolutePath . $fileName;
                $directory->writeFile($fileAbsolutePath, $source);
                $this->archiver->pack($fileAbsolutePath, $archiveAbsolutePath, $fileName);
            }
            $archiveContent = $directory->readFile($archiveAbsolutePath);
        } finally {
            $directory->delete($this->getArchiveDirectoryRelativePath());
            $directory->delete($this->getArchiveRelativePath($dataArchiveName));
        }

        return $archiveContent;
    }

    /**
     * @return string
     */
    private function getArchiveDirectoryRelativePath(): string
    {
        return $this->workingDirectoryPath;
    }

    /**
     * @param string $fileName
     * @return string
     */
    private function getArchiveRelativePath(string $fileName): string
    {
        return $this->workingDirectoryPath . $fileName;
    }

    /**
     * @param WriteInterface $directory
     * @param string $path
     * @return string
     * @throws FileSystemException
     */
    private function prepareFileDirectory(WriteInterface $directory, string $path): string
    {
        $directory->delete($path);
        $dir = dirname($path);
        if ($dir !== '.') {
            $directory->create($dir);
        }
        return $directory->getAbsolutePath($path);
    }
}
