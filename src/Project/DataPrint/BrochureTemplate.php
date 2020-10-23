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

/**
 * CarBrochure demo template class.
 *
 * @see     \Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint
 */
class BrochureTemplate extends AbstractTemplate
{
    /**
     * Name of title textbox element in InDesign-Template
     *
     * @note  Best practice is to define constants for all InDesign-Template elements used in a project.
     *
     * @var string
     */
    const ELEMENT_TITLE = 'title';

    /**
     * Height of title textbox.
     */
    const TITLE_HEIGHT = 9.3;

    /**
     * Position and sizes for content columns
     *
     * @var int|float
     */
    const COLUMN_WIDTH = 90;

    const COLUMN_MARGIN = self::CONTENT_WIDTH - (2 * self::COLUMN_WIDTH);

    const COLUMN_RIGHT_ORIGIN_LEFT = self::CONTENT_ORIGIN_LEFT + self::COLUMN_WIDTH + self::COLUMN_MARGIN;

    /**
     * Sizes and positions of elements used in CarBrochure.
     *
     * @var float
     */
    const CAR_DETAIL_LOGO_WIDTH = 27.3;

    const CAR_DETAIL_LOGO_HEIGHT = 16;

    const CAR_VARIANT_IMAGE_WIDTH = 57;

    /**
     * Variant images are displayed in 4:3 format.
     *
     * @var float
     */
    const CAR_VARIANT_IMAGE_HEIGHT = self::CAR_VARIANT_IMAGE_WIDTH / 1.333333333333333;

    const CAR_VARIANT_SALES_TEXT_WIDTH = 31.325;

    /**
     * Y space between headline and layout line.
     *
     * @var float
     */
    const HEADLINE_LINE_Y_SPACE = 1.675;
}
