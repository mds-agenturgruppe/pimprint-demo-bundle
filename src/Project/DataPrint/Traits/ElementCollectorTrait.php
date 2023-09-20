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

use Mds\PimPrint\CoreBundle\Project\Traits\ElementCollectionRenderingTrait;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Manufacturer;
use Pimcore\Model\DataObject\Manufacturer\Listing as ManufacturerListing;
use Pimcore\Model\Element\AbstractElement;

/**
 * Trait ElementCollectorTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait ElementCollectorTrait
{
    use ElementCollectionRenderingTrait;

    /**
     * Returns true if $category contains renderable elements in concrete project.
     *
     * @param Category $category
     *
     * @return bool
     */
    abstract protected function hasCategoryRenderableElements(Category $category): bool;

    /**
     * Returns true if $manufacturer contains renderable elements in concrete project.
     *
     * @param Manufacturer $manufacturer
     *
     * @return bool
     */
    abstract protected function hasManufacturerRenderableElements(Manufacturer $manufacturer): bool;

    /**
     * Collects all elements to generate. In CarBrochure and CarList demo different publications can be rendered.
     * - One Category
     * - Multiple Categories when a Category has child categories.
     * - One Manufacturer
     * - All manufacturers when the folder Manufacturers is selected.
     *
     * @param AbstractElement|AbstractObject $object
     *
     * @return void
     */
    protected function collectElements(AbstractElement|AbstractObject $object): void
    {
        if ($object instanceof Category) {
            if (true === $this->hasCategoryRenderableElements($object)) {
                $this->elements[] = $object;
            }
            $categories = $object->getChildren([AbstractObject::OBJECT_TYPE_OBJECT]);
            foreach ($categories as $category) {
                if (false === $category instanceof Category
                    || false === $category->getPublished()
                    || false === $category->isAllowed('view')) {
                    continue;
                }
                $this->collectElements($category);
            }
        } elseif ($object instanceof Manufacturer) {
            if (true === $this->hasManufacturerRenderableElements($object)) {
                $this->elements[] = $object;
            }
        } elseif ($object instanceof Folder) {
            $this->elements = $this->loadManufacturersFromFolder($object);
        }
    }

    /**
     * Loads all manufacturers under $folder path with assigned cars.
     *
     * @param Folder $folder
     *
     * @return array
     */
    private function loadManufacturersFromFolder(Folder $folder): array
    {
        $listing = new ManufacturerListing();
        $listing->setUnpublished(false)
                ->addConditionParam('path LIKE :path', ['path' => $folder->getFullPath() . '/%'])
                ->setOrder('ASC')
                ->setOrderKey('name');
        $return = [];
        foreach ($listing->load() as $manufacturer) {
            if (false === $manufacturer->getPublished() || false === $manufacturer->isAllowed('view')) {
                continue;
            }
            if (true === $this->hasManufacturerRenderableElements($manufacturer)) {
                $return[] = $manufacturer;
            }
        }

        return $return;
    }

    /**
     * Renders $object
     *
     * @param AbstractElement $object
     *
     * @return void
     */
    protected function renderElement(AbstractElement $object): void
    {
        switch (true) {
            case $object instanceof Category:
                $this->currentType = self::TYPE_CATEGORY;
                $this->renderCategory($object);
                break;

            case $object instanceof Manufacturer:
                $this->currentType = self::TYPE_MANUFACTURER;
                $this->renderManufacturer($object);
                break;
        }
    }

    /**
     * Abstract method to render a $category.
     *
     * @param Category $category
     */
    abstract protected function renderCategory(Category $category);

    /**
     * Abstract method to render a $manufacturer.
     *
     * @param Manufacturer $manufacturer
     */
    abstract protected function renderManufacturer(Manufacturer $manufacturer);
}
