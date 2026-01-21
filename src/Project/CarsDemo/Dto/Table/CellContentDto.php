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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table;

/**
 * Class CellContentDto
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table
 */
class CellContentDto
{
    /**
     * Content of cell
     *
     * @var string|null
     */
    public ?string $content = null;

    /**
     * Optional cell style
     *
     * @var string|null
     */
    public ?string $cellStyle = null;

    /**
     * Optional paragraph style of cell content
     *
     * @var string|null
     */
    public ?string $paragraphStyle = null;

    /**
     * Optional character style of cell content
     *
     * @var string|null
     */
    public ?string $characterStyle = null;

    /**
     * CellContentDto constructor.
     *
     * @param string|null $content
     * @param string|null $cellStyle
     * @param string|null $paragraphStyle
     * @param string|null $characterStyle
     */
    public function __construct(
        string $content = null,
        string $cellStyle = null,
        string $paragraphStyle = null,
        string $characterStyle = null
    ) {
        $this->content = $content;
        $this->cellStyle = $cellStyle;
        $this->paragraphStyle = $paragraphStyle;
        $this->characterStyle = $characterStyle;
    }
}
