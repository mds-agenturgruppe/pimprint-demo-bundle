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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper;

use Pimcore\Bundle\EcommerceFrameworkBundle\PriceSystem\PriceInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\PriceSystem\TaxManagement\TaxEntry;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class PriceFormatter
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper
 */
class PriceFormatter extends AbstractHelper
{
    /**
     * Replaces 'EUR' with the euro symbol
     *
     * @param string $amount
     *
     * @return string
     */
    public static function replaceEUR(string $amount): string
    {
        return str_replace('EUR', '€', $amount);
    }

    /**
     * Formats $price and $taxEntry.
     *
     * Mimics the Twig output:
     * `{{ taxEntry.entry.name }}: {{ taxEntry.percent }}%
     * ({{ taxEntry.amount.asNumeric | currency(price.currency.shortName) }})`
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function formatTaxEntry(PriceInterface $price, TaxEntry $taxEntry): string
    {
        $percent = $taxEntry->getPercent();
        $currency = $price->getCurrency()
                          ->getShortName();
        $amount = $this->intlFormatter()
                       ->formatCurrency(
                           $taxEntry->getAmount()
                                    ->asNumeric(),
                           $currency
                       );
        $formatted = self::replaceEUR($amount);

        return "$percent% ($formatted)";
    }
}
