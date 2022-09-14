<?php
/**
 * mds PimPrint
 *
 * This source file is licensed under GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license    https://pimprint.mds.eu/license GPLv3
 */

namespace Mds\PimPrint\DemoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Folder;

/**
 * Imports PimPrint Demo InDesign Template files as Pimcore assets.
 *
 * @package Mds\PimPrint\DemoBundle\Migrations
 */
class Version20200929111003 extends AbstractMigration
{
    /**
     * Properties to create.
     *
     * @var array
     */
    protected array $files = [
        'PimPrint-DataPrintDemo_blue.indd',
        'PimPrint-DataPrintDemo_green.indd',
        'PimPrint-DataPrintDemo_orange.indd',
    ];

    /**
     * Relative path to template files.
     *
     * @var string
     */
    protected string $path = '../Resources/pimprint/';

    /**
     * Asset folder name.
     *
     * @var string
     */
    protected string $folderName = 'PimPrint-Demo';

    /**
     * Up method.
     *
     * @param Schema $schema
     *
     * @return void
     * @throws \Exception
     */
    public function up(Schema $schema): void
    {
        $folder = $this->setupFolder($this->folderName);
        $this->importAssets($this->files, $folder);
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     *
     * @return void
     * @throws \Exception
     */
    public function down(Schema $schema): void
    {
        $folder = $this->setupFolder($this->folderName);
        $this->removeAssets($this->files, $folder);
        $this->removeFolder($folder);
    }

    /**
     * Loads asset folder by $folderName or created folder.
     *
     * @param string $folderName
     *
     * @return Folder
     * @throws \Exception
     */
    private function setupFolder(string $folderName): Folder
    {
        $folder = Folder::getByPath($folderName);
        if ($folder instanceof Folder) {
            return $folder;
        }
        $folder = Asset\Service::createFolderByPath($folderName);
        if ($folder instanceof Folder) {
            $this->write('Asset folder created: ' . $folder->getFullPath());
        }

        return $folder;
    }

    /**
     * Imports template $files into $folder.
     *
     * @param array  $files
     * @param Folder $folder
     *
     * @throws \Exception
     */
    private function importAssets(array $files, Folder $folder)
    {
        foreach ($files as $file) {
            $filePath = $this->buildFilePath($file);
            if (false === file_exists($filePath)) {
                $this->write('Template file not found: ' . $filePath);
                continue;
            }
            $assetPath = $folder->getFullPath() . '/' . $file;
            $asset = Asset::getByPath($assetPath);
            if (false === $asset instanceof Asset) {
                $asset = new Asset();
                $asset->setParent($folder);
                $asset->setFilename($file);
            }
            $asset->setData(file_get_contents($filePath));
            $asset->save();
            $this->write("Template file imported: " . $asset->getFullPath());
        }
    }

    /**
     * Builds local filePath to $file.
     *
     * @param string $file
     *
     * @return string
     */
    private function buildFilePath(string $file): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . $this->path . $file;
    }

    /**
     * Removes $files in $folder.
     *
     * @param array  $files
     * @param Folder $folder
     *
     * @throws \Exception
     */
    private function removeAssets(array $files, Folder $folder)
    {
        foreach ($files as $file) {
            $assetPath = $folder->getFullPath() . '/' . $file;
            $asset = Asset::getByPath($assetPath);
            if ($asset instanceof Asset) {
                $asset->delete();
                $this->write("Asset removed: " . $asset->getFullPath());
            }
        }
    }

    /**
     * Removes $folder.
     *
     * @param Folder $folder
     *
     * @throws \Exception
     */
    private function removeFolder(Folder $folder)
    {
        $folder->delete();
        $this->write('Asset folder removed: ' . $folder->getFullPath());
    }
}
