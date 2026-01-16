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

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Demonstrates the CopyBox command for placing template elements in an InDesign document.
 *
 * The CopyBox command is the simplest way to place content in an InDesign document.
 * It takes the element defined by the `elementName` parameter from the template document and places it
 * at the top-left position in the generated document.
 * The command does not change the element’s content.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class CopyBox extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemo();

        $this->copyWithoutResize(12.7);
        $this->copyWithResize(100);

        $this->copyToTemplatePosition();
        $this->copyToTemplatePositionWithResize();
    }

    /**
     * Copy the template elements to the InDesign document without resizing
     * Place them at the $topPosition
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function copyWithoutResize(float $topPosition): void
    {
        // Copy the image box with elementName "image" into the InDesign document.
        // The image file in the image box is not changed.
        $this->addCommand(
            new CopyBoxCommand('image', 12.7, $topPosition)
        );

        // Copy the rectangle with elementName "copyBox" into the InDesign document.
        // The size and content are not changed.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 60, $topPosition)
        );

        $topPosition += 15;
        // You can place elements from the template multiple times in the InDesign document.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 60, $topPosition)
        );

        $topPosition += 15;
        // All commands provide setters for their parameters.
        $copyBox = new CopyBoxCommand();
        $copyBox->setElementName('copyBox')
                ->setLeft(60)
                ->setTop($topPosition);
        $this->addCommand($copyBox);
    }

    /**
     * When placing boxes in the InDesign document, you can freely adjust the size
     * using the width and height parameters.
     * `SizeTrait` handles the resizing.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function copyWithResize(float $topPosition): void
    {
        // Copies the rectangle with elementName "copyBox" into the InDesign document and change its width and height.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 60, $topPosition, 20, 20)
        );

        $topPosition += 25;
        // Places the rectangle "copyBox" and change its width.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 60, $topPosition, 40)
        );

        $topPosition += 5;
        // Places the rectangle "copyBox" and change its height.
        $copyBox = new CopyBoxCommand('copyBox', 60, $topPosition);
        $copyBox->setHeight(100);
        $this->addCommand($copyBox);

        // Negative box sizes are not allowed and throw an \Exception.
        try {
            $copyBox->setHeight(-10);
        } catch (\Exception $e) {
            $this->project->addPageMessage($e->getMessage(), true);
        }
    }

    /**
     * When placing boxes in the InDesign document, the position (top and left) can be used from the template document.
     *
     * @return void
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\Traits\PositionTrait::setUseTemplatePosition
     */
    private function copyToTemplatePosition(): void
    {
        $this->addCommand(new GoToPage(2));

        // Copy the text box "copyPositionText" from the template document.
        $copyBox = new CopyBoxCommand('copyPositionText');

        // Use the template position when placing the box into the InDesign document.
        $copyBox->setUseTemplatePosition(true);
        $this->addCommand($copyBox);

        // Copy rectangle "copyPositionSquare" into the document
        $copyBox = new CopyBoxCommand('copyPositionSquare');
        $copyBox->setUseTemplatePosition(true);
        $this->addCommand($copyBox);
    }

    /**
     * When placing boxes in the InDesign document at the template position (top and left), you can resize the box.
     * The original template position is preserved during placement.
     *
     * @return void
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\Traits\PositionTrait::setUseTemplatePosition
     */
    private function copyToTemplatePositionWithResize(): void
    {
        $this->addCommand(new GoToPage(3));

        // Copy the text box "copyPositionSquare" from the template document.
        $copyBox = new CopyBoxCommand('copyPositionSquare');

        // Use template position when box is placed into the InDesign document.
        $copyBox->setUseTemplatePosition(true);

        // Adjust the size of the box
        $copyBox->setWidth(40)
                ->setHeight(40);

        $this->addCommand($copyBox);
    }
}
