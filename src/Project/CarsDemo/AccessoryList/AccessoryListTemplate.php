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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryList;

use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;

/**
 * Class AccessoryListTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessorList
 */
class AccessoryListTemplate extends AbstractCarsDemoTemplate
{
    /**
     * InDesign style names
     */
    const CELL_STYLE_NO_BORDER = 'No border';

    /**
     * Misc size and positions
     */
    const IMAGE_SIZE = 25;

    const LOGO_WIDTH = 19;

    const LOGO_X_POSITION = 171;

    const TABLE_X_POSITION = 33.333;

    const TABLE_Y_POSITION = 5.118;

    const TABLE_WIDTH = 45;

    const TABLE_HEIGHT = 17.596;

    const TABLE_ROW_HEIGHT = 8;

    const COLUMN_WIDTH_SMALL = 20;

    const COLUMN_WIDTH_SPACER = 5;

    const COLUMN_WIDTH_WIDE = 25;

    const PRICE_X_POSITION = 133.333;
}
