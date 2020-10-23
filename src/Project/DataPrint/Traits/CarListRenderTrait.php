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

namespace Mds\PimPrint\DemoBundle\Project\DataPrint\Traits;

use AppBundle\Model\Product\Car as CarProduct;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\BodyStyle;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Manufacturer;
use Pimcore\Model\DataObject\Objectbrick\Data\Bodywork;

/**
 * Trait CarListRenderTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait CarListRenderTrait
{
    use ListRenderTrait;
    use CarDataInterpreterTrait;

    /**
     * Adds $car variant as row to the table.
     *
     * @param Car $car
     *
     * @throws \Exception
     */
    private function addTableRowCarVariant(Car $car)
    {
        $car = CarProduct::getById($car->getId());
        $this->getTable()
             ->startRow();

        foreach ($this->buildCellContentForCar($car) as $ident => $content) {
            if (false === $this->getTable()
                               ->hasColumn($ident)) {
                continue;
            }
            $this->getTable()
                 ->addCell($content, $ident);
        }
    }

    /**
     * Builds cell contents for $car.
     *
     * @param Car $car
     *
     * @return array
     * @throws \Exception
     */
    protected function buildCellContentForCar(Car $car)
    {
        $return = [];
        $image = $this->buildCarListTableImage($car);
        if ($image instanceof Text) {
            $return['image'] = $image;
        }
        $manufacturer = $car->getManufacturer();
        if ($manufacturer instanceof Manufacturer) {
            $return['manufacturer'] = $manufacturer->getName();
        }
        $carClass = $car->getCarClass();
        if (false === empty($carClass)) {
            $return['carClass'] = $this->translator->trans(strtolower("attribute.$carClass"));
        }
        $bodyStyle = $car->getBodyStyle();
        if ($bodyStyle instanceof BodyStyle) {
            $return['bodyStyle'] = $bodyStyle->getName();
        }
        $attributes = $car->getAttributes();
        if ($attributes instanceof Car\Attributes) {
            $bodyWork = $attributes->getBodywork();
            if ($bodyWork instanceof Bodywork) {
                $return['numberOfDoors'] = (string)$bodyWork->getNumberOfDoors();
            }
        }
        $return['name'] = (string)$car->getName();
        $return['year'] = (string)$car->getProductionYear();
        $return['mileage'] = $this->getMileageFormatted($car);
        $return['price'] = $this->getPriceEurFormatted($car);

        return $return;
    }

    /**
     * Builds an Text element with $car image for display in lists.
     *
     * @param Car $car
     *
     * @return Text|null
     * @throws \Exception
     */
    protected function buildCarListTableImage(Car $car)
    {
        $asset = $car->getMainImage();
        if ($asset instanceof Hotspotimage) {
            $asset = $asset->getImage();
        }
        if (false === $asset instanceof Asset) {
            return null;
        }

        return $this->buildTableImageElement($asset);
    }
}
