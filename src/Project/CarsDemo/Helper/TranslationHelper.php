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

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class TranslationHelper
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper
 */
class TranslationHelper extends AbstractHelper
{
    /**
     * Translates an attribute depending on the $value
     *
     * @param string|null $value
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function transAttributeValue(?string $value): string
    {
        if (!$value) {
            return '';
        }

        return $this->translator()
                    ->trans('attribute.' . $value);
    }

    /**
     * Translates $key and removes the tailing colon.
     *
     * Manual Pimcore demo content cleanup
     * Some translations contain ":" in their value. For column headlines, we remove it.
     *
     * @param string      $key
     * @param string|null $locale
     *
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function transTrimColon(string $key, string $locale = null): string
    {
        $text = $this->translator()
                     ->trans($key, locale: $locale);

        return rtrim($text, ':');
    }
}
