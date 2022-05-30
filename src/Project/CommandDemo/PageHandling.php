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
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\Template;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Mds\PimPrint\CoreBundle\Service\SpecialChars;

/**
 * Demonstrates page handling functions in InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class PageHandling extends AbstractStrategy
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

        $this->firstPage();
        $this->createPageTemplate();

        $this->gotoPage();
        $this->nextPage();
        $this->addTemplateToPages();

        $this->checkNewPage();
    }

    /**
     * Generates content on first page.
     *
     * @return void
     * @throws \Exception
     */
    private function firstPage(): void
    {
        //When rendering starts the first page is the active page.
        $this->placeText('Content on page 1.');
    }

    /**
     * Template command is used to define commands which are automatically executed when a page in InDesign is accessed.
     * Template commands can be defined for single page and facing page documents separately to enable flexible layouts.
     *
     * @return void
     * @throws \Exception
     */
    private function createPageTemplate(): void
    {
        $template = new Template();

        //Create template element 'header' with bleed (sloping off the edge) elements.
        $headerLeft = new CopyBoxCommand('headerLeft', -7, -2);
        //Adds element 'headerLeft' to all single pages.
        $template->addCommand($headerLeft, Template::SIDE_SINGLE);
        //Adds element 'headerLeft' to facing page document side left.
        $template->addCommand($headerLeft, Template::SIDE_FACING_LEFT);

        $headerRight = new CopyBoxCommand('headerRight', 12.7, -2);
        //Adds element 'headerRight' to facing page document side right.
        $template->addCommand($headerRight, Template::SIDE_FACING_RIGHT);

        //Create template element 'page' for page number.
        $pageNumber = new CopyBoxCommand('pageNumber', 103, 288);
        //Default side for addCommand is Template::SIDE_SINGLE
        $template->addCommand($pageNumber);
        //To enable facing pages in this demo all template elements are also registered to facing page documents.
        $template->addCommand($pageNumber, Template::SIDE_FACING_BOTH);

        //Adds template element 'footer' with footer text
        $footer = new TextBox('footer', 12.7, 281.6, 184.55, 2.7);
        $footer->addParagraph(
            new Paragraph(
                sprintf(
                    'PimPrint Demo %s by mds.',
                    $this->specialChars()
                         ->utf8(SpecialChars::RIGHT_INDENT_TAB)
                )
            )
        );
        $template->addCommand($footer);
        $template->addCommand($footer, Template::SIDE_FACING_BOTH);

        //The template is registered in CommandQueue
        $this->addCommand($template);
    }

    /**
     * GoToPage command is used to jump directly to page numbers in InDesign document.
     *
     * @return void
     * @throws \Exception
     */
    private function gotoPage(): void
    {
        //Jumps to page 3 in InDesign document without applying the page template
        //defined in PageHandling::pageTemplate()
        $this->addCommand(new GoToPage(3, false));
        $this->placeText('Page 3 was initially created without page template.');

        //Jumps to page 5 in InDesign document with applying the page template
        //defined in method PageHandling::pageTemplate()
        $this->addCommand(new GoToPage(5, true));
        $this->placeText('Page 5 with page template.');
    }

    /**
     * NextPage command is used to jump to the next page in InDesign document.
     * Useful when generating a multi page publication without the knowledge of page amounts.
     *
     * @return void
     * @throws \Exception
     */
    private function nextPage(): void
    {
        //The current active page from PageHandling::gotoPage() is page 5.
        //Jumps to the next page (6) in InDesign document without applying the page template
        //defined in PageHandling::pageTemplate()
        $this->addCommand(new NextPage(false));
        $this->placeText('Jumped to next page without page template.');

        //Jumps to the next page (7) in InDesign document with applying the page template
        //defined in PageHandling::pageTemplate()
        $this->addCommand(new NextPage());
        $this->placeText('Jumped to next page with page template.');
    }

    /**
     * This method demonstrates the switch between pages and adding templates to the pages.
     *
     * @return void
     * @throws \Exception
     */
    private function addTemplateToPages()
    {
        //Jumps to the empty page 2, which was left out before, and add the template elements to it.
        $this->addCommand(new GoToPage(2));

        //Jumps to the already accessed page 3 without template elements and add the template elements now.
        $this->addCommand(new GoToPage(3));
        $this->placeText('Page template was added later in method addTemplateToPages().', 12.7, 21);

        //NextPage command also adds template elements.
        $this->addCommand(new NextPage());
    }

    /**
     * CheckNewPage command is used to move placed boxes automatically to the next page,
     * if the box exceeds the height of the page.
     *
     * @return void
     * @throws \Exception
     */
    private function checkNewPage(): void
    {
        //We generate page 8 with an overflow of content.
        $this->addCommand(new GoToPage(8));
        $this->placeText('Demonstration of page overflow');

        $text = "After this textbox we will place a very large copyBox which won't fit on the page. " .
            "The element is placed but exceeds page margins.";
        $this->placeText($text, 12.7, 100, 150, 100);

        //This box will exceed the page margins.
        $largeBox = new CopyBoxCommand('copyBox', 12.7, 120, 50, 200);
        $this->addCommand($largeBox);

        //We generate page 9 with the usage of CheckNewPage command to handle the overflow of content automatically.
        $this->addCommand(new GoToPage(9));
        $this->placeText('Demonstration of CheckNewPage command.');

        $text = "After this textbox we will place the same large copyBox as on page 8 which won't fit on the page. " .
            "This time we use the CheckNewPage command, so the element will automatically be placed on the next page.";
        $this->placeText($text, 12.7, 100, 150, 100);

        //We define a CheckNewPage command.
        $maxYPos = 285; //The template footer starts at 285mm y
        $newYPos = 20; //The box should be places at 20mm y on the new page.
        $checkNewPage = new CheckNewPage($maxYPos, $newYPos);

        //Large box is now placed automatically on the next page 10.
        $largeBox = new CopyBoxCommand('copyBox', 12.7, 120, 50, 200);
        //CheckNewPage command can be added as a component to all box placements commands.
        $largeBox->addComponent($checkNewPage);
        $this->addCommand($largeBox);

        //When a new page is created via CheckNewPage command this new page is the current page in InDesign document.
        //All following commands will be executed on this page.
        $this->placeText(
            'CheckNewPage created an automatic page break and placed the box on the next page.',
            12.7,
            12.7,
            180
        );

        //When a box with CheckNewPage command has enough space on the page no page break will be created.
        //For demonstration we jump back to page 9 and place a smaller box at the same position as the automatic
        //moved box.
        $this->addCommand(new GoToPage(9));
        $largeBox = new CopyBoxCommand('copyBox', 80, 120, 50, 100);
        //We add the same CheckNewPage command. But this time the box isn't moved to the next page because there is
        //enough space on the page.
        $largeBox->addComponent($checkNewPage);
        $this->addCommand($largeBox);

        //Just add a descriptive text.
        $this->placeText('This box had space on the current page and was placed here.', 135, 120, 50, 20);
    }
}
