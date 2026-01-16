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
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBoxScaled;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4PortraitTemplate;
use Pimcore\Model\Asset;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Demonstrates the `ImageBox` command for placing image elements in an InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class ImageBox extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemo();

        $topPosition = 12.7;
        $this->placeImage($topPosition);
        $this->fillModes($topPosition);

        $this->assetTypes();

        $this->imageScaledBox();
        $this->fullPagePlacement();
    }

    /**
     * Places an image on the page using different configurations of the `ImageBox` command.
     *
     * @param float $topPosition Reference to the vertical position for placing images.
     *
     * @return void
     * @throws FilesystemException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function placeImage(float &$topPosition): void
    {
        $this->addCommand(new GoToPage(1));
        $asset = $this->loadRandomAsset('/Brand Logos/', 500);

        // Places $asset in an ImageBox on the page.
        // The image parameter expects an Asset\Image instance.
        // This example does not set width or height, so it uses the template element size.
        $this->addCommand(
            new ImageBoxCommand('image', 12.7, $topPosition, null, null, $asset)
        );

        // This example sets the width and height of the placed image box.
        $this->addCommand(
            new ImageBoxCommand('image', 40, $topPosition, 40, 20, $asset)
        );

        $topPosition += 25;
        // You can set and change all ImageBox command parameters using setters.
        $imageBox = new ImageBoxCommand('image', 40, $topPosition);
        $imageBox->setAsset($asset)
                 ->setWidth(40)
                 ->setHeight(20);
        $this->addCommand($imageBox);

        $topPosition += 25;
        // This example sets the box size to the file dimensions of $asset.
        $imageBox = new ImageBoxCommand('image', 12.7, $topPosition);
        $imageBox->setAsset($asset, null, true);
        $this->addCommand($imageBox);

        $topPosition += $imageBox->getHeight() + 20;
    }

    /**
     * Demonstrate the supported FIT modes for InDesign images.
     *
     * @param float $topPosition the top-position for the images
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function fillModes(float $topPosition): void
    {
        $asset = $this->loadRandomAsset('/Car Images/%');
        $width = 60;
        $height = 40;
        $margin = 2.3;
        $left = 12.7;

        // #1 FIT_CENTER_CONTENT
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_CENTER_CONTENT);
        $this->addCommand($imageBox);

        $left += $width + $margin;
        // #2 FIT_CONTENT_AWARE_FIT
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_CONTENT_AWARE_FIT);
        $this->addCommand($imageBox);

        $left += $width + $margin;
        // #3 FIT_CONTENT_TO_FRAME
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_CONTENT_TO_FRAME);
        $this->addCommand($imageBox);

        $left = 12.7;
        $topPosition += $height + 5;
        // #4 FIT_PROPORTIONALLY
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_PROPORTIONALLY);
        $this->addCommand($imageBox);

        $left += $width + $margin;
        // #5 FIT_FILL_PROPORTIONALLY
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY);
        $this->addCommand($imageBox);

        $left = 12.7;
        $topPosition += $height + 5;
        // #6 FIT_FRAME_TO_CONTENT
        $imageBox = new ImageBoxCommand('image', $left, $topPosition, $width, $height, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FRAME_TO_CONTENT);
        $this->addCommand($imageBox);
    }

    /**
     * Demonstrates how to use different Pimcore `Model\Asset` types with thumbnail behavior.
     *
     * PimPrint supports fallback images for InDesign.
     * When placing an `Asset` into an `ImageBox` command, PimPrint checks the property "pimprint_asset"
     * and uses the assigned asset in InDesign.
     * This allows you to add print-ready assets to files like SVGs.
     *
     * Alternatively, you can use the Pimcore thumbnail processor to generate
     * InDesign-compatible assets on the fly, as shown in this demo.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox::PROPERTY_PIMPRINT_ASSET
     */
    private function assetTypes(): void
    {
        $this->addCommand(new GoToPage(2));

        $topPosition = 12.7;
        $left = 12.7;
        $width = 60;
        $height = 40;
        $margin = 5;

        // demo code for SVG support
        $asset = $this->loadRandomAsset('/Brand Logos/%', null, ['image/svg+xml']);
        if (!$asset instanceof Asset) {
            $this->project->addPageMessage('No SVG Demo-Asset found.');
        } else {
            // SVG support can be disabled via configuration `mds_pim_print_core.svg_support`
            $imageBox = new ImageBoxCommand('image', $left, $topPosition);
            $imageBox->setHeight($height)
                     ->setWidth($width);
            try {
                // `mds_pim_print_core.svg_support`: true
                // SVG is used in InDesign
                $imageBox->setAsset($asset);
                $this->addCommand($imageBox);
                $topPosition += $height + $margin;
            } catch (\Exception $e) {
                // `mds_pim_print_core.svg_support`: false
                // setAsset throws an exception when the asset isn't usable in InDesign
                $this->project->addPageMessage($e->getMessage());

                // Note for older InDesign versions:
                // If you need to display an SVG in an unsupported InDesign version,
                // you can force the use of a Pimcore thumbnail instead.
                // See the next ImageBox example, where a thumbnail is forced.
            }
        }

        // If you don't want to use the original asset or filetype, you can use a Pimcore thumbnail configuration.
        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBoxCommand('image', $left, $topPosition);
        $imageBox->setHeight($height)
                 ->setWidth($width);

        // When setting an asset, you can use the name of a thumbnail configuration
        // to use a thumbnail instead of the original asset.
        $imageBox->setAsset($asset, 'product_detail');

        $this->addCommand($imageBox);
        $topPosition += $height + $margin;

        $asset = $this->loadRandomAsset('/Sample Content/Documents/%', null, ['application/pdf']);
        if (!$asset instanceof Asset) {
            $this->project->addPageMessage('No PDF Demo-Asset found.');
        } else {
            // InDesign can place PDFs natively.
            $imageBox = new ImageBoxCommand('image', $left, $topPosition);
            $imageBox->setHeight($height)
                     ->setWidth($width)
                     ->setAsset($asset);
            $this->addCommand($imageBox);
        }
    }

    /**
     * Demonstrates the use of ImageBoxScaled, which provides direct access
     * to the asset’s position and dimensions inside the placed image box.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function imageScaledBox(): void
    {
        $this->addCommand(new GoToPage(3));

        $asset = $this->loadRandomAsset('/Car Images/%', 1000);
        $topPosition = 12.7;
        $left = 12.7;
        $width = 60;
        $height = 40;
        $margin = 10;

        $imageBoxScaled = new ImageBoxScaled('image', $left, $topPosition, $width, $height, $asset);
        $imageBoxScaled->setXScroll(5) // Moves the image inside the box to the right
                       ->setYScroll(5); // Moves the image inside the box downward
        $this->addCommand($imageBoxScaled);

        $topPosition += $height + $margin;
        $imageBoxScaled = new ImageBoxScaled('image', $left, $topPosition, $width, $height, $asset);
        $imageBoxScaled->setXScroll(-5) // Negative offset
                       ->setYScroll(-5); // Negative offset
        $this->addCommand($imageBoxScaled);

        $topPosition += $height + $margin;
        $imageBoxScaled = new ImageBoxScaled('image', $left, $topPosition, $width, $height, $asset);
        $imageBoxScaled->setScale(10); // Sets the X and Y scale to 10% of the original asset dimensions.
        $this->addCommand($imageBoxScaled);

        $topPosition += $height + $margin;
        $imageBoxScaled = new ImageBoxScaled('image', $left, $topPosition, $width, $height, $asset);
        $imageBoxScaled->setScale(10)
                       ->setXScroll(5)
                       ->setYScroll(5);
        $this->addCommand($imageBoxScaled);
    }

    /**
     * Places a full-page PDF scaled to fit within the page dimensions.
     *
     * This method creates a new page, adds a PDF scaled to the full
     * dimensions of an A4 portrait page, and adjusts its offsets.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function fullPagePlacement(): void
    {
        $this->addCommand(new NextPage());

        $imageBoxScaled = new ImageBoxScaled(
            'image',
            0,
            0,
            A4PortraitTemplate::PAGE_WIDTH,
            A4PortraitTemplate::PAGE_HEIGHT,
            $this->loadRandomAsset('/Sample Content/Documents/%', null, ['application/pdf']),
            ImageBoxCommand::FIT_FILL_PROPORTIONALLY
        );
        $imageBoxScaled->setXScroll(5)
                       ->setYScroll(5);

        $this->addCommand($imageBoxScaled);
    }
}
