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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel;

use App\Model\Product\Car;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarInformationDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\CellContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\ColumnDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\RowDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\TableDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ContentCreator
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel
 */
class ContentCreator extends AbstractHelper
{
    /**
     * Car information
     *
     * @var CarInformationDto
     */
    private CarInformationDto $carInformationDto;

    /**
     * Builds the `CarDetailContentDto` for the given car
     *
     * @param Car $car
     *
     * @return CarContentDto
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function createCarDetailContentDto(Car $car): CarContentDto
    {
        $this->carInformationDto = new CarInformationDto($car);

        $detailDto = new CarContentDto();
        $detailDto->price = $this->carInformationDto->price;
        $detailDto->productionDto = $this->getProductionTableDto();
        $detailDto->generalDto = $this->getGeneralTableDto();
        $detailDto->dimensionDto = $this->getDimensionTableDto();
        $detailDto->engineDto = $this->getEngineTableDto();

        return $detailDto;
    }

    /**
     * Creates the `TableDto` for the production details
     *
     * @return TableDto
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getProductionTableDto(): TableDto
    {
        $tableDto = new TableDto(
            CarSalesLabelTemplate::CONTENT_ORIGIN_LEFT,
            CarSalesLabelTemplate::PRODUCTION_DETAILS_Y_POSITION,
            CarSalesLabelTemplate::PRODUCTION_DETAILS_WIDTH,
            CarSalesLabelTemplate::PRODUCTION_DETAILS_HEIGHT,
            CarSalesLabelTemplate::STYLE_TABLE_INFORMATION
        );

        $tableDto->rowHeight = CarSalesLabelTemplate::PRODUCTION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto('', CarSalesLabelTemplate::PRODUCTION_DETAILS_FRIST_WIDTH)
        );
        $tableDto->addColumn(
            new ColumnDto('', CarSalesLabelTemplate::PRODUCTION_DETAILS_SECOND_WIDTH)
        );

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.manufacturer'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->manufacturerName,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.productionYear'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->productionYear,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.country'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->country,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translationHelper()->transTrimColon('general.available-in'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->translationHelper()
                     ->transAttributeValue($this->carInformationDto->availabilityType),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.condition'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->translationHelper()
                     ->transAttributeValue($this->carInformationDto->condition),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        return $tableDto;
    }

    /**
     * Creates the `TableDto` for the general details
     *
     * @return TableDto
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getGeneralTableDto(): TableDto
    {
        $tableDto = new TableDto(
            CarSalesLabelTemplate::GENERAL_DETAILS_X_POSITION,
            CarSalesLabelTemplate::PRODUCTION_DETAILS_Y_POSITION,
            CarSalesLabelTemplate::PRODUCTION_DETAILS_WIDTH,
            CarSalesLabelTemplate::GENERAL_DETAILS_HEIGHT,
            CarSalesLabelTemplate::STYLE_TABLE_INFORMATION
        );

        $tableDto->rowHeight = CarSalesLabelTemplate::PRODUCTION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto('', CarSalesLabelTemplate::DIMENSION_DETAILS_FIRST_WIDTH)
        );
        $tableDto->addColumn(
            new ColumnDto('', CarSalesLabelTemplate::PRODUCTION_DETAILS_SECOND_WIDTH)
        );

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.color'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->translationHelper()
                     ->transAttributeValue($this->carInformationDto->color),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.body-style'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->bodyStyle,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.car-class'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->translationHelper()
                     ->transAttributeValue(strtolower($this->carInformationDto->carClass)),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.milage'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_LEFT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->milage,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_BIG_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        return $tableDto;
    }

    /**
     * Creates the `TableDto` for the dimension details
     *
     * @return TableDto
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getDimensionTableDto(): TableDto
    {
        $tableDto = new TableDto(
            CarSalesLabelTemplate::TECHNICAL_DETAILS_X_POSITION,
            CarSalesLabelTemplate::PRODUCTION_DETAILS_Y_POSITION,
            CarSalesLabelTemplate::DIMENSION_DETAILS_WIDTH,
            CarSalesLabelTemplate::DIMENSION_DETAILS_HEIGHT,
            CarSalesLabelTemplate::STYLE_TABLE_TECHNICAL
        );

        $tableDto->rowHeight = CarSalesLabelTemplate::DIMENSION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto(
                'general.dimensions',
                CarSalesLabelTemplate::DIMENSION_DETAILS_FIRST_WIDTH,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_TABLE_HEADER,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT,
                2,
                CarSalesLabelTemplate::DIMENSION_DETAILS_HEADER_HEIGHT
            )
        );
        $tableDto->addColumn(
            new ColumnDto('', CarSalesLabelTemplate::DIMENSION_DETAILS_SECOND_WIDTH)
        );

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.length'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->length,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.width'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->width,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.wheelbase'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->wheelbase,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        return $tableDto;
    }

    /**
     * Creates the `TableDto` for the engine details
     *
     * @return TableDto
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getEngineTableDto(): TableDto
    {
        $tableDto = new TableDto(
            CarSalesLabelTemplate::TECHNICAL_DETAILS_X_POSITION,
            CarSalesLabelTemplate::ENGINE_DETAILS_Y_POSITION,
            CarSalesLabelTemplate::DIMENSION_DETAILS_WIDTH,
            CarSalesLabelTemplate::ENGINE_DETAILS_HEIGHT,
            CarSalesLabelTemplate::STYLE_TABLE_TECHNICAL
        );

        $tableDto->rowHeight = CarSalesLabelTemplate::DIMENSION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto(
                'general.engine',
                CarSalesLabelTemplate::DIMENSION_DETAILS_FIRST_WIDTH,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_TABLE_HEADER,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT,
                2,
                CarSalesLabelTemplate::DIMENSION_DETAILS_HEADER_HEIGHT
            )
        );
        $tableDto->addColumn(
            new ColumnDto('', CarSalesLabelTemplate::DIMENSION_DETAILS_SECOND_WIDTH)
        );

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.power'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->power,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.capacity'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->capacity,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.engineLocation'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->carInformationDto->engineLocation,
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        $row = [
            new CellContentDto(
                $this->translator()
                     ->trans('general.wheelDrive'),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT
            ),
            new CellContentDto(
                $this->translationHelper()
                     ->transAttributeValue($this->carInformationDto->wheelDrive),
                CarSalesLabelTemplate::STYLE_TABLE_CELL_SMALL_ROWS_RIGHT,
                CarSalesLabelTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT
            )
        ];
        $tableDto->addRow(RowDto::fromArray($row));

        return $tableDto;
    }
}
