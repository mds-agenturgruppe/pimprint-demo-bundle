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
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\TableDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\PriceFormatter;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Pimcore\Model\Asset\Image;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class LabelRenderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel
 */
class LabelRenderer extends AbstractHelper
{
    /**
     * The current car object
     *
     * @var Car
     */
    private Car $car;

    /**
     * Collected car data for rendering
     *
     * @var CarContentDto
     */
    private CarContentDto $contentDto;

    /**
     * LabelRenderer
     *
     * @param ContentCreator $contentCreator
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        private readonly ContentCreator $contentCreator,
        private readonly PriceFormatter $priceFormatter
    ) {
    }

    /**
     * Generates a series of commands based on car attributes.
     *
     * @param Car $car
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function render(Car $car): array
    {
        $this->car = $car;

        $this->contentDto = $this->contentCreator->createCarDetailContentDto($car);

        $return = [];
        $return[] = $this->getTitleTextBox();
        $return[] = $this->getLogoImageBox();
        $return = array_merge($return, $this->getDetailTables());
        $return[] = $this->getPriceTextBox();
        $return[] = $this->getTaxTextBox();

        return $return;
    }

    /**
     * Creates a `TextBox` for the title
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getTitleTextBox(): TextBox
    {
        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            CarSalesLabelTemplate::CONTENT_ORIGIN_LEFT,
                            CarSalesLabelTemplate::TITLE_Y_POSITION,
                            CarSalesLabelTemplate::CONTENT_WIDTH
                        );

        $text = new Text(CarSalesLabelTemplate::STYLE_PARAGRAPH_TITLE);
        $text->addString((string)$this->car->getName());

        $textBox->addText($text);
        $textBox->setBoxIdentReferenced('title');

        return $textBox;
    }

    /**
     * Creates the `ImageBox` for the car’s manufacturer logo
     *
     * @return ImageBox|null
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getLogoImageBox(): ?ImageBox
    {
        $asset = $this->car->getManufacturer()
                           ?->getLogo();
        if (!$asset instanceof Image) {
            return null;
        }

        //the logo should have LOGO_HEIGHT. Width is calculated based on the aspect ratio.
        //so we calculate the aspect ratio from the asset dimensions
        $widthScaled = CarSalesLabelTemplate::MANUFACTURER_LOGO_HEIGHT / ($asset->getHeight() / $asset->getWidth());
        $width = min($widthScaled, CarSalesLabelTemplate::MANUFACTURER_LOGO_MAX_WIDTH);

        //Calculate the x position of the logo box based on the calculated width
        $xPos = CarSalesLabelTemplate::CONTENT_WIDTH - $width + CarSalesLabelTemplate::PAGE_MARGIN_LEFT;

        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE,
            $xPos,
            CarSalesLabelTemplate::CONTENT_ORIGIN_TOP,
            $width,
            CarSalesLabelTemplate::MANUFACTURER_LOGO_HEIGHT,
            $asset
        );
        $imageBox->setBoxIdentReferenced('logo');

        return $imageBox;
    }

    /**
     * Create `Table` command for a `TableDto`
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getTable(
        TableDto $tableDto
    ): Table {
        $table = $this->createTable($tableDto);

        foreach ($tableDto->rows as $row) {
            $table->startRow($row->rowHeight ?? $tableDto->rowHeight);
            foreach ($row->cells as $cell) {
                $text = new Text($cell->paragraphStyle);
                $text->addString((string)$cell->content);
                $table->addCell($text, style: $cell->cellStyle);
            }
        }

        return $table;
    }

    /**
     * Creates a basic `Table` box structure with positioning, columns, and header row.
     *
     * @param TableDto $tableDto
     *
     * @return Table
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function createTable(TableDto $tableDto): Table
    {
        $table = new Table(
            AbstractCarsDemoTemplate::ELEMENT_TABLE,
            $tableDto->xPosition,
            $tableDto->yPosition,
            $tableDto->width,
            $tableDto->height,
            $tableDto->tableStyle
        );

        $table->setRowHeight($tableDto->rowHeight);

        $headerTitle = null;
        $colspan = null;
        $cellStyle = null;
        $paragraphStyle = null;
        $headRowHeight = null;

        foreach ($tableDto->columns as $column) {
            $table->addColumn($column->width);
            if ($column->colspan) {
                $colspan = $column->colspan;
            }
            if ($column->translationKey) {
                $headerTitle = $column->translationKey;
            }

            if ($column->cellStyle) {
                $cellStyle = $column->cellStyle;
            }
            if ($column->headStyle) {
                $paragraphStyle = $column->headStyle;
            }
            if ($column->headRowHeight) {
                $headRowHeight = $column->headRowHeight;
            }
        }

        if ($headerTitle) {
            $table->startRow($headRowHeight ?? $tableDto->rowHeight, Table::ROW_TYPE_HEADER);
            $text = new Text($paragraphStyle);
            $text->addString(
                $this->translator()
                     ->trans($headerTitle)
            );
            $table->addCell(
                content: $text,
                colspan: $colspan,
                style:   $cellStyle
            );
        }

        return $table;
    }

    /**
     * Returns a collection of tables with car details
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getDetailTables(): array
    {
        $return[] = $this->getTable($this->contentDto->productionDto);
        $return[] = $this->getTable($this->contentDto->generalDto);

        $text = new Text(CarSalesLabelTemplate::STYLE_PARAGRAPH_SUBHEADLINE);
        $text->addString(
            $this->translator()
                 ->trans('general.car-details')
        );
        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            CarSalesLabelTemplate::TECHNICAL_DETAILS_X_POSITION,
                            CarSalesLabelTemplate::TECHNICAL_DETAILS_Y_POSITION,
                        );
        $textBox->addText($text);
        $return[] = $textBox;

        $return[] = $this->getTable($this->contentDto->dimensionDto);
        $return[] = $this->getTable($this->contentDto->engineDto);

        return $return;
    }

    /**
     * Creates a `TextBox` for the price
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getPriceTextBox(): TextBox
    {
        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            CarSalesLabelTemplate::CONTENT_ORIGIN_LEFT,
                            CarSalesLabelTemplate::PRICE_Y_POSITION,
                            CarSalesLabelTemplate::CONTENT_WIDTH,
                        );
        $text = new Text(CarSalesLabelTemplate::STYLE_PARAGRAPH_PRICE);
        $text->addString($this->contentDto->price);
        $textBox->addText($text);
        $textBox->setBoxIdentReferenced('price');

        return $textBox;
    }

    /**
     * Creates a `TextBox` for the tax information
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getTaxTextBox(): TextBox
    {
        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            CarSalesLabelTemplate::CONTENT_ORIGIN_LEFT,
                            CarSalesLabelTemplate::TAX_Y_POSITION
                        );
        $taxInfo = [];
        $price = $this->car->getOSPrice();
        foreach ($price->getTaxEntries() as $taxEntry) {
            $name = $taxEntry->getEntry()
                             ->getName();
            $taxInfo[] = "$name: " . $this->priceFormatter->formatTaxEntry($price, $taxEntry);
        }
        $text = new Text(CarSalesLabelTemplate::STYLE_PARAGRAPH_TAX_INFO);
        $text->addString(implode(' | ', $taxInfo));

        $textBox->addText($text);
        $textBox->setBoxIdentReferenced('tax');

        return $textBox;
    }
}
