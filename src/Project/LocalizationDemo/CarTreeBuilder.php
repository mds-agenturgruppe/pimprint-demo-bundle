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

namespace Mds\PimPrint\DemoBundle\Project\LocalizationDemo;

use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Folder;

/**
 * Class CarTreeBuilder
 *
 * @package Mds\PimPrint\DemoBundle\Project\LocalizationDemo
 */
class CarTreeBuilder extends PublicationTreeBuilder
{
    /**
     * Returns all publications in tree structure to display in InDesign-Plugin.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        $folder = Folder::getByPath('/Product Data/Cars');
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
            $object instanceof Car => $object->isAllowed('view'),
            default => false,
        };
    }
}
