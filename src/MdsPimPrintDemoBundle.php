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

namespace Mds\PimPrint\DemoBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

/**
 * Class MdsPimPrintDemoBundle
 *
 * @package Mds\PimPrint\DemoBundle
 */
class MdsPimPrintDemoBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    protected function getComposerPackageName(): string
    {
        return 'mds-agenturgruppe/pimprint-demo-bundle';
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getDescription()
    {
        return 'mds PimPrint DemoBundle for Pimcore 6 Demo.';
    }
}
