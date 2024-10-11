<?php
/**
 * mds Agenturgruppe GmbH
 *
 * This source file is licensed under GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 */

namespace Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo;

use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class ManufacturerTreeBuilder
 *
 * @package Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo
 */
class ManufacturerTreeBuilder extends PublicationTreeBuilder
{
    /**
     * Returns all publications in tree structure to display in InDesign-Plugin.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        $folder = Folder::getByPath('/Product Data/Manufacturer');
        if (!$folder) {
            return [];
        }

        $return = [];
        foreach ($folder->getChildren() as $child) {
            $return[] = $this->buildObjectTree($child);
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
