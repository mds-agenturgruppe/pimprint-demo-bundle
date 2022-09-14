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

use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupEnd;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupStart;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;

/**
 * Demonstrates grouping with GroupStart and GroupEnd command.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Groups extends AbstractStrategy
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
        $topPosition = 12.7;

        $this->simpleGroup($topPosition);
        $this->groupedCheckNewPage($topPosition);
    }

    /**
     * Boxes can be grouped together in the InDesign document.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function simpleGroup(float $topPosition): void
    {
        //Start a new group by passing a GroupStart command to CommandQueue.
        $this->addCommand(new GroupStart());

        //All following elements will be grouped together in InDesign document.
        $image = new CopyBoxCommand('image', 12.7, $topPosition);
        $image->setVariable('imageBottom', Variable::POSITION_BOTTOM);
        $this->addCommand($image);

        $text = new TextBoxCommand('copyText', 12.7);
        $text->addString("Simple Group")
             ->setWidth(29)
             ->setHeight(3.175)
             ->setTopRelative('imageBottom', 2);
        $this->addCommand($text);

        $this->addCommand(new CopyBoxCommand('copyBox', 50, $topPosition));
        $this->addCommand(new CopyBoxCommand('copyBox', 80, $topPosition));
        $topPosition += 15;
        $this->addCommand(new CopyBoxCommand('copyBox', 50, $topPosition));
        $this->addCommand(new CopyBoxCommand('copyBox', 80, $topPosition));

        //End the group by passing a GroupEnd command to CommandQueue.
        //All elements are positioned at there defined position and are grouped together.
        $this->addCommand(new GroupEnd());

        //Groups can be used to position all elements in the group together.
        //For this example we place all elements relative to each other at the top of the page.
        //When ending the group we place the group on the right position on the page.
        $this->addCommand(new GroupStart());
        $this->renderGroupElements('Group position');
        //The complete group is positioned on the page.
        $topPosition += 50;
        $groupEnd = new GroupEnd(null, true);
        $groupEnd->setTop($topPosition)
                 ->setLeft(12.7);
        $this->addCommand($groupEnd);

        //After placing an or moving a group all elements can be ungrouped. This can be used for easier positioning
        //but to have elements ungrouped in the final InDesign document.
        $this->addCommand(new GroupStart());
        $this->renderGroupElements('Ungrouped');
        //The complete group is positioned on the page and ungrouped after placement.
        $topPosition += 50;
        $groupEnd = new GroupEnd(null, true, true);
        $groupEnd->setTop($topPosition)
                 ->setLeft(12.7);
        $this->addCommand($groupEnd);
    }

    /**
     * Groups are mainly used in combination with CheckNewPage command. It allows to move all grouped elements
     * automatically to the next page if the bounds would exceed the defined page space.
     *
     * In this demo we place 10 times the same group on the page. By adding the CheckNewPage to the GroupEnd command
     * groups are moved to the next page if there isn't enough space left on current page.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function groupedCheckNewPage(float $topPosition): void
    {
        $blockMargin = 10;
        $this->addCommand(new NextPage());

        //Define yPos variable in InDesign document for relative positioning of all groups.
        $this->addCommand(new Variable(Variable::VARIABLE_Y_POSITION, $topPosition - $blockMargin));

        //CheckNewPage command defined the maximum y-Position where content can be rendered on the page.
        //If a element would be placed underneath this y-Position it is placed on the next page at then new y-Position
        //parameter value.
        $checkNewPage = new CheckNewPage(284, $topPosition);

        for ($i = 1; $i <= 10; $i++) {
            //Start a new group.
            $this->addCommand(new GroupStart());
            $this->renderGroupElements("Group $i");
            //In this example we position the complete group relative to the group before by using the yPos variable.
            $groupEnd = new GroupEnd($checkNewPage, true);
            //Position of the group can be set in GroupEnd Command
            $groupEnd->setLeft(12.7)
                //Groups can be positioned relative like all other AbstractBox
                     ->setTopRelative(Variable::VARIABLE_Y_POSITION, $blockMargin);
            //Set group bottom as new yPos variable in InDesign document
            $groupEnd->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);

            $this->addCommand($groupEnd);
        }
    }

    /**
     * Creates demo elements for a group example.
     * Elements will be placed in the top left corner of the page relative to each other.
     *
     * @param string $label
     *
     * @throws \Exception
     */
    private function renderGroupElements(string $label)
    {
        $image = new CopyBoxCommand('image', 0, 0);
        $image->setVariable('imageBottom', Variable::POSITION_BOTTOM)
              ->setVariable('imageLeft', Variable::POSITION_LEFT);
        $this->addCommand($image);

        $text = new TextBoxCommand('copyText', 0);
        $text->addString($label)
             ->setWidth(29)
             ->setHeight(3.175)
             ->setTopRelative('imageBottom', 2)
             ->setLeftRelative('imageLeft');
        $this->addCommand($text);

        $this->addCommand(new CopyBoxCommand('copyBox', 50 - 12.7, 0));
        $this->addCommand(new CopyBoxCommand('copyBox', 80 - 12.7, 0));
        $this->addCommand(new CopyBoxCommand('copyBox', 50 - 12.7, 15));
        $this->addCommand(new CopyBoxCommand('copyBox', 80 - 12.7, 15));
    }
}
