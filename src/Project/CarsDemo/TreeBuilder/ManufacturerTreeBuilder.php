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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder;

use App\Model\Product\Car;
use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class ManufacturerTreeBuilder
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder
 */
class ManufacturerTreeBuilder extends PublicationTreeBuilder
{
    /**
     * Returns the manufacturer DataObject tree structure with all manufacturers.
     * The InDesign-Plugin displays manufacturers as publications to render.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        $folder = Folder::getByPath('/Product Data/Manufacturer');
        if (!$folder) {
            return [];
        }

        $return = ['identifier' => 'noManufacturer', 'label' => 'Manufacturer'];
        foreach ($folder->getChildren() as $child) {
            $return['children'][] = $this->buildObjectTree($child);
        }

        return $return;
    }

    /**
     * Returns the structure for display in the InDesign plugin, containing:
     * - manufacturer DataObjects
     * - car DataObjects related to the manufacturer
     *
     * @return array
     */
    public function getPublicationsTreeWithCars(): array
    {
        $folder = Folder::getByPath('/Product Data/Manufacturer');

        if (!$folder) {
            return [];
        }

        $return = [];
        foreach ($folder->getChildren() as $child) {
            if (!$child instanceof Manufacturer) {
                continue;
            }

            $tree = $this->buildTreeElementFromObject($child);
            $requiredBy = $child->getDependencies()
                                ->getRequiredBy();

            foreach ($requiredBy as $item) {
                if ($item['type'] !== 'object') {
                    continue;
                }
                $object = Concrete::getById($item['id']);
                if (!$object instanceof Car) {
                    continue;
                }
                if (!$object->getPublished()) {
                    continue;
                }
                $tree['children'][] = $this->buildObjectTree($object);
            }
            $return[] = $tree;
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     *
     * @param AbstractObject $object
     *
     * @return bool
     */
    protected function showObjectInTree(AbstractObject $object): bool
    {
        return match (true) {
            $object instanceof Folder => true,
            $object instanceof Manufacturer => $object->isAllowed('view'),
            default => false,
        };
    }
}
