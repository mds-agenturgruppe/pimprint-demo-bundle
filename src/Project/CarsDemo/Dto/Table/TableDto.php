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
 * Class TableDto
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table
 */
class TableDto
{
    /**
     * Table x-postion
     *
     * @var float
     */
    public float $xPosition;

    /**
     * Table y-position
     *
     * @var float
     */
    public float $yPosition;

    /**
     * Table width
     *
     * @var float
     */
    public float $width;

    /**
     * Table height
     *
     * @var float
     */
    public float $height;

    /**
     * Row height
     *
     * @var float|null
     */
    public ?float $rowHeight = null;

    /**
     * Table column definitions
     *
     * @var ColumnDto[]
     */
    public array $columns = [];

    /**
     * Table content rows
     *
     * @var RowDto[]
     */
    public array $rows = [];

    /**
     * Table style
     *
     * @var string
     */
    public string $tableStyle;

    /**
     * CarDetailTableDto
     *
     * @param float  $xPosition
     * @param float  $yPosition
     * @param float  $width
     * @param float  $height
     * @param string $tableStyle
     */
    public function __construct(float $xPosition, float $yPosition, float $width, float $height, string $tableStyle)
    {
        $this->xPosition = $xPosition;
        $this->yPosition = $yPosition;
        $this->width = $width;
        $this->height = $height;
        $this->tableStyle = $tableStyle;
    }

    /**
     * Adds column
     *
     * @param ColumnDto $columnDto
     *
     * @return void
     */
    public function addColumn(ColumnDto $columnDto): void
    {
        $this->columns[] = $columnDto;
    }

    /**
     * Adds row
     *
     * @param RowDto $row
     *
     * @return void
     */
    public function addRow(RowDto $row): void
    {
        $this->rows[] = $row;
    }
}
