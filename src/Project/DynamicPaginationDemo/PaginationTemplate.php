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

namespace Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo;

use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewColumn;
use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4PortraitTemplate;

/**
 * Class PaginationTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo
 */
class PaginationTemplate extends A4PortraitTemplate
{
    /**
     * Width of the content area
     *
     * @var float
     */
    const CONTENT_WIDTH = self::PAGE_WIDTH - self::PAGE_MARGIN_LEFT - self::PAGE_MARGIN_RIGHT;

    /**
     * Height of the content area
     *
     * @var float
     */
    const CONTENT_HEIGHT = self::PAGE_HEIGHT - self::PAGE_MARGIN_TOP - self::PAGE_MARGIN_BOTTOM;

    /**
     * Top yPos where content starts on a page
     *
     * @var float
     */
    const CONTENT_ORIGIN_TOP = self::PAGE_MARGIN_TOP;

    /**
     * Top xPos where content starts on a page
     *
     * @var float
     */
    const CONTENT_ORIGIN_LEFT = self::PAGE_MARGIN_LEFT;

    /**
     * Max yPos where is content placed on a page
     *
     * @var float
     */
    const CONTENT_BOTTOM = self::PAGE_HEIGHT - self::PAGE_MARGIN_BOTTOM;

    /**
     * Max xPos where is content placed on a page
     *
     * @var float
     */
    const CONTENT_RIGHT = self::PAGE_WIDTH - self::PAGE_MARGIN_RIGHT;

    /**
     * Defines the width of a layout column.
     * Set the value to:
     * - 60: three columns fit on an A4 portrait page
     *
     * @var int
     */
    const COLUMN_WITH = 90;
//    const COLUMN_WITH = 60; //Smaller column; use with SPLIT_COLUMN_MAX_X 150

    /**
     * Margin between layout columns
     *
     * @var int
     */
    const COLUMN_MARGIN = 4.6;

    /**
     * Margin used for margin between elements
     *
     * @var int
     */
    const BOX_MARGIN = 5;

    /**
     * CheckNewColumn maxXPos defines the maximum x-position where columns can be placed.
     * Change value to:
     * - 100: only one column with width `90` fits
     *
     * @var int|null
     */
    const SPLIT_COLUMN_MAX_X = null;
//    const SPLIT_COLUMN_MAX_X = 150; //Whitespace on the right page side

    /**
     * MarginOffset moves the group to a new column if less space remains on the page after the group ends.
     * Set the values to:
     * - 0, 100, 50
     *
     * @var int
     */
    const SPLIT_HEIGHT_MARGIN_OFFSET = 25;
//    const SPLIT_HEIGHT_MARGIN_OFFSET = 0;
//    const SPLIT_HEIGHT_MARGIN_OFFSET = 100;

    /**
     * Content elements used in DataPrint projects
     */
    const ELEMENT_HEADLINE = 'headline';

    const ELEMENT_TEXTBOX = 'textBox';

    const ELEMENT_IMAGE = 'image';

    const ELEMENT_TABLE = 'tableBox';

    const ELEMENT_HEADER_LEFT = 'headerLeft';

    const ELEMENT_HEADER_RIGHT = 'headerRight';

    /**
     * InDesign styles
     */
    const TABLE_CELL_HEAD = 'TableHead';

    const TABLE_CELL_CONTENT = 'TableCell';

    const PARAGRAPH_HEADER_TEXT_LEFT = 'SubHeadline_1';

    const PARAGRAPH_HEADER_TEXT_RIGHT = 'SubHeadline_1_right';

    /**
     * Returns CheckNewPage command that matches the layout page
     *
     * @param int|float|null $marginOffset
     *
     * @return CheckNewPage
     * @throws \Exception
     */
    public function getCheckNewPage(int|float $marginOffset = null): CheckNewPage
    {
        $command = new CheckNewPage(
            PaginationTemplate::CONTENT_BOTTOM,
            PaginationTemplate::CONTENT_ORIGIN_TOP,
            PaginationTemplate::CONTENT_ORIGIN_LEFT,
            $marginOffset,
        );

        $command->setNewPosXFacingPages(
            PaginationTemplate::CONTENT_ORIGIN_LEFT,
            //For demo purposes, we place the content 5 mm further to the right on right-hand pages
            PaginationTemplate::CONTENT_ORIGIN_LEFT + 5
        );

        return $command;
    }

    /**
     * Returns the CheckNewColumn command that matches the layout column and page
     *
     * @param int|float|null $marginOffset
     *
     * @return CheckNewColumn
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage::setMarginOffset
     */
    public function getCheckNewColumn(int|float $marginOffset = null): CheckNewColumn
    {
        return new CheckNewColumn(
            $this->getCheckNewPage($marginOffset),
            self::COLUMN_WITH,
            self::COLUMN_MARGIN,
            self::SPLIT_COLUMN_MAX_X,
        );
    }
}
