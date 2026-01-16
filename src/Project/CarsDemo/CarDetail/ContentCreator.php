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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarDetail;

use App\Model\Product\Car;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarInformationDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\CellContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\ColumnDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\RowDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\TableDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ContentCreator
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\ElementRenderer
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
     * @throws \Exception
     */
    public function createCarDetailContentDto(Car $car): CarContentDto
    {
        $this->carInformationDto = new CarInformationDto($car);

        $detailDto = new CarContentDto();
        $detailDto->description = $car->getDescription();
        $detailDto->price = $this->carInformationDto->price;
        $detailDto->productionDto = $this->getProductionTableDto();
        $detailDto->generalDto = $this->getGeneralTableDto();
        $detailDto->conditionDto = $this->getConditionTableDto();
        $detailDto->dimensionDto = $this->getDimensionTableDto();
        $detailDto->engineDto = $this->getEngineTableDto();
        $detailDto->accessories = $this->collectAccessories($car);

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
            CarDetailTemplate::PRODUCTION_DETAILS_X_POSITION,
            CarDetailTemplate::PRODUCTION_DETAILS_Y_POSITION,
            CarDetailTemplate::PRODUCTION_DETAILS_WIDTH,
            CarDetailTemplate::PRODUCTION_DETAILS_HEIGHT,
            AbstractCarsDemoTemplate::STYLE_TABLE
        );

        $tableDto->rowHeight = CarDetailTemplate::PRODUCTION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::PRODUCTION_DETAILS_LEFT_COLUMN_WIDTH)
        );
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::PRODUCTION_DETAILS_RIGHT_COLUMN_WIDTH)
        );

        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.manufacturer');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->manufacturerName;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.productionYear');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->productionYear;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.country');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->country;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translationHelper()
                              ->transTrimColon('general.available-in');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->translationHelper()
                              ->transAttributeValue($this->carInformationDto->availabilityType);
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);

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
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
            CarDetailTemplate::GENERAL_DETAILS_Y_POSITION,
            CarDetailTemplate::GENERAL_DETAILS_WIDTH,
            CarDetailTemplate::GENERAL_DETAILS_HEIGHT,
            AbstractCarsDemoTemplate::STYLE_TABLE
        );

        $tableDto->rowHeight = CarDetailTemplate::GENERAL_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::DIMENSION_DETAILS_LEFT_COLUMN_WIDTH)
        );
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::GENERAL_DETAILS_RIGHT_COLUMN_WIDTH)
        );

        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.color');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->translationHelper()
                              ->transAttributeValue($this->carInformationDto->color);
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.body-style');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->bodyStyle;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.car-class');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->translationHelper()
                              ->transAttributeValue(strtolower($this->carInformationDto->carClass));
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);

        return $tableDto;
    }

    /**
     * Creates the `TableDto` for the condition details
     *
     * @return TableDto
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getConditionTableDto(): TableDto
    {
        $tableDto = new TableDto(
            CarDetailTemplate::CONDITION_DETAILS_X_POSITION,
            CarDetailTemplate::GENERAL_DETAILS_Y_POSITION,
            CarDetailTemplate::GENERAL_DETAILS_WIDTH,
            CarDetailTemplate::GENERAL_DETAILS_HEIGHT,
            AbstractCarsDemoTemplate::STYLE_TABLE
        );

        $tableDto->rowHeight = CarDetailTemplate::GENERAL_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::GENERAL_DETAILS_LEFT_COLUMN_WIDTH)
        );
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::CONDITION_DETAILS_RIGHT_COLUMN_WIDTH)
        );

        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.condition');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->translationHelper()
                              ->transAttributeValue($this->carInformationDto->condition);
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.milage');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->milage;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_MEDIUM_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);

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
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
            CarDetailTemplate::DIMENSION_DETAILS_Y_POSITION,
            CarDetailTemplate::DIMENSION_DETAILS_WIDTH,
            CarDetailTemplate::DIMENSION_DETAILS_HEIGHT,
            AbstractCarsDemoTemplate::STYLE_TABLE
        );

        $tableDto->rowHeight = CarDetailTemplate::DIMENSION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto(
                'general.dimensions',
                CarDetailTemplate::DIMENSION_DETAILS_LEFT_COLUMN_WIDTH,
                AbstractCarsDemoTemplate::STYLE_TABLE_CELL_HEADER_LEFT,
                AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_HEAD_LEFT,
                2
            )
        );
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::DIMENSION_DETAILS_RIGHT_COLUMN_WIDTH)
        );

        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.length');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->length;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.width');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->width;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.wheelbase');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->wheelbase;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);

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
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
            CarDetailTemplate::ENGINE_DETAILS_Y_POSITION,
            CarDetailTemplate::DIMENSION_DETAILS_WIDTH,
            CarDetailTemplate::DIMENSION_DETAILS_HEIGHT,
            AbstractCarsDemoTemplate::STYLE_TABLE
        );

        $tableDto->rowHeight = CarDetailTemplate::DIMENSION_DETAILS_ROW_HEIGHT;
        $tableDto->addColumn(
            new ColumnDto(
                'general.engine',
                CarDetailTemplate::DIMENSION_DETAILS_LEFT_COLUMN_WIDTH,
                AbstractCarsDemoTemplate::STYLE_TABLE_CELL_HEADER_LEFT,
                AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_HEAD_LEFT,
                2
            )
        );
        $tableDto->addColumn(
            new ColumnDto('', CarDetailTemplate::DIMENSION_DETAILS_RIGHT_COLUMN_WIDTH)
        );

        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.power');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->power;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.capacity');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->capacity;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.engineLocation');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);

        $cell = new CellContentDto();
        $cell->content = $this->carInformationDto->engineLocation;
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);


        $row = new RowDto();
        $cell = new CellContentDto();
        $cell->content = $this->translator()
                              ->trans('general.wheelDrive');
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $row->addCell($cell);


        $cell = new CellContentDto();
        $cell->content = $this->translationHelper()
                              ->transAttributeValue($this->carInformationDto->wheelDrive);
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $row->addCell($cell);
        $tableDto->addRow($row);

        return $tableDto;
    }

    /**
     * Load up to four car accessories
     *
     * @param Car $car
     *
     * @return array
     * @throws \Exception
     */
    private function collectAccessories(Car $car): array
    {
        $return = [];
        foreach ($car->getAccessories() as $key => $accessory) {
            if (4 == $key) { //Show max 4 accessories
                return $return;
            }
            $return[] = $accessory;
        }

        return $return;
    }
}
