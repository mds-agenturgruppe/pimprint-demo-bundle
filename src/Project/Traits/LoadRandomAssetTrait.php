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

namespace Mds\PimPrint\DemoBundle\Project\Traits;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Listing as AssetListing;

/**
 * Trait LoadRandomAssetTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\Traits
 */
trait LoadRandomAssetTrait
{
    /**
     * Loads a random jpg or png Asset in $path.
     * If $maxWidth is set only assets that are smaller are returned. In some demonstrations we wan't to have small
     * images and in Pimcore demo may be larger ones.
     *
     * @param string   $path
     * @param int|null $maxWidth Max width of the asset.
     * @param array    $mimeTypes
     *
     * @return Asset|null
     */
    protected function loadRandomAsset(
        string $path,
        int $maxWidth = null,
        array $mimeTypes = ['image/jpeg', 'image/png']
    ): ?Asset {
        $listing = new AssetListing();
        $listing->addConditionParam('path LIKE :path', ['path' => $path])
                ->addConditionParam("mimetype IN (:mimeTypes)", ['mimeTypes' => $mimeTypes])
                ->setLimit(1)
                ->setOrderKey('RAND()', false)
                ->load();

        $asset = $listing->current();
        if (false === $asset instanceof Asset) {
            return null;
        }
        if (null === $maxWidth) {
            return $asset;
        }
        $dimensions = $asset->getDimensions();
        if (false === $dimensions) {
            //we can't check the size. Try another image.
            return $this->loadRandomAsset($path, $maxWidth, $mimeTypes);
        }
        if ($dimensions['width'] > $maxWidth) {
            return $this->loadRandomAsset($path, $maxWidth, $mimeTypes);
        }

        return $asset;
    }
}
