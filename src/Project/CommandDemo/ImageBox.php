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

namespace Mds\PimPrint\DemoBundle\Project\CommandDemo;

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\Service\ProjectsManager;
use Pimcore\Model\Asset;

/**
 * Demonstrates the ImageBox command for placement of image elements in InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class ImageBox extends AbstractStrategy
{
    /**
     * Method generated the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    public function build(): void
    {
        $this->initDemo();

        $topPosition = 12.7;
        $topPosition = $this->placeImage($topPosition);
        $this->fillModes($topPosition);
        $this->assetTypes();
    }

    /**
     * Demonstrates the placement of images.
     *
     * @param float $topPosition
     *
     * @return float
     * @throws \Exception
     * @throws FilesystemException
     */
    private function placeImage(float $topPosition): float
    {
        $this->addCommand(new GoToPage(1));
        $asset = $this->loadRandomAsset('/Brand Logos/', 500);

        //Places $asset in a ImageBox on the page. Image parameter expects Asset\Image instance.
        //In this example no width and height is set. Then the size of the template element is used.
        $this->addCommand(
            new ImageBoxCommand('image', 12.7, $topPosition, null, null, $asset)
        );

        //In this example width and height is set.
        $this->addCommand(
            new ImageBoxCommand('image', 40, $topPosition, 40, 20, $asset)
        );

        $topPosition += 25;
        //All parameters of ImageBox command can be set and changed programmatically with setter methods.
        $imageBox = new ImageBoxCommand('image', 40, $topPosition);
        $imageBox->setAsset($asset)
                 ->setWidth(40)
                 ->setHeight(20);
        $this->addCommand($imageBox);

        $topPosition += 25;
        //In this example the box size is set to the file-dimensions of $asset.
        $imageBox = new ImageBoxCommand('image', 12.7, $topPosition);
        $imageBox->setAsset($asset, null, true);
        $this->addCommand($imageBox);

        return $topPosition + $imageBox->getHeight() + 20;
    }

    /**
     * Demonstrates the supported FILL modes of InDesign images.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function fillModes(float $topPosition): void
    {
        $asset = $this->loadRandomAsset('/Car Images/%');
        $width = 60;
        $height = 40;
        $margin = 2.3;
        $left = 12.7;

        //#1 Demonstrates FIT_CENTER_CONTENT mode
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_CENTER_CONTENT);
        $this->addCommand($imageBox);

        $left += $width + $margin;
        //#2 Demonstrates FIT_CONTENT_AWARE_FIT mode
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_CONTENT_AWARE_FIT);
        $this->addCommand($imageBox);

        $left += $width + $margin;
        //#3 Demonstrates FIT_CONTENT_TO_FRAME mode
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_CONTENT_TO_FRAME);
        $this->addCommand($imageBox);

        $left = 12.7;
        $topPosition += $height + 5;
        //#4 Demonstrates FIT_PROPORTIONALLY mode
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_PROPORTIONALLY);
        $this->addCommand($imageBox);

        $left += $width + $margin;
        //#5 Demonstrates FIT_FILL_PROPORTIONALLY mode
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY);
        $this->addCommand($imageBox);

        $left = 12.7;
        $topPosition += $height + 5;
        //#6 Demonstrates FIT_FRAME_TO_CONTENT mode
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FRAME_TO_CONTENT);
        $this->addCommand($imageBox);
    }

    /**
     * Demonstrates usage of different Pimcore Model\Asset types with thumbnail behaviours.
     *
     * PimPrint supports fallback Images for InDesign:
     * When placing an Asset into a ImageBox Command PimPrint checks for Property 'pimprint_asset' and uses the
     * assigned Asset for display in InDesign. With this behaviour user specific print assets can be assigned to assets
     * like SVGs which aren't supported by InDesign.
     *
     * Alternatively the Pimcore thumbnail processor can be used to build assets usable in InDesign on the fly, which
     * is demonstrated in this demo.
     *
     * @return void
     * @throws \Exception
     * @throws \Exception
     * @throws FilesystemException
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox::PROPERTY_PIMPRINT_ASSET)
     */
    private function assetTypes(): void
    {
        $this->addCommand(new GoToPage(2));

        $topPosition = 12.7;
        $left = 12.7;
        $width = 60;
        $height = 40;
        $margin = 5;

        $asset = $this->loadRandomAsset('/Brand Logos/%', null, ['image/svg+xml']);
        if (false === $asset instanceof Asset) {
            ProjectsManager::getProject()
                           ->addPageMessage('No SVG Demo-Asset found.');
        } else {
            //Scalable Vector Graphics (SVG) aren't supported by InDesign.
            $imageBox = new ImageBoxCommand('image', $left, $topPosition);
            $imageBox->setHeight($height)
                     ->setWidth($width);
            try {
                $imageBox->setAsset($asset);
            } catch (\Exception $e) {
                //setAsset throws an exception when asset isn't usable in InDesign
                ProjectsManager::getProject()
                               ->addPageMessage($e->getMessage());
            }
            //When setting an asset the name of a thumbnail config can be used to use thumbnails an not the
            //original asset.
            $imageBox->setAsset($asset, 'product_detail');
            $this->addCommand($imageBox);
            $topPosition += $height + $margin;
        }

        $asset = $this->loadRandomAsset('/Sample Content/Documents/%', null, ['application/pdf']);
        if (false === $asset instanceof Asset) {
            ProjectsManager::getProject()
                           ->addPageMessage('No PDF Demo-Asset found.');
        } else {
            //PDFs can be placed natively in InDesign
            $imageBox = new ImageBoxCommand('image', $left, $topPosition);
            $imageBox->setHeight($height)
                     ->setWidth($width)
                     ->setAsset($asset);
            $this->addCommand($imageBox);
        }
    }
}
