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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarList;

use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;

/**
 * Class CarListTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\CarList
 */
class CarListTemplate extends AbstractCarsDemoTemplate
{
    /**
     * Misc size and positions
     */
    const TEXT_BOX_HEIGHT = 3.418;

    const IMAGE_BOX_HEIGHT = 40;

    const ROW_MARGIN = 20.622;

    const TOP_MARGIN = 16.078;

    const LINE_TOP = 25.253;
}
