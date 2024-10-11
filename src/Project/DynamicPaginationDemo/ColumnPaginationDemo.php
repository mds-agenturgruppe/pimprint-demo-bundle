<?php
/**
 * mds Agenturgruppe GmbH
 *
 * This source file is licensed under GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 */

namespace Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo;

use App\Model\Product\Car;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupEnd;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupStart;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SplitTable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table as TableCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\Template;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\Project\RenderingProject;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Mds\PimPrint\DemoBundle\Project\Traits\FakerGeneratorTrait;
use Pimcore\Model\DataObject\Car\Listing;
use Pimcore\Model\DataObject\Manufacturer;
use Pimcore\Translation\Translator;

/**
 * Class ColumnPaginationDemo
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo
 */
class ColumnPaginationDemo extends RenderingProject
{
    use FakerGeneratorTrait;

    /**
     * Manufacturer to render document for
     *
     * @var Manufacturer
     */
    private Manufacturer $manufacturer;

    /**
     * Car Ids to render
     *
     * @var int[]
     */
    private array $carIds;

    /**
     * ColumnPaginationDemo constructor
     *
     * @param ManufacturerTreeBuilder $treeBuilder
     * @param PaginationTemplate      $template
     * @param Translator              $translator
     */
    public function __construct(
        private ManufacturerTreeBuilder $treeBuilder,
        private PaginationTemplate $template,
        private Translator $translator
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return $this->treeBuilder->getPublicationsTree();
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    public function buildPublication(): void
    {
        try {
            $this->setupManufacturer();
            $this->setupCars();
        } catch (\Exception $exception) {
            $this->addPreMessage($exception->getMessage());

            return;
        }

        $this->startRendering(false);

        $this->setDocumentProperties();
        $this->registerVariables();
        $this->registerTemplateCommands();

        foreach ($this->carIds as $carId) {
            $car = Car::getById($carId);
            if (!$car instanceof Car) {
                continue;
            }
            $this->renderCar($car);
        }

        $this->stopRendering();
    }

    /**
     * Loads Manufacturer to render document for.
     *
     * @return void
     * @throws \Exception
     */
    private function setupManufacturer(): void
    {
        $manufacturer = Manufacturer::getById($this->pluginParams->get(PluginParameters::PARAM_PUBLICATION));
        if ($manufacturer instanceof Manufacturer) {
            $this->manufacturer = $manufacturer;

            return;
        }

        throw new \Exception('Please select an Manufacturer object to render document.');
    }

    /**
     * Document renders a fictive table-list for each "actual car" of selected Manufacturer.
     * Method asserts (and loads) that manufacturer has "actual cars" to render.
     *
     * @return void
     * @throws \Exception
     */
    private function setupCars(): void
    {
        $listing = new Listing();
        $listing->addConditionParam('objectType = :objectType', ['objectType' => 'actual-car']);
        $listing->filterByManufacturer($this->manufacturer);
        $listing->setOrderKey('productionYear');
        $listing->setOrder('asc');

        $this->carIds = $listing->loadIdList();
        if (empty($this->carIds)) {
            throw new \Exception(
                'The chosen an Manufacturer has no actual-cars assigned. Please choose another one.'
            );
        }
    }

    /**
     * Document settings can be adjusted for the current open document in InDesign in which generation takes place.
     * We add 50 pages to the document to have "probably" enough pages.
     *
     * @return void
     * @throws \Exception
     */
    private function setDocumentProperties(): void
    {
        $properties = new DocumentSetup();
        $properties->setNumberOfPages(50);
        $this->addCommand($properties);
    }

    /**
     * Initializes variables for dynamic positioning and layout breaking of generated document.
     * All Left/Top box positioning in this document are coupled to this two variables.
     *
     * @return void
     * @throws \Exception
     */
    private function registerVariables(): void
    {
        //For column pagination we always left position elements at the xPos variable.
        //We initialize it with origin left.
        //While placing elements on the document we update this variable with the left position of the placed element.
        $this->addCommand(new Variable(Variable::VARIABLE_X_POSITION, PaginationTemplate::CONTENT_ORIGIN_LEFT));

        //We always top position elements at the yPos variable.
        //We initialize it with the origin top
        //While placing elements on the document we update this variable with the bottom position of the placed element.
        $this->addCommand(new Variable(Variable::VARIABLE_Y_POSITION, PaginationTemplate::CONTENT_ORIGIN_TOP));
    }

    /**
     * Registers commands to create the page layout for left/right facing pages.
     *
     * PimPrint places the registered commands automatically on every new page, that is created by auto pagination.
     *
     * In this example we just use the header-groups headerLeft/headerRight from the template document
     * and place some dynamic Text.
     *
     * The Template command can be sent as often as it is needed. Each send command overwrites any already sent before.
     * Use this when you have content dependent changes in page layout elements.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function registerTemplateCommands(): void
    {
        $template = new Template();

        //Basic page layout is just a group in the template that is copied to the document
        $template->addCommand(
            new CopyBox(PaginationTemplate::ELEMENT_HEADER_LEFT),
            Template::SIDE_FACING_LEFT
        );
        $template->addCommand(
            new CopyBox(PaginationTemplate::ELEMENT_HEADER_RIGHT, 5.65),
            Template::SIDE_FACING_RIGHT
        );

        //Right facing page
        $textBox = new TextBox(PaginationTemplate::ELEMENT_TEXTBOX, (PaginationTemplate::CONTENT_RIGHT - 80), 4, 80, 4);

        //Manufacture name on page layout element.
        $text = new Text();
        $text->addString($this->manufacturer->getName(), PaginationTemplate::PARAGRAPH_HEADER_TEXT_RIGHT);
        $textBox->addText($text);
        $template->addCommand($textBox, Template::SIDE_FACING_RIGHT);

        //Left facing page
        $textBox = new TextBox(PaginationTemplate::ELEMENT_TEXTBOX, PaginationTemplate::CONTENT_ORIGIN_LEFT, 4, 80, 4);

        //Manufacture name on page layout element.
        $text = new Text();
        $text->addString($this->manufacturer->getName(), PaginationTemplate::PARAGRAPH_HEADER_TEXT_LEFT);
        $textBox->addText($text);
        $template->addCommand($textBox, Template::SIDE_FACING_LEFT);

        //If user created a document without facing pages, we define for fallback to use always ELEMENT_HEADER_LEFT
        $template->addCommand(new CopyBox(PaginationTemplate::ELEMENT_HEADER_LEFT));
        $template->addCommand($textBox);

        //Send the Template command.
        $this->addCommand($template);

        //Open the first page after registering the template commands to have rendered the layout on the first page.
        $this->addCommand(new GoToPage(1));
    }

    /**
     * Renders $car.
     *
     * In this demo a car consists of intro content followed by a table with fictive demo data.
     *
     * @param Car $car
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function renderCar(Car $car): void
    {
        $this->setBoxIdentReference($car->getId());

        $this->renderCarIntro($car);
        $this->renderCarTable();
    }

    /**
     * Renders car intro group containing Name, Image and additional info.
     *
     * Elements are placed starting form the top left corner of the page.
     * When closing the group the whole group is moved to the correct relative position.
     *
     * @param Car $car
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function renderCarIntro(Car $car): void
    {
        //Sizes and positions are deferred from COLUMN_WITH and BOX_MARGIN to have the demo layout
        //adjustable by changing PaginationTemplate constants.

        $headlineWidth = PaginationTemplate::COLUMN_WITH / 2;
        $imageLeft = $headlineWidth + PaginationTemplate::BOX_MARGIN;
        $imageWidth = PaginationTemplate::COLUMN_WITH - $imageLeft;

        $this->addCommand(new GroupStart());

        $headline = $this->manufacturer->getName() . ' ' . $car->getName();
        $headlineBox = new TextBox(PaginationTemplate::ELEMENT_HEADLINE, 0, 0);
        $headlineBox->setBoxIdentReferenced('headline')
                    ->addString($headline)
                    ->setHeight(20)
                    ->setWidth($headlineWidth)
                    ->setFit(TextBox::FIT_FRAME_TO_CONTENT_HEIGHT)
                    ->setVariable('headlineBottom', Variable::POSITION_BOTTOM);
        $this->addCommand($headlineBox);

        $image = $car->getMainImage();
        if ($image) {
            $asset = $image->getImage();
            if ($asset) {
                $imageBox = new ImageBox(PaginationTemplate::ELEMENT_IMAGE, $imageLeft, 0);
                $imageBox->setBoxIdentReferenced('image')
                         ->setAsset($asset)
                         ->setWidth($imageWidth)
                         ->setHeight(20)
                         ->setFit(ImageBox::FIT_FILL_PROPORTIONALLY);
                $this->addCommand($imageBox);
            }
        }

        $textBox = new TextBox(PaginationTemplate::ELEMENT_TEXTBOX);
        $textBox->setBoxIdentReferenced('info')
                ->addText($this->getInfoBoxContent($car))
                ->setWidth($imageLeft)
                ->setHeight(30)
                ->setFit(TextBox::FIT_FRAME_TO_CONTENT_HEIGHT)
                ->setTopRelative('headlineBottom', PaginationTemplate::BOX_MARGIN);
        $this->addCommand($textBox);


        $groupEnd = new GroupEnd();
        $groupEnd->setMoveTo(true)
//        MarginOffset places the group into a new column if less than 25mm are left on the page after group end.
                 ->setCheckNewColumn(
                     $this->template->getCheckNewColumn(PaginationTemplate::SPLIT_HEIGHT_MARGIN_OFFSET)
                 )->setLeftRelative(Variable::VARIABLE_X_POSITION)
                 ->setTopRelative(Variable::VARIABLE_Y_POSITION, PaginationTemplate::BOX_MARGIN)
                 ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM)
                 ->setVariable(Variable::VARIABLE_X_POSITION, Variable::POSITION_LEFT);

        $this->addCommand($groupEnd);
    }

    /**
     * Generates the text content of car infobox
     *
     * @param Car $car
     *
     * @return Text
     * @throws FilesystemException
     */
    private function getInfoBoxContent(Car $car): Text
    {
        $color = '';
        //to have variance we randomize the color display
        if (rand(0, 1) == 1) {
            if (!empty($car->getColor())) {
                $color = implode(', ', $car->getColor());
            }
        }

        $content = [
            $this->translator->trans('general.productionYear') => $car->getProductionYear(),
            $this->translator->trans('general.car-class')      => $car->getCarClass(),
            $this->translator->trans('general.color')          => $color,
        ];

        $content = array_filter($content);
        foreach ($content as $label => &$value) {
            $value = "$label: $value";
        }

        $text = new Text();
        $text->addHtml(implode('<br>', $content));

        return $text;
    }

    /**
     * Renders fictive table
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     */
    private function renderCarTable(): void
    {
        $table = $this->createCarTable();
        $table->setLeftRelative(Variable::VARIABLE_X_POSITION)
              ->setTopRelative(Variable::VARIABLE_Y_POSITION, PaginationTemplate::BOX_MARGIN)
              ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM)
              ->setVariable(Variable::VARIABLE_X_POSITION, Variable::POSITION_LEFT);

        $split = new SplitTable($table, $this->template->getCheckNewColumn());
        $this->addCommand($split);
    }

    /**
     * Creates fictive table for demo purpose
     *
     * @return Table
     * @throws FilesystemException
     * @throws \Exception
     */
    private function createCarTable(): Table
    {
        $table = new Table(PaginationTemplate::ELEMENT_TABLE);
        $table->setBoxIdentReferenced('table')
              ->setFit(Table::FIT_FRAME_TO_CONTENT)
              ->setWidth(PaginationTemplate::COLUMN_WITH)
              ->setHeight(20) //InDesign needs a height to place content in.
              ->setRowHeight(5);

        $columnHalfWidth = PaginationTemplate::COLUMN_WITH / 2;
        $table->addColumn($columnHalfWidth)
              ->addColumn($columnHalfWidth);

        //TableHeader will be repeated in each table split.
        $table->startRow(null, TableCommand::ROW_TYPE_HEADER);
        $table->addCell('Column A', null, 1, PaginationTemplate::TABLE_CELL_HEAD)
              ->addCell('Column B', null, 1, PaginationTemplate::TABLE_CELL_HEAD);

        //Random rows
        for ($i = 0; $i <= rand(10, 100); $i++) {
            $table->startRow();

            $wordA = $this->getFaker()
                          ->words(2, true);
            $wordB = $this->getFaker()
                          ->words(2, true);

            $table->addCell($wordA, null, 1, PaginationTemplate::TABLE_CELL_CONTENT)
                  ->addCell($wordB, null, 1, PaginationTemplate::TABLE_CELL_CONTENT);
        }

        return $table;
    }
}
