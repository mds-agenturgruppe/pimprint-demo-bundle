<?php
/**
 * mds. Agenturgruppe GmbH
 *
 * This source file is available under the terms of the
 * mds. Commercial License (MCL)
 *
 * Full copyright and license information is available in
 * LICENSE.md, which is distributed with this source code.
 *
 * @copyright Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license   mds. Commercial License (MCL)
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
 * Demonstrates how to group multiple commands using the `GroupStart` and `GroupEnd` commands.
 * This will then create a group of elements in the InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Groups extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemo();
        $topPosition = 12.7;

        $this->simpleGroup($topPosition);
        $this->groupedCheckNewPage($topPosition);
    }

    /**
     * Boxes can be grouped in the InDesign document.
     *
     * @param float $topPosition the top-position for the boxes
     *
     * @return void
     * @throws \Exception
     */
    private function simpleGroup(float $topPosition): void
    {
        // Start a new group by passing a GroupStart command to CommandQueue.
        $this->addCommand(new GroupStart());

        // All the following elements will be grouped together in the InDesign document.
        $image = new CopyBoxCommand('image', 12.7, $topPosition);
        $image->setVariable('imageBottom', Variable::POSITION_BOTTOM);
        $this->addCommand($image);

        $text = new TextBoxCommand('copyText', 12.7);
        $text->addString("Simple Group")
             ->setWidth(29)
             ->setHeight(3.175)
             ->setTopRelative('imageBottom', 2);
        $this->addCommand($text);

        $this->addCommand(new CopyBoxCommand('copyBox', 60, $topPosition));
        $this->addCommand(new CopyBoxCommand('copyBox', 80, $topPosition));
        $topPosition += 15;
        $this->addCommand(new CopyBoxCommand('copyBox', 60, $topPosition));
        $this->addCommand(new CopyBoxCommand('copyBox', 80, $topPosition));

        // End the group by passing a GroupEnd command to CommandQueue.
        $this->addCommand(new GroupEnd());

        // Groups allow you to position all elements together.
        // In this example, we place all elements relative to each other at the top of the page.
        // When the group ends, we move the group to the correct position on the page.
        $this->addCommand(new GroupStart());
        $this->renderGroupElements('Group position');
        $topPosition += 50;
        $groupEnd = new GroupEnd(moveTo: true);
        $groupEnd->setTop($topPosition)
                 ->setLeft(12.7);
        $this->addCommand($groupEnd);

        // After placing or moving a group, you can ungroup all elements.
        // This allows easier positioning while keeping elements ungrouped in the final InDesign document.
        $this->addCommand(new GroupStart());
        $this->renderGroupElements('Ungrouped');
        // Position the group on the page and ungroup it after placement.
        $topPosition += 50;
        $groupEnd = new GroupEnd(moveTo: true, ungroupAfter: true);
        $groupEnd->setTop($topPosition)
                 ->setLeft(12.7);
        $this->addCommand($groupEnd);
    }

    /**
     * Groups are mainly used with the `CheckNewPage` command.
     * This moves all grouped elements to the next page if their bounds exceed the available page space.
     *
     * In this demo, we place the same group 10 times on the page.
     * By adding `CheckNewPage` to the `GroupEnd` command, the group moves
     * to the next page if there is not enough space left on the current page.
     *
     * @param float $topPosition the top-position on the new page
     *
     * @return void
     * @throws \Exception
     */
    private function groupedCheckNewPage(float $topPosition): void
    {
        $blockMargin = 10;
        $this->addCommand(new NextPage());

        // Define the yPos variable in the InDesign document for relative positioning of all groups.
        $this->addCommand(new Variable(Variable::VARIABLE_Y_POSITION, $topPosition - $blockMargin));

        // The CheckNewPage command defines the maximum y-position where content can be rendered on the page.
        // If an element is placed below this y-position, it moves to the next page
        // and uses the new y-position parameter value.
        $checkNewPage = new CheckNewPage(284, $topPosition);

        for ($i = 1; $i <= 10; $i++) {
            // Start a new group.
            $this->addCommand(new GroupStart());
            $this->renderGroupElements("Group $i");
            // In this example we position the complete group relative to the group before by using the yPos variable.
            $groupEnd = new GroupEnd(layoutBreakCommand: $checkNewPage, moveTo: true);
            // The position of the group can be set in GroupEnd Command
            $groupEnd->setLeft(12.7)
                // Groups can be positioned relatively as every other AbstractBox element.
                     ->setTopRelative(Variable::VARIABLE_Y_POSITION, $blockMargin);
            // Set the group bottom position as the new yPos variable in the InDesign document.
            $groupEnd->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);

            $this->addCommand($groupEnd);
        }
    }

    /**
     * Renders a group of elements, consisting of an image, label text, and multiple copy boxes.
     * Elements are placed relative to each other in the top-left corner of the page.
     *
     * @param string $label The text label to be rendered within the group.
     *
     * @return void
     * @throws \Exception
     */
    private function renderGroupElements(string $label): void
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

        $this->addCommand(new CopyBoxCommand('copyBox', 60 - 12.7, 0));
        $this->addCommand(new CopyBoxCommand('copyBox', 80 - 12.7, 0));
        $this->addCommand(new CopyBoxCommand('copyBox', 60 - 12.7, 15));
        $this->addCommand(new CopyBoxCommand('copyBox', 80 - 12.7, 15));
    }
}
