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
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Command\VariableOutput;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variables\MaxValue;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variables\MinValue;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;

/**
 * Demonstrates relative positioning concepts in PimPrint.
 *
 * PimPrint provides the `Variable` command to set custom variables in InDesign.
 * You can define all position parameters relative to these variables.
 * You can also store the bounds of placed elements as variables to position elements relative to each other.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class RelativePositioning extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    public function build(): void
    {
        $this->initDemo();

        $this->manualVariables();
        $this->boxVariables();
        $this->mathVariables();
        $this->demoPage();
    }

    /**
     * You can define arbitrary variables in InDesign using the `Variable` command.
     * You can use these variables in top and left placement parameters.
     *
     * @return void
     * @throws \Exception
     */
    private function manualVariables(): void
    {
        $this->addCommand(new GoToPage());

        // Sets the variable "xPos" to 105 (10.5 cm).
        $this->addCommand(
            new Variable('xPos', 105)
        );

        // Sets the variable "yPos" to 120 (12 cm).
        $this->addCommand(
            new Variable('yPos', 120)
        );

        // When placing a box, you can use defined variables for positioning.
        // This box uses a static left position and a top position relative to the "yPos" variable.
        $box = $this->createDemoBox();
        $box->setLeft(12.7)
            ->setTopRelative('yPos');
        $this->addCommand($box);

        // This box uses a left position relative to the "xPos" variable and a static top position.
        $box = $this->createDemoBox();
        $box->setTop(12.7)
            ->setLeftRelative('xPos');
        $this->addCommand($box);

        // You can define both left and top positions as relative.
        $box = $this->createDemoBox();
        $box->setLeftRelative('xPos')
            ->setTopRelative('yPos');
        $this->addCommand($box);

        // If a relative variable is not defined, adding the command throws an \Exception.
        $box = $this->createDemoBox();
        $box->setTopRelative('notDefinedVariable');
        try {
            $this->addCommand($box);
        } catch (\Exception $e) {
            $this->placeText($e->getMessage(), 20, 20, 65, 10);
        }

        // The box is placed at left "'xPos' +30.5mm" and top "'yPos' -30.5mm".
        $box = $this->createDemoBox();
        $box->setLeftRelative('xPos', 30.5)
            ->setTopRelative('yPos', -30.5);
        $this->addCommand($box);

        // You can overwrite existing variables at any time.
        $this->addCommand(new Variable('xPos', 40));
        $this->addCommand(new Variable('yPos', 245));
        $box = $this->createDemoBox();
        $box->setLeftRelative('xPos')
            ->setTopRelative('yPos');
        $this->addCommand($box);
    }

    /**
     * When you place a box, you can store its bounds as dynamic variables.
     * You can use these variables in left and top positions like manually defined variables.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function boxVariables(): void
    {
        $this->addCommand(new GoToPage(2));
        $asset = $this->loadRandomAsset('%/Car Images/%');

        // For demonstration purposes, we randomize the image position and size.
        // (Open the InDesign page and generate the demo multiple times.)
        $image = new ImageBoxCommand(
            'image',
            rand(40, 60),
            rand(90, 110),
            rand(90, 110),
            rand(50, 70),
            $asset,
            ImageBoxCommand::FIT_FILL_PROPORTIONALLY
        );

        // When placing a box, you can assign its bounds as variables dynamically.
        $image->setVariable('topPos', Variable::POSITION_TOP)
              ->setVariable('bottomPos', Variable::POSITION_BOTTOM)
              ->setVariable('leftPos', Variable::POSITION_LEFT)
              ->setVariable('rightPos', Variable::POSITION_RIGHT);
        $this->addCommand($image);

        // For development purposes, you can output variables in the plugin.
        // By default, this output appears only in the 'dev' environment (see the optional setForce() option).
        // Outputs the variable 'topPos' in the plugin with an optional label.
        $this->addCommand(new VariableOutput('topPos', 'Image topPos'));


        // Like manually defined variables, you can place new elements relative to variables.
        // This allows you to position elements relative to other elements.

        // The box is placed at the top-left corner of the image.
        $box = $this->createDemoBox();
        // Keep in mind that elements use the top-left position for placement,
        // so you must consider the box size.
        $box->setTopRelative('topPos', $box->getHeight() * -1)
            ->setLeftRelative('leftPos', $box->getWidth() * -1);
        $this->addCommand($box);

        // The box is placed at the top-right corner of the image.
        $box = $this->createDemoBox();
        $box->setTopRelative('topPos', $box->getHeight() * -1)
            ->setLeftRelative('rightPos');
        $this->addCommand($box);

        // The box is placed at the bottom-left corner of the image.
        $box = $this->createDemoBox();
        $box->setTopRelative('bottomPos')
            ->setLeftRelative('leftPos', $box->getWidth() * -1);
        $this->addCommand($box);

        // The box is placed at the bottom-right corner of the image.
        $box = $this->createDemoBox();
        $box->setTopRelative('bottomPos')
            ->setLeftRelative('rightPos');
        $this->addCommand($box);

        // The box is placed above the image and centered on it.
        $box = $this->createDemoBox();
        $box->setTopRelative('topPos', $box->getHeight() * 2 * -1)
            ->setLeftRelative('leftPos', ($image->getWidth() / 2) - ($box->getWidth() / 2));
        $this->addCommand($box);

        // Some experimentation
        $box = $this->createDemoBox();
        $box->setTopRelative('bottomPos')
            ->setLeftRelative('leftPos', ($image->getWidth() / 2) - ($box->getWidth() / 2))
            ->setVariable('newTop', Variable::POSITION_BOTTOM)
            ->setVariable('leftBox1', Variable::POSITION_LEFT)
            ->setVariable('leftBox2', Variable::POSITION_RIGHT);
        $this->addCommand($box);

        $box = $this->createDemoBox();
        $box->setTopRelative('newTop')
            ->setLeftRelative('leftBox1', $box->getWidth() * -1);
        $this->addCommand($box);

        $box = $this->createDemoBox();
        $box->setTopRelative('newTop')
            ->setLeftRelative('leftBox2');
        $this->addCommand($box);
    }

    /**
     * Demonstrates the use of Variable\AbstractMath commands.
     * These commands set a variable in InDesign to the maximum or minimum value of other variables.
     * This is useful for building flexible multi-column layouts.
     *
     * @return void
     * @throws \Exception
     */
    private function mathVariables(): void
    {
        $this->addCommand(new GoToPage(3));

        // Example using manual variables
        $this->addCommand(new Variable('variable1', 20));
        $this->addCommand(new Variable('variable2', 40));
        $this->addCommand(new Variable('variable3', 60));

        // Sets "maxValue" to the maximum value of "variable1", "variable2", and "variable3".
        $this->addCommand(
            new MaxValue('maxValue', ['variable1', 'variable2', 'variable3'])
        );
        // Places an example box at the top position "maxValue".
        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString('Box placed at maxValue top Position')
             ->setWidth(50)
             ->setHeight(10)
             ->setTopRelative('maxValue');
        $this->addCommand($text);

        // Sets "minValue" to the maximum value of "variable1", "variable2", and "variable3".
        $this->addCommand(
            new MinValue('minValue', ['variable1', 'variable2', 'variable3'])
        );
        // Places an example box at the top position "minValue".
        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString('Box placed at minValue top Position')
             ->setWidth(50)
             ->setHeight(10)
             ->setTopRelative('minValue');
        $this->addCommand($text);

        // Example with two columns of elements with random heights.
        $this->addCommand(new Variable('topPos', 75));

        // Text with a random length in the first column.
        $text = new TextBoxCommand('textBox', 12.7);
        $text->setWidth(100)
             ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT_HEIGHT)
             ->addString($this->getDemoWords(rand(50, 120)))
             ->setTopRelative('topPos', 5)
             ->setVariable('bottomCol1', Variable::POSITION_BOTTOM);
        $this->addCommand($text);

        // Random box height in the second column.
        $box = $this->createDemoBox();
        $box->setLeft(120)
            ->setHeight(rand(20, 100))
            ->setTopRelative('topPos', 5)
            ->setVariable('bottomCol2', Variable::POSITION_BOTTOM);
        $this->addCommand($box);

        // Sets the variable "maxColumn" to the maximum of "bottomCol1" and "bottomCol2".
        $this->addCommand(
            new MaxValue('maxColumn', ['bottomCol1', 'bottomCol2'])
        );

        // Places the next box at the calculated "topPos".
        $text = new TextBoxCommand('textBox', 12.7);
        $text->setWidth(180)
             ->setHeight(10)
             ->addString("Box is placed as 5mm margin of calculated variable 'maxColumn'.")
             ->setTopRelative('maxColumn', 5);
        $this->addCommand($text);
    }

    /**
     * Demonstrates relative positioning when creating a flexible content page.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function demoPage(): void
    {
        $this->addCommand(new GoToPage(4));

        $leftPosition = 12.7;
        $topPosition = 12.7;
        $pageWidth = 184.6;

        $headline = new TextBoxCommand('headline', $leftPosition, $topPosition, 150, 100);
        $headline->addString($this->getDemoWords(9))
                 ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
                 ->setVariable('topPosition', Variable::POSITION_BOTTOM);
        $this->addCommand($headline);

        $styleBar = new CopyBoxCommand('copyBox', $leftPosition, null, $pageWidth);
        $styleBar->setTopRelative('topPosition', 3)
                 ->setVariable('topPosition', Variable::POSITION_BOTTOM)
                 ->setVariable('logoRight', Variable::POSITION_RIGHT);
        $this->addCommand($styleBar);

        $asset = $this->loadRandomAsset('/Brand Logos/', 500);
        $logo = new ImageBoxCommand('imageInline');
        $logo->setFit(ImageBoxCommand::FIT_PROPORTIONALLY)
             ->setAsset($asset)
             ->setWidth(20)
             ->setHeight(20)
             ->setTopRelative('topPosition', 3)
             ->setLeftRelative('logoRight', -20);
        $this->addCommand($logo);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBoxCommand('image', $leftPosition, null, 60, 40, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY)
                 ->setTopRelative('topPosition', 3)
                 ->setVariable('imageBottom', Variable::POSITION_BOTTOM)
                 ->setVariable('imageRight', Variable::POSITION_RIGHT);
        $this->addCommand($imageBox);

        $highlight = new Paragraph($this->getDemoWords(60), 'CopyText', 'Highlight');
        $text = new TextBoxCommand('textBox', null, null, $pageWidth - 63, 40);
        $text->addParagraph($highlight)
             ->setTopRelative('topPosition', 3)
             ->setLeftRelative('imageRight', 3);
        $this->addCommand($text);

        $text = new TextBoxCommand('colText', $leftPosition, null, $pageWidth, 100);
        $text->addString($this->getDemoText())
             ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
             ->setTopRelative('imageBottom', 3)
             ->setVariable('topPosition', Variable::POSITION_BOTTOM)
             ->setVariable('textRight', Variable::POSITION_RIGHT);
        $this->addCommand($text);

        $subHeadline = new TextBoxCommand('subHeadline', $leftPosition, null, $pageWidth, 100);
        $subHeadline->addString($this->getDemoText())
                    ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
                    ->setTopRelative('topPosition', 5)
                    ->setVariable('topPosition', Variable::POSITION_BOTTOM);
        $this->addCommand($subHeadline);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBoxCommand('image', $leftPosition, null, 90, 60, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY)
                 ->setTopRelative('topPosition', 3);
        $this->addCommand($imageBox);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBoxCommand('image', null, null, 90, 60, $asset);
        $imageBox->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY)
                 ->setTopRelative('topPosition', 3)
                 ->setLeftRelative('textRight', -90)
                 ->setVariable('imageBottom', Variable::POSITION_BOTTOM);
        $this->addCommand($imageBox);

        $text = new TextBoxCommand('colText', $leftPosition, null, $pageWidth, 100);
        $text->addString($this->getDemoText(4, 'long'))
             ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
             ->setTopRelative('imageBottom', 3)
             ->setVariable('topPosition', Variable::POSITION_BOTTOM);
        $this->addCommand($text);

        $styleBar = new CopyBoxCommand('copyBox', $leftPosition, null, $pageWidth);
        $styleBar->setTopRelative('topPosition', 3)
                 ->setVariable('topPosition', Variable::POSITION_BOTTOM)
                 ->setVariable('rightPosition', Variable::POSITION_RIGHT);
        $this->addCommand($styleBar);
    }

    /**
     * Creates a `CopyBox` element that is 2 × 2 cm.
     *
     * @return CopyBoxCommand
     * @throws \Exception
     */
    private function createDemoBox(): CopyBoxCommand
    {
        $box = new CopyBoxCommand('copyBox');
        $box->setWidth(20)
            ->setHeight(20);

        return $box;
    }
}
