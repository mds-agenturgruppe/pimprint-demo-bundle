<?php
/**
 * mds. Agenturgruppe GmbH
 *
 * This source file is available under the terms of the
 * mds. Commercial License (MCL)
 *
 * Full copyright and license information is available in
 * LICENSE.md, which is distributed with this source code.
 *
 * @copyright Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license   mds. Commercial License (MCL)
 */

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto;

use App\Model\Product\Car;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\PriceFormatter;
use Pimcore\Model\DataObject\Objectbrick\Data\Dimensions;
use Pimcore\Model\DataObject\Objectbrick\Data\Engine;
use Pimcore\Model\DataObject\Objectbrick\Data\SaleInformation;
use Pimcore\Model\DataObject\Objectbrick\Data\Transmission;
use Symfony\Component\Intl\Countries;

/**
 * Class CarInformationDto
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto
 */
class CarInformationDto
{
    /**
     * Price
     *
     * @var string|null
     */
    public ?string $price = null;

    /**
     * Power
     *
     * @var string|null
     */
    public ?string $power = null;

    /**
     * Production year
     *
     * @var int|null
     */
    public ?int $productionYear = null;

    /**
     * Body style
     *
     * @var string|null
     */
    public ?string $bodyStyle = null;

    /**
     * Manufacturer name
     *
     * @var string|null
     */
    public ?string $manufacturerName = null;

    /**
     * Country
     *
     * @var string|null
     */
    public ?string $country = null;

    /**
     * Availability type
     *
     * @var string|null
     */
    public ?string $availabilityType = null;

    /**
     * Color
     *
     * @var string|null
     */
    public ?string $color = null;

    /**
     * Car class
     *
     * @var string|null
     */
    public ?string $carClass = null;

    /**
     * Condition
     *
     * @var string|null
     */
    public ?string $condition = null;

    /**
     * Mileage
     *
     * @var string|null
     */
    public ?string $milage = null;

    /**
     * Length
     *
     * @var string|null
     */
    public ?string $length = null;

    /**
     * Width
     *
     * @var string|null
     */
    public ?string $width = null;

    /**
     * Wheelbase
     *
     * @var string|null
     */
    public ?string $wheelbase = null;

    /**
     * Capacity
     *
     * @var string|null
     */
    public ?string $capacity = null;

    /**
     * Cylinders
     *
     * @var int|null
     */
    public ?int $cylinders = null;

    /**
     * Engine location
     *
     * @var string|null
     */
    public ?string $engineLocation = null;

    /**
     * Wheel drive
     *
     * @var string|null
     */
    public ?string $wheelDrive = null;

    /**
     * CarInformationDto
     *
     * @param Car|null $object
     */
    public function __construct(?Car $object = null)
    {
        if ($object) {
            $this->fromObject($object);
        }
    }

    /**
     * From $car
     *
     * @param Car $object
     *
     * @return void
     */
    private function fromObject(Car $object): void
    {
        $this->price = PriceFormatter::replaceEUR($object->getOSPrice());
        $this->productionYear = $object->getProductionYear();
        $this->bodyStyle = $object->getBodyStyle()
                                  ?->getName();

        $engine = $object->getAttributes()
                         ?->getEngine();

        if ($engine instanceof Engine) {
            $this->power = $engine->getPower();
            $this->capacity = $engine->getCapacity();
            $this->cylinders = $engine->getCylinders();
            $this->engineLocation = $engine->getEngineLocation();
        }

        $this->manufacturerName = $object->getManufacturer()
                                         ?->getName();

        try {
            $this->country = Countries::getName($object->getCountry());
        } catch (\Exception) {
            $this->country = '';
        }

        $saleInformation = $object->getSaleInformation()
                                  ?->getSaleInformation();
        if ($saleInformation instanceof SaleInformation) {
            $this->availabilityType = $saleInformation->getAvailabilityType();
            $this->condition = $saleInformation->getCondition();
            $this->milage = $saleInformation->getMilage();
        }

        $color = current($object->getColor());
        if ($color) {
            $this->color = $color;
        }

        $this->carClass = $object->getCarClass();

        $dimensions = $object->getAttributes()
                             ?->getDimensions();
        if ($dimensions instanceof Dimensions) {
            $this->length = $dimensions->getLength();
            $this->width = $dimensions->getWidth();
            $this->wheelbase = $dimensions->getWheelbase();
        }

        $transmission = $object->getAttributes()
                               ?->getTransmission();
        if ($transmission instanceof Transmission) {
            $this->wheelDrive = $transmission->getWheelDrive();
        }
    }
}
