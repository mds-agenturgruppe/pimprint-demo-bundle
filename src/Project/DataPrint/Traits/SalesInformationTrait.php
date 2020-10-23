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

use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Objectbrick\Data\SaleInformation;

/**
 * Trait SalesInformationTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait SalesInformationTrait
{
    /**
     * Returns salesInformation for $object. If none is found an exception is thrown.
     *
     * @param AbstractObject $object
     *
     * @return SaleInformation
     * @throws \Exception
     */
    protected function getSalesInformation(AbstractObject $object)
    {
        if (false === method_exists($object, 'getSaleInformation')) {
            throw new \Exception();
        }
        $salesInformation = $object->getSaleInformation();
        if (empty($salesInformation)) {
            throw new \Exception();
        }
        $salesInformation = $salesInformation->getSaleInformation();
        if (empty($salesInformation)) {
            throw new \Exception();
        }

        return $salesInformation;
    }

    /**
     * Returns EUR price formatted. If no price is found an empty string
     *
     * @param AbstractObject $object
     *
     * @return string
     */
    protected function getPriceEurFormatted(AbstractObject $object)
    {
        try {
            $salesInformation = $this->getSalesInformation($object);
        } catch (\Exception $e) {
            return '';
        }
        $price = $salesInformation->getPriceInEUR();
        if (empty($price)) {
            return '';
        }

        return $this->intlFormatter->formatCurrency($price, 'EUR');
    }

    /**
     * Returns mileage formatted with value and unit. If no mileage is found an empty string is returned
     *
     * @param AbstractObject $object
     *
     * @return string
     */
    protected function getMileageFormatted(AbstractObject $object)
    {
        try {
            $salesInformation = $this->getSalesInformation($object);
        } catch (\Exception $e) {
            return '';
        }

        $mileage = $salesInformation->getMilage();
        if (false === $mileage instanceof QuantityValue) {
            return '';
        }
        $parts = [];
        $parts[] = $this->intlFormatter->formatNumber($mileage->getValue());
        $parts[] = $mileage->getUnit()
                           ->getAbbreviation();

        return implode(' ', $parts);
    }
}
