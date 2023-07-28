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

namespace Mds\PimPrint\DemoBundle\Project\LocalizationDemo;

use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4PortraitTemplate;

/**
 * Class ExampleTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\LocalizationDemo
 * @see     \Mds\PimPrint\CoreBundle\InDesign\Template\BestPractices\ContentSizesPositions
 */
class ExampleTemplate extends A4PortraitTemplate
{
    /**
     * Width of content area
     *
     * @var float
     */
    const CONTENT_WIDTH = self::PAGE_WIDTH - self::PAGE_MARGIN_LEFT - self::PAGE_MARGIN_RIGHT;

    /**
     * Height of content area
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
     * Max yPos where is content placed in a page
     *
     * @var float
     */
    const CONTENT_BOTTOM = self::PAGE_HEIGHT - self::PAGE_MARGIN_BOTTOM;

    /**
     * Max xPos where is content placed in a page
     *
     * @var float
     */
    const CONTENT_RIGHT = self::PAGE_WIDTH - self::PAGE_MARGIN_RIGHT;

    /**
     * Content elements used in DataPrint projects
     *
     * @var string
     */
    const ELEMENT_COPYBOX = 'copyBox';

    const ELEMENT_HEADLINE = 'headline';

    const ELEMENT_TEXTBOX = 'textBox';

    const ELEMENT_IMAGE = 'image';

    const ELEMENT_TABLE = 'tableBox';

    /**
     * InDesign styles
     *
     * @var string
     */
    const STYLE_PARAGRAPH_COPYTEXT = 'CopyText';

    const STYLE_TABLE_CELL = 'ProductLabel';
}
