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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table;

/**
 * Class TableColumnDto
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table
 */
class ColumnDto
{
    /**
     * Translation key used for the column headline
     *
     * @var string|null
     */
    public ?string $translationKey;

    /**
     * Column width
     *
     * @var float
     */
    public float $width;

    /**
     * InDesign cell style
     *
     * @var string
     */
    public string $cellStyle;

    /**
     * InDesign head style
     *
     * @var string
     */
    public string $headStyle;

    /**
     * Optional colspan
     *
     * @var int|null
     */
    public ?int $colspan;

    /**
     * Optional head row height
     *
     * @var float|null
     */
    public ?float $headRowHeight = null;

    /**
     * Column constructor.
     *
     * @param string|null $translationKey
     * @param int|float   $width
     * @param string      $cellStyle
     * @param string      $headStyle
     * @param int|null    $colspan
     * @param float|null  $headRowHeight
     */
    public function __construct(
        ?string $translationKey,
        int|float $width = 10,
        string $cellStyle = '',
        string $headStyle = '',
        ?int $colspan = null,
        ?float $headRowHeight = null
    ) {
        $this->translationKey = $translationKey;
        $this->width = (float)$width;
        $this->cellStyle = $cellStyle;
        $this->headStyle = $headStyle;
        $this->colspan = $colspan;
        $this->headRowHeight = $headRowHeight;
    }
}
