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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList;

use App\Model\Product\Category;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\FileBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SplitTable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\Dto\TableRowElementDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\ColumnDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\PriceFormatter;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Objectbrick\Data\SaleInformation;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class PriceListRenderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList
 */
class PriceListRenderer extends AbstractHelper
{
    /**
     * Optional second language
     *
     * @var string|null
     */
    private ?string $secondLanguage = null;

    /**
     * Sets $secondLanguage
     *
     * @param string|null $secondLanguage
     *
     * @return void
     */
    public function setSecondLanguage(?string $secondLanguage): void
    {
        if (!$secondLanguage) {
            return;
        }

        $this->secondLanguage = $secondLanguage;
    }

    /**
     * Creates the `Table`, sets styles, and adds columns and a header row.
     *
     * @return Table
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function createTable(): Table
    {
        $table = new Table(
            AbstractCarsDemoTemplate::ELEMENT_SPLIT_TABLE,
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
            AccessoryPriceListTemplate::TABLE_Y_POSITION,
            AbstractCarsDemoTemplate::CONTENT_WIDTH,
            AbstractCarsDemoTemplate::CONTENT_HEIGHT,
            AccessoryPriceListTemplate::STYLE_TABLE,
        );
        $table->setFit(Table::FIT_FRAME_TO_CONTENT_HEIGHT)
              ->setLineHeight(null);

        $this->createColumns($table);
        $this->createHeadRow($table);

        return $table;
    }

    /**
     * Creates the SplitTable command to split the table dynamically over multiple pages.
     *
     * @return SplitTable
     * @throws \Exception
     */
    public function createSplitTable(): SplitTable
    {
        $splitTable = new SplitTable();

        //Define the CheckNewPage command with CONTENT_BOTTOM as maxYPos.
        $checkPage = new CheckNewPage(
            AbstractCarsDemoTemplate::CONTENT_BOTTOM,
            AccessoryPriceListTemplate::TABLE_Y_POSITION
        );
        $splitTable->setCheckNewPage($checkPage);

        return $splitTable;
    }

    /**
     * Builds row cells content for $accessoryPart.
     *
     * @param AccessoryPart $accessoryPart
     *
     * @return TableRowElementDto
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function createRowElement(AccessoryPart $accessoryPart): TableRowElementDto
    {
        $return = new TableRowElementDto();

        $return->ean = $accessoryPart->getErpNumber();
        $return->name = $this->getName($accessoryPart);
        $return->image = $this->getImage($accessoryPart);
        $return->price = PriceFormatter::replaceEUR($accessoryPart->getOSprice());

        $salesInformation = $accessoryPart->getSaleInformation()
                                          ->getSaleInformation();
        if ($salesInformation instanceof SaleInformation) {
            $return->milage = $this->getMileageFormatted($salesInformation);

            if ($salesInformation->getCondition()) {
                $return->condition = $this->getTranslatedBilingual(
                    'attribute.' . $salesInformation->getCondition(),
                    false
                );
            }
            if ($salesInformation->getAvailabilityType()) {
                $return->availability = $this->getTranslatedBilingual(
                    'attribute.' . $salesInformation->getAvailabilityType(),
                    false
                );
            }
        }

        return $return;
    }

    /**
     * Create the table columns
     *
     * @param Table $table
     *
     * @return void
     * @throws \Exception
     */
    private function createColumns(Table $table): void
    {
        foreach ($this->getTableColumns() as $columnDefinition) {
            $table->addColumn(
                width: $columnDefinition->width,
                style: $columnDefinition->cellStyle
            );
        }
    }

    /**
     * Creates header rows for the defined columns.
     *
     * @param Table $table
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function createHeadRow(Table $table): void
    {
        $table->startRow(type: Table::ROW_TYPE_HEADER);

        foreach ($this->getTableColumns() as $columnDefinition) {
            $text = '';
            if ($columnDefinition->translationKey) {
                $text = $this->getTranslatedBilingual($columnDefinition->translationKey);
            }

            $table->addCell(
                content: $text,
                style:   $columnDefinition->headStyle
            );
        }
    }

    /**
     * Table column definitions to build the table head and body
     *
     * @return ColumnDto[]
     */
    private function getTableColumns(): array
    {
        return [
            new ColumnDto(
                null,
                25,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
            new ColumnDto(
                'EAN',
                26,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
            new ColumnDto(
                'Name',
                35,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
            new ColumnDto(
                'general.available-in',
                29,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
            new ColumnDto(
                'general.condition',
                28,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
            new ColumnDto(
                'general.milage',
                24,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
            new ColumnDto(
                'general.totalPrice',
                23,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT,
                AccessoryPriceListTemplate::STYLE_TABLE_CELL_HEADER
            ),
        ];
    }

    /**
     * Creates the name `Text` element.
     * We do not use the calculated field "generatedName" here because we format it differently.
     *
     * @param AccessoryPart $accessoryPart
     *
     * @return Text
     * @throws \Exception
     */
    private function getName(AccessoryPart $accessoryPart): Text
    {
        $text = new Text();

        $manufacturer = $accessoryPart->getManufacturer();
        if ($manufacturer) {
            $paragraph = new Paragraph(
                $manufacturer->getName(),
                AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_HEAD_LEFT
            );
            $text->addParagraph($paragraph);
        }

        $parts = [];
        $series = $accessoryPart->getSeries();
        if ($series) {
            $parts[] = $series->getName();
        }

        $category = $accessoryPart->getMainCategory();
        if ($category instanceof Category) {
            $parts[] = $category->getName();
        }

        $parts = array_filter($parts);
        if (!empty($parts)) {
            $paragraph = new Paragraph(
                implode(' ', $parts),
                AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_HEAD_LEFT
            );
            $text->addParagraph($paragraph);
        }

        if ($this->secondLanguage && $category instanceof Category) {
            $paragraph = new Paragraph(
                $category->getName($this->secondLanguage),
                AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_LANGUAGE
            );
            $text->addParagraph($paragraph);
        }

        return $text;
    }

    /**
     * Create `ImageBox` for the accessory part image
     *
     * @param AccessoryPart $accessoryPart
     *
     * @return Text|null
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getImage(AccessoryPart $accessoryPart): ?Text
    {
        $asset = $accessoryPart->getImage();
        if ($asset instanceof Hotspotimage) {
            $asset = $asset->getImage();
        }
        if (!$asset instanceof Asset) {
            return null;
        }

        $image = new ImageBox(AbstractCarsDemoTemplate::ELEMENT_IMAGE);
        $image->setAsset($asset)
              ->setFit(FileBox::FIT_FILL_PROPORTIONALLY)
              ->setWidth(AccessoryPriceListTemplate::TABLE_IMAGE_WIDTH)
              ->setHeight(AccessoryPriceListTemplate::TABLE_IMAGE_HEIGHT);

        $paragraph = new Paragraph();
        $paragraph->addComponent($image);

        $text = new Text();
        $text->addParagraph($paragraph);

        return $text;
    }

    /**
     * Get the formatted mileage string
     *
     * @param SaleInformation $saleInformation
     *
     * @return string
     */
    private function getMileageFormatted(SaleInformation $saleInformation): string
    {
        $mileage = $saleInformation->getMilage();
        if (!$mileage instanceof QuantityValue) {
            return '';
        }

        return (string)$mileage;
    }

    /**
     * Create Text with the translation of $translationKey.
     * If the second language is set, the translation will be added in the second language as well.
     *
     * Each translation will be wrapped in a Paragraph.
     *
     * @param string $translationKey
     * @param bool   $firstBold
     *
     * @return Text
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getTranslatedBilingual(string $translationKey, bool $firstBold = true): Text
    {
        $contentFirstLanguage = $this->translationHelper()
                                     ->transTrimColon($translationKey);
        $contentSecondLanguage = $this->secondLanguage ?
            $this->translationHelper()
                 ->transTrimColon($translationKey, $this->secondLanguage) : null;

        return $this->getTextBilingual(
            $contentFirstLanguage,
            $contentSecondLanguage,
            $firstBold
        );
    }

    /**
     * Creates a Text frame with two Paragraphs.
     * One for $contentFirstLanguage and one for $contentSecondLanguage.
     *
     * @param string|null $contentFirstLanguage
     * @param string|null $contentSecondLanguage
     * @param bool        $firstBold
     *
     * @return Text
     * @throws \Exception
     */
    private function getTextBilingual(
        ?string $contentFirstLanguage,
        ?string $contentSecondLanguage = null,
        bool $firstBold = true,
    ): Text {
        $text = new Text();

        if ($contentFirstLanguage) {
            $paragraph = new Paragraph(
                $contentFirstLanguage,
                $firstBold ? AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_HEAD_LEFT : null
            );
            $text->addParagraph($paragraph);
        }

        if ($this->secondLanguage && $contentSecondLanguage) {
            $paragraph = new Paragraph(
                $contentSecondLanguage,
                AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_LANGUAGE
            );
            $text->addParagraph($paragraph);
        }

        return $text;
    }
}
