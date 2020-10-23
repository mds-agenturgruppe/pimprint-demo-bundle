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

use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variables\MaxValue;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variables\MinValue;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;

/**
 * Demonstrates the functionality and concepts in PimPrint for relative positioning of elements to each other.
 *
 * PimPrint offers the Variable command to set arbitrary variables in InDesign.
 * All position parameters can be defined relative to this variables.
 * On top of this the bounds of placed elements can be dynamically defined as variables to be able to position
 * elements relative to each other.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class RelativePositioning extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemoLayer();

        $this->manualVariables();
        $this->boxVariables();
        $this->mathVariables();
        $this->demoPage();
    }

    /**
     * With Variable command arbitrary variables can be defined in InDesign.
     * In top and left placement parameters this variables can be used.
     *
     * @return void
     * @throws \Exception
     */
    private function manualVariables(): void
    {
        $this->addCommand(new GoToPage());

        //Sets variable with name 'xPos' with value 105 (10.5cm)
        $this->addCommand(
            new Variable('xPos', 105)
        );

        //Sets variable with name 'yPos' with value 120 (12cm)
        $this->addCommand(
            new Variable('yPos', 120)
        );

        //When placing a box defined variables can be used for placement.
        //This box is placed at static left and relative top yPos variable position.
        $box = $this->createDemoBox();
        $box->setLeft(12.7)
            ->setTopRelative('yPos');
        $this->addCommand($box);

        //This box is placed at relative left xPos variable and static top position
        $box = $this->createDemoBox();
        $box->setTop(12.7)
            ->setLeftRelative('xPos');
        $this->addCommand($box);

        //Both left and top position can be relative.
        $box = $this->createDemoBox();
        $box->setLeftRelative('xPos')
            ->setTopRelative('yPos');
        $this->addCommand($box);

        //Previous not defined variables in relative positioning creates an \Exception when adding the command.
        $box = $this->createDemoBox();
        $box->setTopRelative('notDefinedVariable');
        try {
            $this->addCommand($box);
        } catch (\Exception $e) {
            $this->placeText($e->getMessage(), 20, 20, 65, 10);
        }

        //Box will be placed at left "'xPos' +30.5mm" and top "'yPos' -30.5mm"
        $box = $this->createDemoBox();
        $box->setLeftRelative('xPos', 30.5)
            ->setTopRelative('yPos', -30.5);
        $this->addCommand($box);

        //Existing varibales can we overwritten anytime.
        $this->addCommand(new Variable('xPos', 40));
        $this->addCommand(new Variable('yPos', 245));
        $box = $this->createDemoBox();
        $box->setLeftRelative('xPos')
            ->setTopRelative('yPos');
        $this->addCommand($box);
    }

    /**
     * When a box is placed the bounds of the box can be defined as dynamically variables.
     * This variables can be used in left and top positions as manual defined variables.
     *
     * @return void
     * @throws \Exception
     */
    private function boxVariables(): void
    {
        $this->addCommand(new GoToPage(2));
        $asset = $this->loadRandomAsset('%/Car Images/%');

        //For demonstration purpose we randomize the image position and size.
        //(Open the InDesign page and generate the demo multiple times.)
        $image = new ImageBoxCommand(
            'image',
            rand(40, 60),
            rand(90, 110),
            rand(90, 110),
            rand(50, 70),
            $asset,
            ImageBoxCommand::FIT_FILL_PROPORTIONALLY
        );

        //When placing a box the bounds can be dynamically assigned as variables.
        $image->setVariable('topPos', Variable::POSITION_TOP)
              ->setVariable('bottomPos', Variable::POSITION_BOTTOM)
              ->setVariable('leftPos', Variable::POSITION_LEFT)
              ->setVariable('rightPos', Variable::POSITION_RIGHT);
        $this->addCommand($image);

        //As manual defined variables new elements can be placed relative to the the variables.
        //By this elements can be positioned relative to other elements.

        //Box is placed at top-left corner of image
        $box = $this->createDemoBox();
        //Keep in mind that elements are placed by the left-top position. So we have to pay attention to the box size.
        $box->setTopRelative('topPos', $box->getHeight() * -1)
            ->setLeftRelative('leftPos', $box->getWidth() * -1);
        $this->addCommand($box);

        //Box is places at top-right corner of image
        $box = $this->createDemoBox();
        $box->setTopRelative('topPos', $box->getHeight() * -1)
            ->setLeftRelative('rightPos');
        $this->addCommand($box);

        //Box is placed at bottom-left corner of image
        $box = $this->createDemoBox();
        $box->setTopRelative('bottomPos')
            ->setLeftRelative('leftPos', $box->getWidth() * -1);
        $this->addCommand($box);

        //Box is placed at bottom-right corner of image
        $box = $this->createDemoBox();
        $box->setTopRelative('bottomPos')
            ->setLeftRelative('rightPos');
        $this->addCommand($box);

        //Box is placed above the image and centered to it.
        $box = $this->createDemoBox();
        $box->setTopRelative('topPos', $box->getHeight() * 2 * -1)
            ->setLeftRelative('leftPos', ($image->getWidth() / 2) - ($box->getWidth() / 2));
        $this->addCommand($box);

        //Some playing around...
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
     * Demonstrates the usage of Variable\AbstractMath commands. With this commands InDesign sets a variable to
     * maximum or minimum value of other variables. This is useful to build multi column flexible layouts.
     *
     * @return void
     * @throws \Exception
     */
    private function mathVariables()
    {
        $this->addCommand(new GoToPage(3));

        //Example with manual variables
        $this->addCommand(new Variable('variable1', 20));
        $this->addCommand(new Variable('variable2', 40));
        $this->addCommand(new Variable('variable3', 60));

        //Set 'maxValue' to the maximum value of 'variable1', 'variable2' and 'variable3'
        $this->addCommand(
            new MaxValue('maxValue', ['variable1', 'variable2', 'variable3'])
        );
        //Place a example box at top 'maxValue'
        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString('Box placed at maxValue top Position')
             ->setWidth(50)
             ->setHeight(10)
             ->setTopRelative('maxValue');
        $this->addCommand($text);

        //Set 'minValue' to the minimum value of 'variable1', 'variable2' and 'variable3'
        $this->addCommand(
            new MinValue('minValue', ['variable1', 'variable2', 'variable3'])
        );
        //Place a example box at top 'minValue'
        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString('Box placed at minValue top Position')
             ->setWidth(50)
             ->setHeight(10)
             ->setTopRelative('minValue');
        $this->addCommand($text);

        //Example with two 'columns' of elements with random height
        $this->addCommand(new Variable('topPos', 75));

        //Text with random length in first 'column'
        $text = new TextBoxCommand('textBox', 12.7);
        $text->setWidth(100)
             ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT_HEIGHT)
             ->addString($this->getDemoWords(rand(50, 120)))
             ->setTopRelative('topPos', 5)
             ->setVariable('bottomCol1', Variable::POSITION_BOTTOM);
        $this->addCommand($text);

        //Random amount of boxed in second 'column'
        for ($i = 0; $i <= rand(1, 2); $i++) {
            $box = $this->createDemoBox();
            $box->setLeft(120)
                ->setTopRelative('topPos', 5)
                ->setVariable('topPos', Variable::POSITION_BOTTOM)
                ->setVariable('bottomCol2', Variable::POSITION_BOTTOM);
            $this->addCommand($box);
        }

        //Set variable 'maxColumn' to the maximum of 'bottomCol1' and 'bottomCol2'
        $this->addCommand(
            new MaxValue('maxColumn', ['bottomCol1', 'bottomCol2'])
        );

        //Place the next box at calculated 'topPos'
        $text = new TextBoxCommand('textBox', 12.7);
        $text->setWidth(180)
             ->setHeight(10)
             ->addString("Box is placed as 5mm margin of calculated variable 'maxColumn'.")
             ->setTopRelative('maxColumn', 5);
        $this->addCommand($text);
    }

    /**
     * Demonstrates the usage of relative positioning when  creating a flexible a content page.
     *
     * @return void
     * @throws \Exception
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

        $highlight = new Paragraph($this->getDemoWords(50), 'CopyText', 'Highlight');
        $text = new TextBoxCommand('textBox', null, null, $pageWidth - 63, 40);
        $text->addParagraph($highlight)
             ->setTopRelative('topPosition', 3)
             ->setLeftRelative('imageRight', 3);
        $this->addCommand($text);

        $text = new TextBoxCommand('colText', $leftPosition, null, $pageWidth, 100);
        $text->addString($this->getDemoText(2, 'short'))
             ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
             ->setTopRelative('imageBottom', 3)
             ->setVariable('topPosition', Variable::POSITION_BOTTOM)
             ->setVariable('textRight', Variable::POSITION_RIGHT);
        $this->addCommand($text);

        $subHeadline = new TextBoxCommand('subHeadline', $leftPosition, null, $pageWidth, 100);
        $subHeadline->addString($this->getDemoWords(8))
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
        $text->addString($this->getDemoText(2))
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
     * Creates a copyBox element with 2x2 cm.
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
