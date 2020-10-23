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
 * Class ListTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint
 */
class ListTemplate extends AbstractTemplate
{
    /**
     * Element names used in ListTemplate.
     *
     * @var string
     */
    const ELEMENT_TABLE = 'table';

    /**
     * Used table and cell styles.
     *
     * @var string
     */
    const STYLE_TABLE = 'table';

    const STYLE_TABLE_CELL_HEAD = 'tableHead';

    const STYLE_TABLE_CELL_HEAD_CENTER = 'tableHeadCenter';

    const STYLE_TABLE_CELL_ROW = 'tableRow';

    const STYLE_TABLE_CELL_ROW_CENTER = 'tableRowCenter';

    const STYLE_TABLE_CELL_ROW_RIGHT = 'tableRowRight';

    const STYLE_TABLE_CELL_PRICE = 'tablePrice';

    const STYLE_TABLE_CELL_FOOT = 'tableFoot';

    const TABLE_IMAGE_WIDTH = 21;

    /**
     * Table images are displayed in 4:3 format.
     *
     * @var float
     */
    const TABLE_IMAGE_HEIGHT = self::TABLE_IMAGE_WIDTH / 1.333333333333333;

    /**
     * Table and column sizes
     *
     * @var float
     */
    const TABLE_WIDTH = self::CONTENT_WIDTH;

    const COLUMN_WIDTH_IMAGE = self::TABLE_IMAGE_WIDTH + 2;

    const COLUMN_WIDTH_MANUFACTURER = 25.5;

    const COLUMN_WIDTH_YEAR = 11;

    const COLUMN_WIDTH_DOORS = 13.5;

    const COLUMN_WIDTH_PRICE = 19;

    const COLUMN_WIDTH_EAN = 23;

    const COLUMN_WIDTH_CONDITION = 20;

    const COLUMN_WIDTH_MILEAGE = 22;
}
