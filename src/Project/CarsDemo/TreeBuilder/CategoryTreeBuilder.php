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

use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Folder;

/**
 * Class CategoryTreeBuilder
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder
 */
class CategoryTreeBuilder extends PublicationTreeBuilder
{
    /**
     * Returns the category DataObject tree structure under $path.
     * The InDesign-Plugin displays categories as publications to render.
     *
     * @param string $path
     *
     * @return array
     */
    public function getPublicationsTree(string $path): array
    {
        $folder = Category::getByPath($path);
        if (!$folder) {
            return [];
        }

        $return = ['identifier' => 'noCategory', 'label' => 'Category'];
        foreach ($folder->getChildren() as $child) {
            $return['children'][] = $this->buildObjectTree($child);
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
            $object instanceof Category => $object->isAllowed('view'),
            default => false,
        };
    }
}
