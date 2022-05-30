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
use Mds\PimPrint\CoreBundle\Service\ProjectsManager;

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
        $this->initDemoLayer();

        $this->copyWithoutResize(12.7);
        $this->copyWithResize(100);
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
        //Copies rectangle with elementName "copyBox" into the InDesign document and changes it's width and height.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 50, $topPosition, 20, 20)
        );

        $topPosition += 25;
        //Places rectangle "copyBox" and changes it's width.
        $this->addCommand(
            new CopyBoxCommand('copyBox', 50, $topPosition, 40)
        );

        $topPosition += 5;
        //Places rectangle "copyBox" and changes it's height.
        $copyBox = new CopyBoxCommand('copyBox', 50, $topPosition);
        $copyBox->setHeight(100);
        $this->addCommand($copyBox);

        //Negative box sizes aren't allowed and throws an \Exception.
        try {
            $copyBox->setHeight(-10);
        } catch (\Exception $e) {
            ProjectsManager::getProject()
                           ->addPageMessage($e->getMessage(), true);
        }
    }
}
