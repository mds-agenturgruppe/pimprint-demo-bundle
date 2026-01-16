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
 * Class RowDto
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table
 */
class RowDto
{
    /**
     * Optional row height
     *
     * @var float|null
     */
    public ?float $rowHeight = null;

    /**
     * Cells of row
     *
     * @var CellContentDto[]
     */
    public array $cells = [];

    /**
     * Adds a cell to the row
     *
     * @param CellContentDto $cellDto
     *
     * @return void
     */
    public function addCell(CellContentDto $cellDto): void
    {
        $this->cells[] = $cellDto;
    }

    /**
     * Creates a new RowDto instance from an array of row data
     *
     * @param CellContentDto[] $cells
     *
     * @return RowDto
     */
    public static function fromArray(array $cells): RowDto
    {
        $return = new self();
        foreach ($cells as $cell) {
            $return->addCell($cell);
        }

        return $return;
    }
}
