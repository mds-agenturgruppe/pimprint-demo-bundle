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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper;

use App\Model\Product\Car as CarProduct;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Car\Listing as CarListing;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class CarProvider
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper
 */
class CarProvider
{
    /**
     * Loads all published `Car` objects of the given $type assigned
     * to $manufacturer that are visible to the current user.
     *
     * @param Manufacturer $manufacturer
     * @param string       $type
     *
     * @return Car[]
     */
    public function loadIdsByManufacturer(
        Manufacturer $manufacturer,
        string $type = CarProduct::OBJECT_TYPE_VIRTUAL_CAR
    ): array {
        $listing = new CarListing();
        $listing->setUnpublished(false)
                ->setOrderKey('name')
                ->setOrder('ASC');
        $listing->addConditionParam('objectType = :objectType', ['objectType' => $type]);

        $listing->filterByManufacturer($manufacturer);
        if (CarProduct::OBJECT_TYPE_ACTUAL_CAR == $type) {
            return $listing->loadIdList();
        }

        return $this->filterForRootCars($listing->load());
    }

    /**
     * Loads all published `Car` objects of the given $type assigned
     * to $category that are visible to the current user.
     *
     * @param Category $category
     * @param string   $type
     *
     * @return array
     */
    public function loadIdsByCategory(
        Category $category,
        string $type = CarProduct::OBJECT_TYPE_VIRTUAL_CAR
    ): array {
        $listing = new CarListing();
        $listing->setUnpublished(false)
                ->setOrderKey('name')
                ->setOrder('ASC');
        $listing->addConditionParam('objectType = :objectType', ['objectType' => $type]);

        $listing->filterByCategories($category);

        if (CarProduct::OBJECT_TYPE_ACTUAL_CAR == $type) {
            return $listing->loadIdList();
        }

        return $this->filterForRootCars($listing->load());
    }

    /**
     * In publications, only "root" virtual-cars visible to the current user are rendered.
     * Checking each car’s parent filters out BodyStyle "virtual-car" `Car` objects.
     *
     * @param array $cars
     *
     * @return array
     */
    private function filterForRootCars(array $cars): array
    {
        $return = [];
        foreach ($cars as $car) {
            if ($car->getParent() instanceof Car) {
                continue;
            }
            if (!$car->isAllowed('view')) {
                continue;
            }
            $return[] = $car->getId();
        }

        return $return;
    }
}
