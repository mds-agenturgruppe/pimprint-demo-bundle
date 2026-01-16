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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto;

use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\TableDto;

/**
 * Class CarContentDto
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto
 */
class CarContentDto
{
    /**
     * Description of the car
     *
     * @var string|null
     */
    public ?string $description;

    /**
     * Price of the car
     *
     * @var string|null
     */
    public ?string $price;

    /**
     * General details of the car
     *
     * @var TableDto
     */
    public TableDto $generalDto;

    /**
     * Engine details of the car
     *
     * @var TableDto
     */
    public TableDto $engineDto;

    /**
     * Production details of the car
     *
     * @var TableDto
     */
    public TableDto $productionDto;

    /**
     * Condition details of the car
     *
     * @var TableDto
     */
    public TableDto $conditionDto;

    /**
     * Dimension details of the car
     *
     * @var TableDto
     */
    public TableDto $dimensionDto;

    /**
     * Accessories of the car
     *
     * @var array
     */
    public array $accessories = [];
}
