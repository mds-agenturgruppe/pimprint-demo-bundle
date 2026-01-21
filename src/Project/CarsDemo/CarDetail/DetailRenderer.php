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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarDetail;

use App\Model\Product\AccessoryPart;
use App\Model\Product\Car;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupEnd;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupStart;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarContentDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\Table\TableDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\PriceFormatter;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class DetailRenderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\ElementRenderer
 */
class DetailRenderer extends AbstractHelper
{
    const VARIABLE_PRICE_TAG_X = 'priceXPosition';

    const VARIABLE_ACCESSORY_Y_POS = 'accessoryYPos';

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
     * DetailRenderer
     *
     * @param ContentCreator $detailContentProvider
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        private readonly ContentCreator $detailContentProvider,
        private readonly PriceFormatter $priceFormatter
    ) {
    }

    /**
     * Generates a series of commands based on car attributes.
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    public function render(Car $car): array
    {
        $this->car = $car;
        $this->contentDto = $this->detailContentProvider->createCarDetailContentDto($car);

        $return[] = $this->getMainImageBox();
        $return[] = $this->getLogoImageBox();
        $return = array_merge($return, $this->getPriceBoxes());
        $return = array_merge($return, $this->getDetailTables());
        $return[] = $this->getDescriptionTextBox();

        return array_merge($return, $this->getAccessoryBoxes());
    }

    /**
     * Creates the `ImageBox` for the car’s main image
     *
     * @return ImageBox|null
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getMainImageBox(): ?ImageBox
    {
        $asset = $this->car->getMainImage();
        if (!$asset instanceof Hotspotimage) {
            return null;
        }
        $asset = $asset->getImage();
        if (!$asset) {
            return null;
        }

        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE_ROUNDED,
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
            CarDetailTemplate::MAIN_IMAGE_Y_POSITION,
            CarDetailTemplate::MAIN_IMAGE_WIDTH,
            CarDetailTemplate::MAIN_IMAGE_HEIGHT,
            $asset,
            ImageBox::FIT_FILL_PROPORTIONALLY
        );
        $imageBox->setBoxIdentReferenced('image');

        return $imageBox;
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

        $size = AbstractCarsDemoTemplate::MANUFACTURER_LOGO_SIZE;
        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE_UP_RIGHT,
            CarDetailTemplate::MANUFACTURER_LOGO_X_POSITION,
            AbstractCarsDemoTemplate::PAGE_MARGIN_TOP,
            $size,
            $size,
            $asset
        );
        $imageBox->setBoxIdentReferenced('logo');

        return $imageBox;
    }

    /**
     * Create commands for car price and tax information
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getPriceBoxes(): array
    {
        $return[] = new Variable(
            self::VARIABLE_PRICE_TAG_X,
            AbstractCarsDemoTemplate::PAGE_WIDTH - AbstractCarsDemoTemplate::PAGE_MARGIN_RIGHT
        );

        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_PRICE_TAG,
                            CarDetailTemplate::PRICE_X_POSITION,
                            CarDetailTemplate::PRICE_Y_POSITION,
                            CarDetailTemplate::PRICE_WIDTH,
                            CarDetailTemplate::PRICE_HEIGHT,
                            TextBox::FIT_NO_ADJUST
                        );

        $text = new Text(
            CarDetailTemplate::STYLE_PARAGRAPH_PRICE,
            AbstractCarsDemoTemplate::STYLE_CHARACTER_HIGHLIGHT_2
        );
        if ($this->contentDto->price) {
            $text->addString($this->contentDto->price);
        }

        $textBox->addText($text);
        $textBox->setLeftRelative(self::VARIABLE_PRICE_TAG_X, -1 * CarDetailTemplate::PRICE_WIDTH);
        $return[] = $textBox;

        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            CarDetailTemplate::TAX_X_POSITION,
                            CarDetailTemplate::TAX_Y_POSITION,
                            CarDetailTemplate::TAX_WIDTH,
                        );

        $price = $this->car->getOSPrice();

        $paragraph = new Text\Paragraph(paragraphStyle: AbstractCarsDemoTemplate::STYLE_PARAGRAPH_INFO_RIGHT);
        foreach ($price->getTaxEntries() as $key => $taxEntry) {
            if (0 !== $key) {
                $paragraph->addText(PHP_EOL);
            }

            $name = $taxEntry->getEntry()
                             ->getName();
            $paragraph->addText("$name: ", AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD);
            $paragraph->addText($this->priceFormatter->formatTaxEntry($price, $taxEntry));
        }

        $textBox->addParagraph($paragraph);
        $return[] = $textBox;

        return $return;
    }

    /**
     * Creates a collection of tables with car details
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getDetailTables(): array
    {
        $return = [
            $this->getTable($this->contentDto->productionDto),
            $this->getTable($this->contentDto->generalDto),
            $this->getTable($this->contentDto->conditionDto),
        ];

        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_SUBHEADING);
        $text->addString(
            $this->translator()
                 ->trans('general.car-details')
        );
        $textBox = $this->boxGenerator()
                        ->getTextBox(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
                            CarDetailTemplate::CAR_DETAILS_DETAIL_HEADLINE_Y_POSITION,
                        );
        $textBox->addText($text);

        $return[] = $textBox;
        $return[] = $this->getTable($this->contentDto->dimensionDto);
        $return[] = $this->getTable($this->contentDto->engineDto);

        return $return;
    }

    /**
     * Create `Table` command for a `TableDto`
     *
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getTable(TableDto $tableDto): Table
    {
        $table = $this->createTable($tableDto);

        foreach ($tableDto->rows as $row) {
            $table->startRow($row->rowHeight ?? $tableDto->rowHeight);
            foreach ($row->cells as $cell) {
                $text = new Text($cell->paragraphStyle, $cell->characterStyle);
                $text->addString((string)$cell->content);
                $table->addCell($text, style: $cell->cellStyle ?? AbstractCarsDemoTemplate::STYLE_TABLE_CELL_ROWS_LEFT);
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

        foreach ($tableDto->columns as $column) {
            $table->addColumn($column->width);
            if ($column->translationKey) {
                $headerTitle = $column->translationKey;
            }
            if ($column->colspan) {
                $colspan = $column->colspan;
            }
            if ($column->cellStyle) {
                $cellStyle = $column->cellStyle;
            }
            if ($column->headStyle) {
                $paragraphStyle = $column->headStyle;
            }
        }

        if ($headerTitle) {
            $table->startRow($tableDto->rowHeight, Table::ROW_TYPE_HEADER);
            $text = new Text($paragraphStyle, AbstractCarsDemoTemplate::STYLE_CHARACTER_BOLD);
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
     * Creates a `TextBox` for additional information
     *
     * @return TextBox|null
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getDescriptionTextBox(): ?TextBox
    {
        if (empty($this->contentDto->description)) {
            return null;
        }

        $textBox = new TextBox(
            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
            CarDetailTemplate::DESCRIPTION_X_POSITION,
            CarDetailTemplate::DESCRIPTION_Y_POSITION,
            CarDetailTemplate::DESCRIPTION_TEXT_WIDTH,
            CarDetailTemplate::DESCRIPTION_TEXT_HEIGTH,
        );
        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_SUBHEADING);
        $text->addString(
            $this->translator()
                 ->trans('general.additional-information')
        );
        $textBox->addText($text);

        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_COPY);
        $text->addString($this->contentDto->description);
        $textBox->addText($text);
        $textBox->setBoxIdentReferenced('addInfo');

        return $textBox;
    }

    /**
     * Creates a collection of commands for the car accessories
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getAccessoryBoxes(): array
    {
        $return = [];
        if (empty($this->contentDto->accessories)) {
            return $return;
        }

        $return[] = new GroupStart();

        $textBox = $this->boxGenerator()
                        ->getTextBox(AbstractCarsDemoTemplate::ELEMENT_TEXTBOX, 0, 0);

        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_SUBHEADING);
        $text->addString(
            $this->translator()
                 ->trans('general.accessories')
        );

        $textBox->addText($text);
        $return[] = $textBox;

        $positions = $this->getAccessoryPositions();

        foreach ($this->contentDto->accessories as $index => $accessory) {
            if (!$accessory instanceof AccessoryPart) {
                continue;
            }
            $xPosition = $positions[$index]['xPosition'];
            $yPosition = $positions[$index]['yPosition'];
            $size = 15;

            $return[] = $this->getAccessoryImageBox($xPosition, $yPosition, $size, $accessory, $index);

            $xPosition += $size + AbstractCarsDemoTemplate::BOX_MARGIN_SMALL;
            $return[] = $this->getAccessoryNameTextBox($xPosition, $yPosition, $accessory, 'accessoryName-' . $index);
            $return[] = $this->getAccessoryConditionTextBox($xPosition, $accessory, 'accessoryCondition-' . $index);
            $return[] = $this->getAccessoryPriceTextBox($xPosition, 'accessoryPrice-' . $index);
        }
        $groupEnd = new GroupEnd();
        $groupEnd->setMoveTo(true)
                 ->setLeft(CarDetailTemplate::DESCRIPTION_X_POSITION)
                 ->setTop(CarDetailTemplate::ACCESSORIES_Y_POSITION);

        //If no description accessories are placed at the description position
        if (empty($this->contentDto->description)) {
            $groupEnd->setTop(CarDetailTemplate::DESCRIPTION_Y_POSITION);
        }

        $return[] = $groupEnd;

        return $return;
    }

    /**
     * Creates an `ImageBox` for an accessory
     *
     * @param float         $xPosition
     * @param float         $yPosition
     * @param int           $size
     * @param AccessoryPart $accessory
     * @param int           $index
     *
     * @return ImageBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getAccessoryImageBox(
        float $xPosition,
        float $yPosition,
        int $size,
        AccessoryPart $accessory,
        int $index
    ): ImageBox {
        $imageBox = new ImageBox(
            elementName: AbstractCarsDemoTemplate::ELEMENT_IMAGE_ROUNDED,
            left:        $xPosition,
            top:         $yPosition,
            width:       $size,
            height:      $size,
            fit:         ImageBox::FIT_FILL_PROPORTIONALLY
        );
        $asset = $accessory->getImage()
                           ?->getImage();
        if ($asset instanceof Image) {
            $imageBox->setAsset($asset);
        }
        $imageBox->setBoxIdentReferenced('accessoryImage-' . $index);

        return $imageBox;
    }

    /**
     * Creates a `TextBox` for the accessory name
     *
     * @param float         $xPosition
     * @param float         $yPosition
     * @param AccessoryPart $accessory
     * @param string        $boxIdent
     *
     * @return TextBox
     * @throws FilesystemException
     * @throws \Exception
     */
    private function getAccessoryNameTextBox(
        float $xPosition,
        float $yPosition,
        AccessoryPart $accessory,
        string $boxIdent,
    ): TextBox {
        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TITLE_ACCESSORIES);
        $text->addString($accessory->getGeneratedName());

        $textBox = new TextBox(
            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
            $xPosition,
            $yPosition,
            CarDetailTemplate::ACCESSORY_NAME_WIDTH,
            CarDetailTemplate::ACCESSORY_NAME_HEIGHT,
            TextBox::FIT_NO_ADJUST
        );
        $textBox->addText($text);
        $textBox->setBoxIdentReferenced($boxIdent);
        $textBox->setVariable(self::VARIABLE_ACCESSORY_Y_POS, Variable::POSITION_BOTTOM);

        return $textBox;
    }

    /**
     * Creates a `TextBox` for the accessory condition
     *
     * @param float         $xPosition
     * @param AccessoryPart $accessory
     * @param string        $boxIdent
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getAccessoryConditionTextBox(float $xPosition, AccessoryPart $accessory, string $boxIdent): TextBox
    {
        $condition = (string)$accessory->getSaleInformation()
                                       ?->getSaleInformation()
                                       ?->getCondition();
        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_COPY_SMALL);
        $conditionKey = $this->translator()
                             ->trans('general.condition');
        $conditionValue = $this->translationHelper()
                               ->transAttributeValue($condition);
        $text->addString("$conditionKey: $conditionValue");

        $textBox = $this->boxGenerator()
                        ->getTextBoxTopRelative(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            $xPosition,
                            self::VARIABLE_ACCESSORY_Y_POS,
                            AbstractCarsDemoTemplate::BOX_MARGIN_SMALLER
                        );
        $textBox->addText($text);
        $textBox->setBoxIdentReferenced($boxIdent);
        $textBox->setVariable(self::VARIABLE_ACCESSORY_Y_POS, Variable::POSITION_BOTTOM);

        return $textBox;
    }

    /**
     * Creates a `TextBox` for the accessory price
     *
     * @param float  $xPosition
     * @param string $boxIdent
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getAccessoryPriceTextBox(
        float $xPosition,
        string $boxIdent
    ): TextBox {
        $text = new Text(CarDetailTemplate::STYLE_PARAGRAPH_ACCESSORY_PRICE);
        $text->addString($this->contentDto->price);

        $textBox = $this->boxGenerator()
                        ->getTextBoxTopRelative(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            $xPosition,
                            self::VARIABLE_ACCESSORY_Y_POS,
                            AbstractCarsDemoTemplate::BOX_MARGIN_SMALLER
                        );
        $textBox->setBoxIdentReferenced($boxIdent);
        $textBox->addText($text);

        return $textBox;
    }

    /**
     * Returns positions for the four accessories
     *
     * @return array[]
     */
    private function getAccessoryPositions(): array
    {
        return [
            [
                'xPosition' => CarDetailTemplate::ACCESSORIES_X_POSITION_LEFT,
                'yPosition' => CarDetailTemplate::ACCESSORIES_Y_POSITION_TOP,
            ],
            [
                'xPosition' => CarDetailTemplate::ACCESSORIES_X_POSITION_RIGHT,
                'yPosition' => CarDetailTemplate::ACCESSORIES_Y_POSITION_TOP,
            ],
            [
                'xPosition' => CarDetailTemplate::ACCESSORIES_X_POSITION_LEFT,
                'yPosition' => CarDetailTemplate::ACCESSORIES_Y_POSITION_BOTTOM,
            ],
            [
                'xPosition' => CarDetailTemplate::ACCESSORIES_X_POSITION_RIGHT,
                'yPosition' => CarDetailTemplate::ACCESSORIES_Y_POSITION_BOTTOM,
            ],
        ];
    }
}
