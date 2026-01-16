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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Template;

use Mds\PimPrint\CoreBundle\InDesign\Command\Template;

/**
 * Interface PageLayoutInterface
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Template
 */
interface PageLayoutInterface
{
    /**
     * Returns the InDesign commands to build the page layout.
     *
     * @param string|null $chapterName
     *
     * @return Template
     */
    public function getPageLayoutCommands(?string $chapterName): Template;
}
