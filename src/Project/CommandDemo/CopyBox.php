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

/**
 * Demonstrates the CopyBox command for placement of template elements in InDesign document.
 *
 * CopyBox command is the simplest command to place content in InDesign document.
 * It takes the element defined by parameter elementName from the template Document and places it
 * at top and left position in the generated document. The content in the element isn't changed.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class CopyBox extends AbstractStrategy
{
    /**
     * Method generated the InDesign commands to build the demo publication.
     *
     * @return void
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
     * Boxes are placed in the InDesign document at top and left positions without changing the size.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function copyWithoutResize(float $topPosition): void
    {
        //Copies imagebox with elementName "image" into the InDesign document.
        //The image file in the imagebox isn't changed.
        $this->addCommand(
            new CopyBoxCommand('image', 12.7, $topPosition)
        );

        //Copies rectangle with elementName "copyBox" into the InDesign document.
        //The size and content of isn't changes.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 50, $topPosition)
        );

        $topPosition += 15;
        //Elements from the template can be placed multiple times in the InDesign document.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 50, $topPosition)
        );

        $topPosition += 15;
        //All commands have setters for all parameters.
        $copyBox = new CopyBoxCommand();
        $copyBox->setElementName('copyBox')
                ->setLeft(50)
                ->setTop($topPosition);
        $this->addCommand($copyBox);
    }

    /**
     * When placing boxes in the InDesign document, the size can be adjusted freely with the width and height parameter.
     * Resizing is done in SizeTrait.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function copyWithResize(float $topPosition): void
    {
        //Copies rectangle with elementName "copyBox" into the InDesign document and changes its width and height.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 50, $topPosition, 20, 20)
        );

        $topPosition += 25;
        //Places rectangle "copyBox" and changes its width.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 50, $topPosition, 40)
        );

        $topPosition += 5;
        //Places rectangle "copyBox" and changes its height.
        $copyBox = new CopyBoxCommand('copyBox', 50, $topPosition);
        $copyBox->setHeight(100);
        $this->addCommand($copyBox);

        //Negative box sizes aren't allowed and throws an \Exception.
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

        //Copies the text box "copyPositionText" from template document.
        $copyBox = new CopyBoxCommand('copyPositionText');

        //Use template position when box is placed into the InDesign document.
        $copyBox->setUseTemplatePosition(true);
        $this->addCommand($copyBox);

        //Copies rectangle "copyPositionSquare" into the document
        $copyBox = new CopyBoxCommand('copyPositionSquare');
        $copyBox->setUseTemplatePosition(true);
        $this->addCommand($copyBox);
    }

    /**
     * When placing boxes in the InDesign document at the template position (top and left) the box can be resized.
     *
     * @return void
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\Traits\PositionTrait::setUseTemplatePosition
     */
    private function copyToTemplatePositionWithResize(): void
    {
        $this->addCommand(new GoToPage(3));

        //Copies the text box "copyPositionSquare" from template document.
        $copyBox = new CopyBoxCommand('copyPositionSquare');

        //Use template position when box is placed into the InDesign document.
        $copyBox->setUseTemplatePosition(true);

        //Adjust the size as needed
        $copyBox->setWidth(40)
                ->setHeight(40);

        $this->addCommand($copyBox);
    }
}
