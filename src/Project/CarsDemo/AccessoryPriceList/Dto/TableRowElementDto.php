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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\Dto;

use Mds\PimPrint\CoreBundle\InDesign\Text;

/**
 * Class TableRowElementDto
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\Dto
 */
class TableRowElementDto
{
    /**
     * Image to display in table row
     *
     * @var Text|null
     */
    public ?Text $image = null;

    /**
     * Ean cell content
     *
     * @var string
     */
    public string $ean = '';

    /**
     * Name cell content
     *
     * @var Text|null
     */
    public ?Text $name = null;

    /**
     * Availability cell content
     *
     * @var Text|null
     */
    public ?Text $availability = null;

    /**
     * Condition cell content
     *
     * @var Text|null
     */
    public ?Text $condition = null;

    /**
     * Milage cell content
     *
     * @var string
     */
    public string $milage = '';

    /**
     * Price cell content
     *
     * @var string
     */
    public string $price = '';
}
