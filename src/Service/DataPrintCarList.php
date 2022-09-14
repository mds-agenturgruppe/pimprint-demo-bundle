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

namespace Mds\PimPrint\DemoBundle\Service;

use App\Model\Product\Car as CarProduct;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractCarProject;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractTemplate;
use Mds\PimPrint\DemoBundle\Project\DataPrint\ListTemplate;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\CarListRenderTrait;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\ListRenderTrait;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class DataPrintCarList.
 *
 * @package Mds\PimPrint\DemoBundle\Service
 */
class DataPrintCarList extends AbstractCarProject
{
    use CarListRenderTrait;

    /**
     * Width of all known size columns.
     *
     * @var float
     */
    const COLUMN_WIDTH_KNOWN =
        ListTemplate::COLUMN_WIDTH_MANUFACTURER
        + ListTemplate::COLUMN_WIDTH_DOORS
        + ListTemplate::COLUMN_WIDTH_YEAR
        + ListTemplate::COLUMN_WIDTH_PRICE
        + ListTemplate::COLUMN_WIDTH_IMAGE;

    /**
     * Space for free defined columns
     *
     * @var float
     */
    const COLUMN_FREE_SPACE = ListTemplate::TABLE_WIDTH - self::COLUMN_WIDTH_KNOWN;

    /**
     * Column amounts for easy calculation.
     *
     * @var int
     */
    const AMOUNT_COLUMNS = 8;

    const AMOUNT_KNOWN_COLUMNS = 5;

    const AMOUNT_DYNAMIC_COLUMNS = self::AMOUNT_COLUMNS - self::AMOUNT_KNOWN_COLUMNS;

    /**
     * Width of dynamic columns.
     *
     * @var float
     */
    const DYNAMIC_COLUMN_WIDTH = self::COLUMN_FREE_SPACE / self::AMOUNT_DYNAMIC_COLUMNS;

    /**
     * Column definition for list tables. Must be defined in using classes.
     *
     * @see \Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\ListRenderTrait::$tableColumnDefinition
     *
     * @var array
     */
    protected array $tableColumnDefinition = [
        [
            //We use one central column definition and filter out columns dependent of current contentType.
            'contentType' => null,
            'ident'       => 'image', //We use the named column feature of Table.
            'translation' => '',
            'width'       => ListTemplate::COLUMN_WIDTH_IMAGE,
            'rowStyle'    => ListTemplate::STYLE_TABLE_CELL_ROW_CENTER,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
            'cellStyle'   => '',
        ],
        [
            'contentType' => self::TYPE_CATEGORY,
            'ident'       => 'manufacturer',
            'translation' => 'general.manufacturer',
            'width'       => ListTemplate::COLUMN_WIDTH_MANUFACTURER,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'contentType' => null,
            'ident'       => 'name',
            'translation' => 'general.name',
            'width'       => self::DYNAMIC_COLUMN_WIDTH,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'contentType' => null,
            'ident'       => 'year',
            'translation' => 'Year',
            'width'       => ListTemplate::COLUMN_WIDTH_YEAR,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'contentType' => self::TYPE_MANUFACTURER,
            'ident'       => 'carClass',
            'translation' => 'general.car-class',
            'width'       => ListTemplate::COLUMN_WIDTH_MANUFACTURER,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'contentType' => null,
            'ident'       => 'bodyStyle',
            'translation' => 'general.body-style',
            'width'       => self::DYNAMIC_COLUMN_WIDTH,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'contentType' => null,
            'ident'       => 'numberOfDoors',
            'translation' => 'Doors',
            'width'       => ListTemplate::COLUMN_WIDTH_DOORS,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW_CENTER,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'contentType' => null,
            'ident'       => 'mileage',
            'translation' => 'general.milage',
            'width'       => self::DYNAMIC_COLUMN_WIDTH,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW_RIGHT,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD_CENTER,
        ],
        [
            'contentType' => null,
            'ident'       => 'price',
            'translation' => 'Price',
            'width'       => ListTemplate::COLUMN_WIDTH_PRICE,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_PRICE,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
    ];

    /**
     * Renders Category headline and adds Category layout elements.
     *
     * @param Category $category
     *
     * @throws \Exception
     * @throws FilesystemException
     */
    protected function renderCategory(Category $category)
    {
        //Set boxIdentReference for content aware updates.
        $this->setBoxIdentReference($category->getId());

        $label = $category->getName();

        //Create the page layout for the category.
        $this->renderPageLayout($label);

        //A category always starts on a new page, because we have the category name on the page layout.
        $this->addCommand(new NextPage());
        $this->renderSubTitle($label, Variable::VARIABLE_Y_POSITION, AbstractTemplate::CONTENT_ORIGIN_TOP);

        //Build table structure.
        $this->initTable();
        $this->setupTableStructure($this->filterColumnDefinitionByType($this->tableColumnDefinition));

        //Add cars to table.
        foreach ($this->loadCarsForCategory($category, CarProduct::OBJECT_TYPE_ACTUAL_CAR) as $car) {
            $this->addTableRowCarVariant($car);
        }

        //Add the Table with SplitTable to CommandQueue.
        $this->addSplitTable();
    }

    /**
     * Renders Manufacturer headline and adds Manufacturer layout elements.
     *
     * @param Manufacturer $manufacturer
     *
     * @throws \Exception
     * @throws FilesystemException
     */
    protected function renderManufacturer(Manufacturer $manufacturer)
    {
        //Set boxIdentReference for content aware updates.
        $this->setBoxIdentReference($manufacturer->getId());

        $label = $manufacturer->getName();
        $logo = $manufacturer->getLogo();

        //Create the page layout for the category.
        $this->renderPageLayout($label, $logo);

        //A manufacturer always starts on a new page, because we have the category name on the page layout.
        $this->addCommand(new NextPage());
        $this->renderSubTitle($label, Variable::VARIABLE_Y_POSITION, AbstractTemplate::CONTENT_ORIGIN_TOP);

        //Build table structure.
        $this->initTable(Variable::VARIABLE_Y_POSITION);
        $this->setupTableStructure($this->filterColumnDefinitionByType($this->tableColumnDefinition));

        //Add cars to table.
        foreach ($this->loadCarsForManufacturer($manufacturer, CarProduct::OBJECT_TYPE_ACTUAL_CAR) as $car) {
            $this->addTableRowCarVariant($car);
        }

        //Add the Table with SplitTable to CommandQueue.
        $this->addSplitTable();
    }

    /**
     * Filters column $definition array by removing columns not shown in current contentType of the publication.
     *
     * @param array $definition
     *
     * @return array
     * @see \Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractProject::$currentType
     */
    private function filterColumnDefinitionByType(array $definition): array
    {
        foreach ($definition as $key => $values) {
            if (null !== $values['contentType'] && $this->currentType != $values['contentType']) {
                unset($definition[$key]);
            }
        }

        return $definition;
    }
}
