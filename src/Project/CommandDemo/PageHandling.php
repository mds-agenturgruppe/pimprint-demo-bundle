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
 * Demonstrates page-handling functions in an InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class PageHandling extends AbstractStrategy
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

        $this->firstPage();
        $this->createPageTemplateCommands();

        $this->gotoPage();
        $this->nextPage();
        $this->addTemplateToPages();

        $this->checkNewPage();
    }

    /**
     * Generates content on the first page.
     *
     * @return void
     * @throws \Exception
     */
    private function firstPage(): void
    {
        // When rendering starts, the first page is the active page.
        $this->placeText('Content on page 1.');
    }

    /**
     * The Template command defines commands that run automatically when a page in InDesign is accessed.
     * You can define template commands separately for single-page and facing-page documents to enable flexible layouts.
     *
     * @return void
     * @throws \Exception
     */
    private function createPageTemplateCommands(): void
    {
        $template = new Template();

        // Creates the template element "header" with bleed elements that extend beyond the page edge.
        $headerLeft = new CopyBoxCommand('headerLeft', -5, -5);
        // Adds the element "headerLeft" to all single pages.
        $template->addCommand($headerLeft, Template::SIDE_SINGLE);
        // Adds the element "headerLeft" to the left side of a facing-page document.
        $template->addCommand($headerLeft, Template::SIDE_FACING_LEFT);

        $headerRight = new CopyBoxCommand('headerRight', 12.2, -5);
        // Adds the element "headerRight" to the right side of a facing-page document.
        $template->addCommand($headerRight, Template::SIDE_FACING_RIGHT);

        // Creates the template element "page" for the page number.
        $pageNumber = new CopyBoxCommand('pageNumber', 103, 288);
        // The default side for addCommand is Template::SIDE_SINGLE.
        $template->addCommand($pageNumber);
        // To enable facing pages in this demo, all template elements are also registered for facing-page documents.
        $template->addCommand($pageNumber, Template::SIDE_FACING_BOTH);

        // Adds the template element "footer" with text.
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

        // The template command is registered in the CommandQueue.
        $this->addCommand($template);
    }

    /**
     * The `GoToPage` command jumps directly to a page number in the InDesign document.
     *
     * @return void
     * @throws \Exception
     */
    private function gotoPage(): void
    {
        // Jumps to page 3 in the InDesign document without applying the page template
        // defined in PageHandling::pageTemplate().
        $this->addCommand(new GoToPage(page: 3, useTemplate: false));
        $this->placeText('Page 3 was initially created without page template.');

        // Jumps to page 5 in the InDesign document and applies the page template
        // defined in PageHandling::pageTemplate().
        $this->addCommand(new GoToPage(page: 5, useTemplate: true));
        $this->placeText('Page 5 with page template.');
    }

    /**
     * The NextPage command jumps to the next page in the InDesign document.
     * It is useful when generating a multipage publication without knowing the page count in advance.
     *
     * @return void
     * @throws \Exception
     */
    private function nextPage(): void
    {
        // The current active page from PageHandling::gotoPage() is page 5.
        // Jumps to the next page (6) in the InDesign document without applying the page template
        // defined in PageHandling::pageTemplate().
        $this->addCommand(new NextPage(false));
        $this->placeText('Jumped to next page without page template.');

        // Jumps to the next page (7) in the InDesign document and applies the page template
        // defined in PageHandling::pageTemplate().
        $this->addCommand(new NextPage());
        $this->placeText('Jumped to next page with page template.');
    }

    /**
     * This method demonstrates switching between pages and adding templates to pages.
     *
     * @return void
     * @throws \Exception
     */
    private function addTemplateToPages(): void
    {
        // Jumps to the empty page 2, which was skipped before, and adds the template elements.
        $this->addCommand(new GoToPage(2));

        // Jumps to the already accessed page 3 without template elements
        // and adds the template elements now.
        $this->addCommand(new GoToPage(3));
        $this->placeText('Page template was added later in method addTemplateToPages().', 12.7, 21);

        // The NextPage command also adds template elements.
        $this->addCommand(new NextPage());
    }

    /**
     * The `CheckNewPage` command moves placed boxes to the next page if they exceed the page height.
     *
     * @return void
     * @throws \Exception
     */
    private function checkNewPage(): void
    {
        // Generate page 8 with overflowing content.
        $this->addCommand(new GoToPage(8));
        $this->placeText('Demonstration of page overflow');

        $text = "After this textbox we place a very large copyBox that won't fit on the page. " .
            "The element is placed, but it exceeds the page margins.";
        $this->placeText($text, 12.7, 100, 150, 100);

        // This box exceeds the page margins.
        $largeBox = new CopyBoxCommand('copyBox', 12.7, 120, 50, 200);
        $this->addCommand($largeBox);

        // Generate page 9 and use CheckNewPage to handle overflow automatically.
        $this->addCommand(new GoToPage(9));
        $this->placeText('Demonstration of the CheckNewPage command.');

        $text = "After this textbox we place the same large copyBox as on page 8, which won't fit on the page. " .
            "This time we use the CheckNewPage command, so the element moves to the next page automatically.";
        $this->placeText($text, 12.7, 100, 150, 100);

        // Define a CheckNewPage command.
        $maxYPos = 285; // The template footer starts at 285mm y.
        $newYPos = 20;  // Place the box at 20mm y on the new page.
        $checkNewPage = new CheckNewPage($maxYPos, $newYPos);

        // The large box moves to the next page (page 10).
        $largeBox = new CopyBoxCommand('copyBox', 12.7, 120, 50, 200);
        // You can add CheckNewPage as a component to all box placement commands.
        $largeBox->addComponent($checkNewPage);
        $this->addCommand($largeBox);

        // CheckNewPage creates a page break and makes the new page the active page.
        // All following commands run on that page.
        $this->placeText(
            'CheckNewPage created an automatic page break and placed the box on the next page.',
            12.7,
            12.7,
            180
        );

        // If the box fits, CheckNewPage does not create a page break.
        // Jump back to page 9 and place a smaller box at the same position.
        $this->addCommand(new GoToPage(9));
        $largeBox = new CopyBoxCommand('copyBox', 80, 120, 50, 100);
        // Add the same CheckNewPage command. The box stays on the page because it fits.
        $largeBox->addComponent($checkNewPage);
        $this->addCommand($largeBox);

        // Add a short label.
        $this->placeText('This box fits on the current page and was placed here.', 135, 120, 50, 20);
    }
}
