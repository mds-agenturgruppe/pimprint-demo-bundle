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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Template;

use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Template;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4PortraitTemplate;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\Service\SpecialChars;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class AbstractCarsDemoTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Template
 */
abstract class AbstractCarsDemoTemplate extends A4PortraitTemplate implements PageLayoutInterface
{
    /**
     * Best practice
     *
     * @see \Mds\PimPrint\CoreBundle\InDesign\Template\BestPractices\ContentSizesPositions
     */
    const CONTENT_WIDTH = self::PAGE_WIDTH - self::PAGE_MARGIN_LEFT - self::PAGE_MARGIN_RIGHT;

    const CONTENT_HEIGHT = self::PAGE_HEIGHT - self::PAGE_MARGIN_TOP - self::PAGE_MARGIN_BOTTOM;

    /**
     * Variable is used for automatic pagination with CheckPage
     */
    const CONTENT_ORIGIN_TOP = self::PAGE_MARGIN_TOP + 26.311;

    const CONTENT_ORIGIN_LEFT = self::PAGE_MARGIN_LEFT;

    const CONTENT_BOTTOM = self::PAGE_HEIGHT - self::PAGE_MARGIN_BOTTOM;

    const CONTENT_RIGHT = self::PAGE_WIDTH - self::PAGE_MARGIN_RIGHT;

    /**
     * CarDemo InDesign Template file margins
     */
    const PAGE_MARGIN_TOP = 10;

    const PAGE_MARGIN_BOTTOM = 15;

    const PAGE_MARGIN_LEFT = 10;

    const PAGE_MARGIN_RIGHT = 10;

    /**
     * InDesign element names from the "CarsDemo" template file used for rendering.
     */
    const ELEMENT_TEXTBOX = 'textbox';

    const ELEMENT_IMAGE = 'image';

    const ELEMENT_IMAGE_UP_RIGHT = 'imageUpRight';

    const ELEMENT_IMAGE_ROUNDED = 'imageRounded';

    const ELEMENT_TABLE = 'table';

    const ELEMENT_SPLIT_TABLE = 'splitTable';

    const ELEMENT_PRICE_TAG = 'priceTag';

    const ELEMENT_BOX_ICON = 'FooterIcon';

    /**
     * InDesign template file paragraph styles
     */
    const STYLE_PARAGRAPH_TITLE_PAGE = 'Title page';

    const STYLE_PARAGRAPH_TITLE_ACCESSORIES = 'Title accessories';

    const STYLE_PARAGRAPH_COPY = 'Copy';

    const STYLE_PARAGRAPH_COPY_SMALL = 'Copy small';

    const STYLE_PARAGRAPH_SUBHEADING = 'Subheading';

    const STYLE_PARAGRAPH_INFO_RIGHT = 'Info right';

    const STYLE_PARAGRAPH_INFO_SMALL_RIGHT = 'Info small right';

    const STYLE_PARAGRAPH_PRICE = 'Prices|Price';

    const STYLE_PARAGRAPH_PRICE_SPARE_PARTS = 'Prices|Price spare parts';

    const STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT = "Table|Table Copy big left";

    const STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT = "Table|Table Copy medium left";

    const STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT = 'Table|Table Copy small left';

    const STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT = 'Table|Table Copy small right';

    const STYLE_PARAGRAPH_TABLE_HEAD_LEFT = 'Table|Table Head left';

    const STYLE_PARAGRAPH_TABLE_COPY_LANGUAGE = 'Table|Table Copy Language';

    /**
     * InDesign template file character styles
     */

    const STYLE_CHARACTER_BOLD = 'Bold';

    const STYLE_CHARACTER_HIGHLIGHT_2 = 'Highlight 2';

    const STYLE_CHARACTER_HIGHLIGHT = 'Highlight';

    /**
     * InDesign template file table and cell styles
     */
    const STYLE_TABLE = 'SpareParts';

    const STYLE_TABLE_CELL_ROWS_LEFT = 'Table rows left';

    const STYLE_TABLE_CELL_HEADER_LEFT = 'Table header left';

    /**
     * Page footer definitions
     */
    const PAGE_FOOTER_Y_POS = self::CONTENT_BOTTOM + 2.266;

    const STYLE_PARAGRAPH_PAGINA = 'Pagina';

    const STYLE_PARAGRAPH_PAGINA_RIGHT = 'Pagina right';

    /**
     * Misc size and positions
     */
    const MANUFACTURER_LOGO_SIZE = 16;

    const BOX_MARGIN = 10;

    const BOX_MARGIN_SMALL = 2.5;

    const BOX_MARGIN_SMALLER = 2.3;

    const BOX_WIDTH = 56.667;

    /**
     * Chapter name rendered in the page footer
     *
     * @var string|null
     */
    private ?string $chapterName;

    /**
     * The CheckNewPage command creates a page break if an element is placed below `maxYPos,
     * which defines the page content bottom.
     *
     * @return CheckNewPage
     * @throws \Exception
     */
    public function getCheckNewPage(): CheckNewPage
    {
        return new CheckNewPage(
            AbstractCarsDemoTemplate::CONTENT_BOTTOM,
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_TOP,
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
        );
    }

    /**
     * Returns a `\Mds\PimPrint\CoreBundle\InDesign\Command\Template` with all commands to
     * create page layouts.
     *
     * Example shows how to define left- and right-side (facing-pages) and single-page layouts in parallel.
     *
     * This leads to more flexibility for the user because he can choose between single- and facing-pages by
     * the used InDesign document.
     *
     * @param string|null $chapterName
     *
     * @return Template
     * @throws \Exception
     */
    public function getPageLayoutCommands(?string $chapterName): Template
    {
        $this->chapterName = $chapterName;

        //Icon placed in footer
        $iconBox = new CopyBox(self::ELEMENT_BOX_ICON);
        $iconBox->setUseTemplatePosition(true);

        $template = new Template();

        //Register layouts for facing pages left and right
        $template->addCommand($this->getPageNumberLeft(), Template::SIDE_FACING_LEFT);
        $template->addCommand($this->getPageNumberRight(), Template::SIDE_FACING_RIGHT);
        $template->addCommand($iconBox, Template::SIDE_FACING_LEFT);

        //Register layouts for single pages
        $template->addCommand($this->getPageNumberLeft(), Template::SIDE_SINGLE);
        $template->addCommand($iconBox, Template::SIDE_SINGLE);

        return $template;
    }

    /**
     * The left-side page number uses a `TextBox` with automatic page numbering and the appended chapter name.
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getPageNumberLeft(): TextBox
    {
        $paragraph = new Text\Paragraph();
        $paragraph->setParagraphStyle(self::STYLE_PARAGRAPH_PAGINA);
        $paragraph->addText(SpecialChars::AUTO_PAGE_NUMBER, self::STYLE_CHARACTER_BOLD);
        if (isset($this->chapterName)) {
            $paragraph->addText(' | ' . $this->chapterName, self::STYLE_CHARACTER_HIGHLIGHT);
        }

        $textBox = new TextBox(
            self::ELEMENT_TEXTBOX,
            self::CONTENT_ORIGIN_LEFT,
            self::PAGE_FOOTER_Y_POS,
            71,
            3
        );
        $textBox->addParagraph($paragraph);

        return $textBox;
    }

    /**
     * The right-side page number uses a `TextBox` with automatic page numbering and the appended chapter name.
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getPageNumberRight(): TextBox
    {
        $paragraph = new Text\Paragraph();
        $paragraph->setParagraphStyle(self::STYLE_PARAGRAPH_PAGINA_RIGHT);
        if (isset($this->chapterName)) {
            $paragraph->addText($this->chapterName . ' | ', self::STYLE_CHARACTER_HIGHLIGHT);
        }
        $paragraph->addText(SpecialChars::AUTO_PAGE_NUMBER, self::STYLE_CHARACTER_BOLD);

        $textBox = new TextBox(
            self::ELEMENT_TEXTBOX,
            self::CONTENT_RIGHT - 71,
            self::PAGE_FOOTER_Y_POS,
            71,
            3
        );
        $textBox->addParagraph($paragraph);

        return $textBox;
    }
}
