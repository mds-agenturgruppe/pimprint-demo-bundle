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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList;

use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;

/**
 * Class AccessoryPriceTableTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList
 */
class AccessoryPriceListTemplate extends AbstractCarsDemoTemplate
{
    /**
     * InDesign style names
     */
    const STYLE_TABLE = 'Pricelist';

    const STYLE_TABLE_CELL_HEADER = 'Pricelist header';

    const STYLE_TABLE_CELL_LEFT = 'Pricelist row left';

    /**
     * Misc size and positions
     */
    const TABLE_Y_POSITION = 41;

    const TABLE_IMAGE_WIDTH = 21;

    const TABLE_IMAGE_HEIGHT = self::TABLE_IMAGE_WIDTH / 4 * 3;
}
