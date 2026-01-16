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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\CustomField;

use Mds\PimPrint\CoreBundle\InDesign\CustomField\Select;
use Pimcore\Localization\LocaleService;
use Pimcore\Tool;

/**
 * Class SecondLanguageSelect
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\CustomField
 */
class SecondLanguageSelect extends Select
{
    /**
     * SecondLanguageSelect
     *
     * @param LocaleService $localeService
     *
     * @throws \Exception
     */
    public function __construct(LocaleService $localeService)
    {
        $this->setParam('secondLanguage') //Parameter name sent back to server
             ->setLabel('Second Language'); //Label shown in InDesign

        foreach (Tool::getValidLanguages() as $code) {
            $label = \Locale::getDisplayLanguage($code, $localeService->getLocale());
            $displayRegion = \Locale::getDisplayRegion($code, $localeService->getLocale());

            if ($displayRegion) {
                $label .= ' (' . $displayRegion . ')';
            }

            if ($label) {
                $label .= ' (' . $code . ')';
            } else {
                $label = $code;
            }

            $this->addValueRaw($code, $label);
        }
    }
}
