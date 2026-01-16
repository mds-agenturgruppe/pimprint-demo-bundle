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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryList;

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\CellContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\PriceFormatter;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Objectbrick\Data\SaleInformation;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ListRenderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\ElementRenderer
 */
class ListRenderer extends AbstractHelper
{
    /**
     * AccessoryPart DataObject to render
     *
     * @var AccessoryPart
     */
    private AccessoryPart $accessoryPart;

    /**
     * Toggles display of the related Manufacturer DataObject logo
     *
     * @var bool
     */
    private bool $showManufacturerLogo = true;

    /**
     * ListRenderer
     *
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(private readonly PriceFormatter $priceFormatter)
    {
    }

    /**
     * Sets $showManufacturerLogo
     *
     * @param bool $showManufacturerLogo
     *
     * @return void
     */
    public function setShowManufacturerLogo(bool $showManufacturerLogo): void
    {
        $this->showManufacturerLogo = $showManufacturerLogo;
    }

    /**
     * Generates a series of commands based on accessory part attributes.
     *
     * @param AccessoryPart $accessoryPart
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws FilesystemException
     */
    public function render(AccessoryPart $accessoryPart): array
    {
        $this->accessoryPart = $accessoryPart;

        $return = [
            $this->getImageBox(),
            $this->getNameBox(),
            $this->getSaleInformationTable(),
            $this->getPriceBox(),
            $this->getTaxInformationTextBox(),
        ];

        if ($this->showManufacturerLogo) {
            $return[] = $this->getManufacturerLogoImageBox();
        }

        return $return;
    }

    /**
     * create ImageBox command for the accessory part image
     *
     * @return ImageBox|null
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getImageBox(): ?ImageBox
    {
        $asset = $this->accessoryPart->getImage();
        if (!$asset instanceof Hotspotimage) {
            return null;
        }

        $asset = $asset->getImage();
        if (!$asset instanceof Asset\Image) {
            return null;
        }

        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE,
            0,
            0,
            AccessoryListTemplate::IMAGE_SIZE,
            AccessoryListTemplate::IMAGE_SIZE,
            $asset,
            ImageBox::FIT_FILL_PROPORTIONALLY,
        );
        $imageBox->setBoxIdentReferenced('image');

        return $imageBox;
    }

    /**
     * Create TextBox command for the accessory part name
     *
     * @return TextBox
     * @throws FilesystemException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getNameBox(): TextBox
    {
        $textBox = $this->boxGenerator()
                        ->getTextBox(AbstractCarsDemoTemplate::ELEMENT_TEXTBOX, 33.333, 0);
        $textBox->setBoxIdentReferenced('name');

        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_SUBHEADING);
        $text->addString($this->accessoryPart->getGeneratedName());
        $textBox->addText($text);

        return $textBox;
    }

    /**
     * Create Table command for the accessory part sale information
     *
     * @return Table
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getSaleInformationTable(): Table
    {
        //Create the table box and set position, size and style.
        $tableBox = new Table(
            AbstractCarsDemoTemplate::ELEMENT_TABLE,
            AccessoryListTemplate::TABLE_X_POSITION,
            AccessoryListTemplate::TABLE_Y_POSITION,
            AccessoryListTemplate::TABLE_WIDTH,
            AccessoryListTemplate::TABLE_HEIGHT,
            AbstractCarsDemoTemplate::STYLE_TABLE,
        );
        $tableBox->setBoxIdentReferenced('table');

        //Create columns
        $tableBox->addColumn(AccessoryListTemplate::COLUMN_WIDTH_WIDE);
        $tableBox->addColumn(AccessoryListTemplate::COLUMN_WIDTH_SMALL);
        $tableBox->addColumn(AccessoryListTemplate::COLUMN_WIDTH_SPACER);
        $tableBox->addColumn(AccessoryListTemplate::COLUMN_WIDTH_WIDE);
        $tableBox->addColumn(AccessoryListTemplate::COLUMN_WIDTH_SMALL);

        //Create table rows
        foreach ($this->buildSalesInfoContent() as $row) {
            $tableBox->startRow(AccessoryListTemplate::TABLE_ROW_HEIGHT);

            foreach ($row as $cell) {
                $text = new Text($cell->paragraphStyle, $cell->characterStyle);
                $text->addString((string)$cell->content);

                $tableBox->addCell($text, style: $cell->cellStyle);
            }
        }

        return $tableBox;
    }

    /**
     * Collect accessory part sale information from the saleInformation object brick
     * and build the table content/data array.
     *
     * @return array<CellContentDto[]>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function buildSalesInfoContent(): array
    {
        $saleInformation = $this->accessoryPart->getSaleInformation()
                                               ?->getSaleInformation();
        if (!$saleInformation instanceof SaleInformation) {
            return [];
        }

        $firstRow = [];

        //Condition label
        $cell = new CellContentDto(
            $this->translator()
                 ->trans('general.condition')
        );
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $firstRow[] = $cell;

        //Condition value
        $cell = new CellContentDto($saleInformation->getCondition());
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $firstRow[] = $cell;

        //Spacer column
        $cell = new CellContentDto();
        $cell->cellStyle = AccessoryListTemplate::CELL_STYLE_NO_BORDER;
        $firstRow[] = $cell;

        //Milage label
        $cell = new CellContentDto(
            $this->translator()
                 ->trans('general.milage')
        );
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $firstRow[] = $cell;

        //Milage value
        $cell = new CellContentDto($saleInformation->getMilage());
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT;
        $firstRow[] = $cell;


        $secondRow = [];

        //Availability label
        $cell = new CellContentDto(
            $this->translationHelper()
                 ->transTrimColon('general.available-in')
        );
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $secondRow[] = $cell;

        //Availability value
        $cell = new CellContentDto($saleInformation->getAvailabilityType());
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $secondRow[] = $cell;

        //Spacer column
        $cell = new CellContentDto();
        $cell->cellStyle = AccessoryListTemplate::CELL_STYLE_NO_BORDER;
        $secondRow[] = $cell;

        //Pieces label
        $cell = new CellContentDto(
            $this->translationHelper()
                 ->transTrimColon('general.available-pieces')
        );
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT;
        $cell->characterStyle = AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD;
        $secondRow[] = $cell;

        //Pieces value
        $cell = new CellContentDto($saleInformation->getAvailabilityPieces());
        $cell->paragraphStyle = AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT;
        $secondRow[] = $cell;


        return [$firstRow, $secondRow];
    }

    /**
     * Create TextBox command for the accessory part price
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getPriceBox(): TextBox
    {
        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            AccessoryListTemplate::PRICE_X_POSITION,
                            0,
                            30
                        );
        $textBox->setBoxIdentReferenced('price');

        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_PRICE_SPARE_PARTS);
        $text->addString(PriceFormatter::replaceEUR($this->accessoryPart->getOSPrice()));
        $textBox->addText($text);

        return $textBox;
    }

    /**
     * Create TexBox command for the accessory part tax information
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getTaxInformationTextBox(): TextBox
    {
        $price = $this->accessoryPart->getOSPrice();

        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            AccessoryListTemplate::PRICE_X_POSITION,
                            6.818,
                            30
                        );
        $textBox->setBoxIdentReferenced('taxInfo');

        $paragraph = new Text\Paragraph(paragraphStyle: AbstractCarsDemoTemplate::STYLE_PARAGRAPH_INFO_SMALL_RIGHT);
        foreach ($price->getTaxEntries() as $key => $taxEntry) {
            if (0 !== $key) {
                $paragraph->addText(PHP_EOL);
            }

            $name = $taxEntry->getEntry()
                             ->getName();
            $paragraph->addText($name . ": ", AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD);
            if (0 !== $key) {
                $paragraph->addText(PHP_EOL);
            }

            $paragraph->addText($this->priceFormatter->formatTaxEntry($price, $taxEntry));
        }

        $textBox->addParagraph($paragraph);

        return $textBox;
    }

    /**
     * Create ImageBox command for the accessory part manufacturer logo
     *
     * @return ImageBox|null
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getManufacturerLogoImageBox(): ?ImageBox
    {
        $asset = $this->accessoryPart->getManufacturer()
                                     ?->getLogo();
        if (!$asset) {
            return null;
        }

        //the logo should fit into the box LOGO_WIDTH x IMAGE_SIZE,
        //so we calculate the aspect ratio from the asset dimensions
        $heightScaled = AccessoryListTemplate::LOGO_WIDTH * $asset->getHeight() / $asset->getWidth();
        $height = min($heightScaled, AccessoryListTemplate::IMAGE_SIZE);

        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE_UP_RIGHT,
            AccessoryListTemplate::LOGO_X_POSITION,
            0,
            AccessoryListTemplate::LOGO_WIDTH,
            $height,
            $asset,
        );
        $imageBox->setBoxIdentReferenced('logo');

        return $imageBox;
    }
}
