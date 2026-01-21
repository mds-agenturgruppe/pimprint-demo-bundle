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
