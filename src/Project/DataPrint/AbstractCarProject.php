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

use App\Model\Product\Car as CarProduct;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\CarDataInterpreterTrait;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Car\Listing as CarListing;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class AbstractCarProject
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint
 */
abstract class AbstractCarProject extends AbstractProject
{
    use CarDataInterpreterTrait;

    /**
     * Returns all publications in tree structure to display in InDesign-Plugin.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return $this->publicationLoader->buildCarPublicationTree(
            Category::getByPath('/Product Data/Categories/products/cars')
        );
    }

    /**
     * Only categories with cars assigned are rendered in the publication.
     *
     * @param Category $category
     *
     * @return bool
     */
    protected function hasCategoryRenderableElements(Category $category): bool
    {
        return !empty($this->loadCarsForCategory($category, CarProduct::OBJECT_TYPE_ACTUAL_CAR));
    }

    /**
     * Only manufacturers with cars assigned are rendered in the publication.
     *
     * @param Manufacturer $manufacturer
     *
     * @return bool
     */
    protected function hasManufacturerRenderableElements(Manufacturer $manufacturer): bool
    {
        return !empty($this->loadCarsForManufacturer($manufacturer, CarProduct::OBJECT_TYPE_ACTUAL_CAR));
    }

    /**
     * Loads all published Car objects for $type object-type assigned to $category visible for current user.
     *
     * @param Category $category
     * @param string   $type
     *
     * @return array
     */
    protected function loadCarsForCategory(Category $category, string $type): array
    {
        $listing = $this->createCarListing($type);
        $listing->addConditionParam(
            'categories LIKE :categories',
            ['categories' => '%,' . $category->getId() . ',%']
        );
        if (CarProduct::OBJECT_TYPE_ACTUAL_CAR == $type) {
            return $listing->load();
        }

        return $this->filterForRootCars($listing->load());
    }

    /**
     * Loads all published Car objects for $type object-type assigned to $manufacturer visible for current user.
     *
     * @param Manufacturer $manufacturer
     * @param string       $type
     *
     * @return Car[]
     */
    protected function loadCarsForManufacturer(Manufacturer $manufacturer, string $type): array
    {
        $listing = $this->createCarListing($type);
        $listing->addConditionParam(
            'manufacturer__id = :manufacturer',
            ['manufacturer' => $manufacturer->getId()]
        );
        if (CarProduct::OBJECT_TYPE_ACTUAL_CAR == $type) {
            return $listing->load();
        }

        return $this->filterForRootCars($listing->load());
    }

    /**
     * Returns listing for published Car objects with optional $objectType.
     *
     * @param string|null $objectType
     *
     * @return CarListing
     */
    protected function createCarListing(string $objectType = null): CarListing
    {
        $listing = new CarListing();
        $listing->setUnpublished(false)
                ->setOrderKey('name')
                ->setOrder('ASC');
        if (null !== $objectType) {
            $listing->addConditionParam('objectType = :objectType', ['objectType' => $objectType]);
        }

        return $listing;
    }

    /**
     * In publications only 'root' virtual-cars that are visible for the current user are rendered.
     * By checking the parent of each car, BodyStyle 'virtual-car' Car objects are filtered out.
     *
     * @param array $cars
     *
     * @return array
     */
    protected function filterForRootCars(array $cars): array
    {
        $return = [];
        foreach ($cars as $car) {
            if ($car->getParent() instanceof Car) {
                continue;
            }
            if (false === $car->isAllowed('view')) {
                continue;
            }
            $return[] = $car;
        }

        return $return;
    }

    /**
     * Loads all CarProduct::OBJECT_TYPE_ACTUAL_CAR variants for $car visible for current user.
     *
     * @param Car $car
     *
     * @return array
     */
    protected function loadVariantsForCar(Car $car): array
    {
        $listing = $this->createCarListing(CarProduct::OBJECT_TYPE_ACTUAL_CAR);
        $listing->addConditionParam('path LIKE :path', ['path' => $car->getFullPath() . '/%'])
                ->setOrder('ASC')
                ->setOrderKey('name');

        $return = [];
        foreach ($listing->load() as $car) {
            if (false === $car->isAllowed('view')) {
                continue;
            }
            $return[] = $car;
        }

        return $return;
    }
}
