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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper;

use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class AccessoryPartProvider
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper
 */
class AccessoryPartProvider
{
    /**
     * Loads AccessoryPart Ids by $manufacturer
     *
     * @param Manufacturer $manufacturer
     *
     * @return array
     */
    public function loadIdsByManufacturer(Manufacturer $manufacturer): array
    {
        $listing = new AccessoryPart\Listing();
        $listing->filterByManufacturer($manufacturer);

        return $listing->loadIdList();
    }

    /**
     * Loads AccessoryPart Ids by $category
     *
     * @param Category $category
     *
     * @return array
     */
    public function loadIdsByCategory(Category $category): array
    {
        $listing = new AccessoryPart\Listing();
        $listing->filterByMainCategory($category);

        return $listing->loadIdList();
    }
}
