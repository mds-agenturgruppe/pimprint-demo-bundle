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

use App\Model\Product\Car as CarProduct;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\BodyStyle;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Data\ImageGallery;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Trait CarDataInterpreterTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait CarDataInterpreterTrait
{
    use SalesInformationTrait;

    /**
     * Loads main image of $car.
     * If no image is available and currentType is 'category' the manufacturer logo is used.
     *
     * @param CarProduct $car
     *
     * @return Asset|null
     */
    protected function getMainImageForCar(CarProduct $car): ?Asset
    {
        $asset = $car->getMainImage();
        if ($asset instanceof Hotspotimage) {
            return $asset->getImage();
        }
        if (self::TYPE_CATEGORY == $this->currentType) {
            $manufacturer = $car->getManufacturer();
            if ($manufacturer instanceof Manufacturer && $manufacturer->getPublished()) {
                return $manufacturer->getLogo();
            }
        }
        $assets = $car->getGenericImages();
        if ($assets instanceof ImageGallery) {
            $asset = current($assets->getItems());
            if ($asset instanceof Hotspotimage) {
                $asset = $asset->getImage();
            }
            if ($asset instanceof Asset) {
                return $asset;
            }
        }

        return null;
    }

    /**
     * Builds headline displayed in brochure for a variant.
     *
     * @param Car $car
     *
     * @return string
     */
    protected function getVariantHeadline(Car $car): string
    {
        $parts = [];
        if (self::TYPE_CATEGORY == $this->currentType) {
            $manufacturer = $car->getManufacturer();
            if ($manufacturer instanceof Manufacturer) {
                $parts[] = $manufacturer->getName();
            }
        }
        $parts[] = $car->getName();
        $color = current($car->getColor());
        if (false === empty($color)) {
            $parts[] = '-';
            $parts[] = $this->translator->trans("attribute.$color");
        }

        return implode(' ', array_filter($parts));
    }

    /**
     * Builds description text of a variant for brochure.
     *
     * @param Car $car
     *
     * @return string
     */
    protected function getVariantDescription(Car $car): string
    {
        $parts = [];
        $bodyStyle = $car->getBodyStyle();
        if ($bodyStyle instanceof BodyStyle) {
            $parts[] = $bodyStyle->getName();
        }

        $salesInformation = $car->getSaleInformation();
        if (false === empty($salesInformation)) {
            $salesInformation = $salesInformation->getSaleInformation();
            if (false === empty($salesInformation)) {
                $parts[] = $salesInformation->getMilage();
            }
        }

        return implode(', ', $parts);
    }

    /**
     * Builds sales information of a car variant for brochure format in HTML.
     *
     * @param Car $car
     *
     * @return string
     */
    protected function getVariantSalesInformation(Car $car): string
    {
        try {
            $salesInformation = $this->getSalesInformation($car);
        } catch (\Exception $exception) {
            return '';
        }
        $contentDefinition = [
            'general.condition'    => [
                'method'               => 'getCondition',
                'translatePrefixValue' => 'attribute.',
            ],
            'general.available-in' => [
                'method'               => 'getAvailabilityType',
                'translatePrefixValue' => 'attribute.',
            ],
        ];
        $parts = [];
        foreach ($contentDefinition as $translationKey => $definition) {
            $method = $definition['method'];
            $value = $salesInformation->$method();
            $value = (string)$value;
            if (null !== $definition['translatePrefixValue']) {
                $value = $this->translator->trans($definition['translatePrefixValue'] . $value);
            }
            $parts[] = '<div class="copyHeadline">' . $this->translator->trans($translationKey) . '</div>';
            $parts[] = '<div class="copyText">' . $value . '<br></div>';
        }
        $price = $this->getPriceEurFormatted($car);
        if (false === empty($price)) {
            $parts[] = '<div class="price">' . $price . '<br></div>';
        }

        return implode('', $parts);
    }
}
