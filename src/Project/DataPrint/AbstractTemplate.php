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

namespace Mds\PimPrint\DemoBundle\Project\DataPrint;

use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4PortraitTemplate;

/**
 * Abstract DataPrint template class with page size and layout definitions used in all DataPrint demos.
 *
 * @note    Template classes are a best practice to define constants with elementNames, positions, margins, etc.
 *          With these files layout adaptions regarding used elements or positions can be made in a central file.
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint
 */
class AbstractTemplate extends A4PortraitTemplate
{
    /**
     * Content sizes and positions
     *
     * @var float
     * @see \Mds\PimPrint\CoreBundle\InDesign\Template\BestPractices\ContentSizesPositions
     */
    const CONTENT_WIDTH = self::PAGE_WIDTH - self::PAGE_MARGIN_LEFT - self::PAGE_MARGIN_RIGHT;

    const CONTENT_HEIGHT = self::PAGE_HEIGHT - self::PAGE_MARGIN_TOP - self::PAGE_MARGIN_BOTTOM;

    const CONTENT_ORIGIN_TOP = self::PAGE_MARGIN_TOP;

    const CONTENT_ORIGIN_LEFT = self::PAGE_MARGIN_LEFT;

    const CONTENT_BOTTOM = self::PAGE_HEIGHT - self::PAGE_MARGIN_BOTTOM;

    const CONTENT_RIGHT = self::PAGE_WIDTH - self::PAGE_MARGIN_RIGHT;

    /**
     * Common Y in mm space between elements.
     *
     * @var int
     */
    const ELEMENT_Y_SPACE = 4;

    /**
     * Y space in mm between a block.
     *
     * @var int
     */
    const BLOCK_Y_SPACE = 6;

    /**
     * Element name, sizes and positions for layout bars.
     *
     * @var string|float|int
     */
    const ELEMENT_LAYOUT_BAR = 'layoutBar';

    const LAYOUT_BAR_WIDTH = 150;

    const LAYOUT_BAR_HEIGHT = 8.2;

    const LAYOUT_BAR_LEFT_LEFT = 0;

    const LAYOUT_BAR_RIGHT_LEFT = self::PAGE_WIDTH - self::LAYOUT_BAR_HEIGHT;

    const LAYOUT_BAR_TOP = self::LAYOUT_BAR_WIDTH;

    /**
     * Element name, sizes and positions for footers.
     *
     * @var string|float|int
     */
    const ELEMENT_FOOTER_LEFT = 'footerLeft';

    const ELEMENT_FOOTER_RIGHT = 'footerRight';

    const FOOTER_WIDTH = 92.3;

    const FOOTER_HEIGHT = 8;

    const FOOTER_TOP = 289;

    const FOOTER_LEFT_LEFT = self::PAGE_MARGIN_LEFT;

    const FOOTER_RIGHT_LEFT = self::PAGE_WIDTH - self::PAGE_MARGIN_RIGHT - self::FOOTER_WIDTH;

    const FOOTER_IMAGE_WIDTH = 18.449;

    const FOOTER_IMAGE_HEIGHT = 10;

    const FOOTER_IMAGE_TOP = self::PAGE_HEIGHT - self::PAGE_MARGIN_BOTTOM;

    const FOOTER_IMAGE_LEFT = (self::PAGE_WIDTH / 2) - (self::FOOTER_IMAGE_WIDTH / 2);

    /**
     * Content elements used in DataPrint projects
     *
     * @var string
     */
    const ELEMENT_SUBTITLE = 'subTitle';

    const ELEMENT_HEADLINE = 'headline';

    const ELEMENT_COPYTEXT = 'copyText';

    const ELEMENT_COPYTEXT_BOTTOM = 'copyTextBottom';

    const ELEMENT_STYLE_BOX = 'styleBox';

    const ELEMENT_IMAGE = 'image';

    const ELEMENT_IMAGE_ROUNDED = 'imageRounded';

    const ELEMENT_IMAGE_FLOW = 'imageFlow';

    /**
     * InDesign styles
     *
     * @var string
     */
    const STYLE_PARAGRAPH_FOOTER = 'footer';

    const STYLE_PARAGRAPH_FOOTER_RIGHT = 'footerRight';

    const STYLE_PARAGRAPH_SUBTITLE = 'subTitle';

    const STYLE_PARAGRAPH_HEADLINE = 'headline';

    const STYLE_PARAGRAPH_COPYTEXT = 'copyText';

    const STYLE_PARAGRAPH_PRICE = 'price';

    const STYLE_CHARACTER_PAGINA = 'pagina';

    const STYLE_CHARACTER_SPACER = 'spacer';
}
