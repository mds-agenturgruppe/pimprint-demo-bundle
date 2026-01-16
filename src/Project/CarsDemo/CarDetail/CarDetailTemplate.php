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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarDetail;

use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;

/**
 * Class CarDetailTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\CarDetail
 */
class CarDetailTemplate extends AbstractCarsDemoTemplate
{
    /**
     * InDesign style names
     */
    const STYLE_PARAGRAPH_PRICE = 'Prices|Price right';

    const STYLE_PARAGRAPH_ACCESSORY_PRICE = 'Prices|Price accessories';

    /**
     * Misc size and positions
     */
    const CAR_DETAILS_DETAIL_HEADLINE_Y_POSITION = 168.899;

    const MANUFACTURER_LOGO_X_POSITION = 184;

    const MAIN_IMAGE_Y_POSITION = 33.811;

    const MAIN_IMAGE_WIDTH = 123.333;

    const MAIN_IMAGE_HEIGHT = 85;

    const PRICE_X_POSITION = 160.635;

    const PRICE_Y_POSITION = 33.811;

    const PRICE_WIDTH = 45;

    const PRICE_HEIGHT = 10;

    const TAX_X_POSITION = 156.253;

    const TAX_Y_POSITION = 46;

    const TAX_WIDTH = 43.747;

    const GENERAL_DETAILS_Y_POSITION = 128.811;

    const GENERAL_DETAILS_WIDTH = 90;

    const GENERAL_DETAILS_HEIGHT = 31;

    const GENERAL_DETAILS_ROW_HEIGHT = 10;

    const GENERAL_DETAILS_LEFT_COLUMN_WIDTH = 23;

    const GENERAL_DETAILS_RIGHT_COLUMN_WIDTH = 66.667;

    const PRODUCTION_DETAILS_X_POSITION = 143.333;

    const PRODUCTION_DETAILS_Y_POSITION = 67.719;

    const PRODUCTION_DETAILS_WIDTH = 52;

    const PRODUCTION_DETAILS_HEIGHT = 60;

    const PRODUCTION_DETAILS_ROW_HEIGHT = 12.75;

    const PRODUCTION_DETAILS_RIGHT_COLUMN_WIDTH = 25;

    const PRODUCTION_DETAILS_LEFT_COLUMN_WIDTH = 31.5;

    const DIMENSION_DETAILS_Y_POSITION = 178.234;

    const DIMENSION_DETAILS_WIDTH = 57;

    const DIMENSION_DETAILS_HEIGHT = 29;

    const DIMENSION_DETAILS_ROW_HEIGHT = 7;

    const DIMENSION_DETAILS_LEFT_COLUMN_WIDTH = 26;

    const DIMENSION_DETAILS_RIGHT_COLUMN_WIDTH = 30.5;

    const CONDITION_DETAILS_X_POSITION = 110;

    const CONDITION_DETAILS_RIGHT_COLUMN_WIDTH = 67;

    const ENGINE_DETAILS_Y_POSITION = 211.322;

    const DESCRIPTION_X_POSITION = 76.667;

    const DESCRIPTION_TEXT_WIDTH = 123.333;

    const DESCRIPTION_TEXT_HEIGTH = 51;

    const DESCRIPTION_Y_POSITION = 168.899;

    const ACCESSORIES_Y_POSITION = 225;

    const ACCESSORY_NAME_WIDTH = 39.167;

    const ACCESSORY_NAME_HEIGHT = 8.498;

    const ACCESSORIES_X_POSITION_LEFT = 0;

    const ACCESSORIES_X_POSITION_RIGHT = CarDetailTemplate::ACCESSORIES_X_POSITION_LEFT + 66.666;

    const ACCESSORIES_Y_POSITION_BOTTOM = 36.933;

    const ACCESSORIES_Y_POSITION_TOP = 9.101;
}
