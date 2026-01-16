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

namespace Mds\PimPrint\DemoBundle\Project\LocalizationDemo;

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\SortLayers;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Mds\PimPrint\CoreBundle\Project\MasterLocaleRenderingProject;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Mds\PimPrint\DemoBundle\Project\Traits\FakerGeneratorTrait;
use Mds\PimPrint\DemoBundle\Project\Traits\LoadRandomAssetTrait;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Manufacturer;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LocalizationProject
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\LocalizationDemo
 */
class LocalizationProject extends MasterLocaleRenderingProject
{
    use LoadRandomAssetTrait;
    use FakerGeneratorTrait;

    /**
     * Car object
     *
     * @var Car
     */
    private Car $car;

    /**
     * Manufacturer object
     *
     * @var Manufacturer
     */
    private Manufacturer $manufacturer;

    /**
     * LocalizationProject constructor
     *
     * @param CarTreeBuilder      $treeBuilder
     * @param TranslatorInterface $translator
     */
    public function __construct(
        private readonly CarTreeBuilder $treeBuilder,
        private readonly TranslatorInterface $translator
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
     * @throws FilesystemException
     * @throws \Exception
     */
    public function buildPublication(): void
    {
        try {
            $this->setupCar();
            $this->setupManufacturer();
        } catch (\Exception $exception) {
            $this->addPreMessage($exception->getMessage());

            return;
        }

        $this->startRendering();

        $this->setDocumentSettings();
        $this->renderExamplePage();
        $this->sortLayers();
        $this->stopRendering();
    }

    /**
     * Sets default demo page settings
     *
     * @return void
     * @throws \Exception
     */
    private function setDocumentSettings(): void
    {
        $command = new DocumentSetup(new ExampleTemplate(), 2);
        $this->addCommand($command);
    }

    /**
     * Renders example localized page.
     * For a detailed explanation of the localization API refer to:
     * \Mds\PimPrint\DemoBundle\Project\CommandDemo\Localization::build
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     * @see \Mds\PimPrint\DemoBundle\Project\CommandDemo\Localization::build
     */
    private function renderExamplePage(): void
    {
        $this->rendersLayoutBar(ExampleTemplate::PAGE_MARGIN_TOP);
        $this->renderDemoDescription();
        $this->rendersLayoutBar(40);
        $this->renderManufacturer(50);
        $this->renderCar(80);
        $this->renderUseMasterLocaleExamples(180);
    }

    /**
     * Load car object to render the document.
     * This demo only renders 'Actual-Car' objects
     *
     * @return void
     * @throws \Exception
     */
    private function setupCar(): void
    {
        $car = Car::getById($this->pluginParams->get(PluginParameters::PARAM_PUBLICATION));
        if ($car instanceof Car) {
            if ('actual-car' === $car->getObjectType()) {
                $this->car = $car;

                return;
            }
        }

        throw new \Exception('Please select an "Actual-Car" object to render document.');
    }

    /**
     * Load the manufacturer object for the current car.
     * This demo needs a Manufacturer assigned to the Car
     *
     * @return void
     * @throws \Exception
     */
    private function setupManufacturer(): void
    {
        $manufacturer = $this->car->getManufacturer();
        if ($manufacturer instanceof Manufacturer) {
            $this->manufacturer = $manufacturer;

            return;
        }

        throw new \Exception('Selected Car object must have a Manufacturer assigned to render the document');
    }

    /**
     * Render non-localized manufacturer elements on layer "Manufacturer"
     *
     * @param int $topPos
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     */
    private function renderManufacturer(int $topPos): void
    {
        //Manufacturer elements are rendered on a separate layer
        $this->addCommand(new SetLayer('Manufacturer'));

        //Set the manufacturer id as the boxIdent for content-sensitive updates.
        $this->setBoxIdentReference($this->manufacturer->getId());

        //Manufacturer data is not localized. We render non-localized elements.
        //As the default setting for all elements is non-localized, we don't need to call setLocalized().

        $logo = $this->manufacturer->getLogo();
        if ($logo instanceof Image) {
            $logoBox = new ImageBox(
                ExampleTemplate::ELEMENT_IMAGE,
                12.7,
                $topPos,
                20,
                20,
                $logo
            );
            $logoBox->setBoxIdentReferenced('logo');
            $this->addCommand($logoBox);
        }

        $nameBox = new TextBox(
            ExampleTemplate::ELEMENT_HEADLINE,
            12.7 + 20 + 5,
            $topPos + 7.883,
            100,
            4.3,
            TextBox::FIT_FRAME_TO_CONTENT
        );
        $nameBox->addString($this->manufacturer->getName());
        $nameBox->setBoxIdentReferenced('name');
        $this->addCommand($nameBox);
    }

    /**
     * Render localized and non-localized car elements.
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function renderCar(int $topPos): void
    {
        //Set the car id as the boxIdent for content-sensitive updates.
        $this->setBoxIdentReference($this->car->getId());

        //Put the elements on a separate layer
        $this->addCommand(new SetLayer('Car'));

        $this->renderCarName($topPos);
        $this->renderCarImage($topPos);
        $this->renderCarDescription();
    }

    /**
     * Render localized car name.
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     */
    private function renderCarName(int $topPos): void
    {
        $carNameBox = new TextBox(
            ExampleTemplate::ELEMENT_HEADLINE,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            $topPos,
            100,
            20,
            TextBox::FIT_FRAME_TO_CONTENT
        );
        $carNameBox->addString($this->buildCarName());
        $carNameBox->setVariable('descriptionTop', Variable::POSITION_BOTTOM);
        $carNameBox->setBoxIdentReferenced('name');

        //Car name has localized content -> we localize the box.
        $carNameBox->setLocalized();

        //Localized elements have the `setUseMasterLocaleDimension` option.
        //This option determines whether localized elements should match the dimensions of the master locale.
        //@see \Mds\PimPrint\CoreBundle\InDesign\Command\AbstractBox::setUseMasterLocaleDimension

        //Option AbstractBox::USE_MASTER_LOCALE_POSITION:
        //Uses the master locale's position (left, top).
        //Takes dimensions (width, height) and fit from the command.
        $carNameBox->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_POSITION);

        $this->addCommand($carNameBox);
    }

    /**
     * Build the localized car name.
     * If the name is empty, generates a placeholder word.
     *
     * @return string
     * @throws \Exception
     */
    private function buildCarName(): string
    {
        $name = $this->car->getName();
        if (empty($name)) {
            $name = $this->getFaker()
                         ->word();
        }

        $parts = [$name];

        $color = current($this->car->getColor());
        if (!empty($color)) {
            $parts[] = '-';
            $parts[] = $this->translator->trans("attribute.$color");
        }

        return implode(' ', array_filter($parts));
    }

    /**
     * Renders a non-localized car image.
     *
     * @param int $topPos
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     */
    private function renderCarImage(int $topPos): void
    {
        $image = $this->getCarImage();
        if (!$image instanceof Image) {
            return;
        }

        $imageBox = new ImageBox(
            ExampleTemplate::ELEMENT_IMAGE,
            ExampleTemplate::CONTENT_RIGHT - 60,
            $topPos,
            60,
            45,
            $image,
            ImageBox::FIT_FILL_PROPORTIONALLY
        );

        $imageBox->setBoxIdentReferenced('image');

        $this->addCommand($imageBox);
    }

    /**
     * Get first image from the car image gallery
     *
     * @return Image|null
     */
    private function getCarImage(): ?Image
    {
        $gallery = $this->car->getGallery();
        if (empty($gallery)) {
            return null;
        }

        foreach ($gallery->getItems() as $image) {
            if ($image instanceof Hotspotimage) {
                $image = $image->getImage();
            }
            if ($image instanceof Image) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Render localized car description
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function renderCarDescription(): void
    {
        $textBox = new TextBox(
            ExampleTemplate::ELEMENT_TEXTBOX,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            null, //using top relative positioning
            120,
            100,
            TextBox::FIT_FRAME_TO_CONTENT_HEIGHT
        );
        $textBox->setTopRelative('descriptionTop', 5);
        $textBox->setBoxIdentReferenced('text');

        $text = new Text(ExampleTemplate::STYLE_PARAGRAPH_COPYTEXT);
        $text->addHtml($this->buildCarDescription());
        $textBox->addText($text);

        //Description is localized
        $textBox->setLocalized();

        //Option AbstractBox::USE_MASTER_LOCALE_WIDTH:
        //Uses the master locale's position (left, top).
        //Takes dimensions (width, height) and fit from the command.
        $textBox->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_WIDTH);

        $this->addCommand($textBox);
    }

    /**
     * Builds localized car description.
     * If the description is empty, generates a random sentence with
     * the same word amount as the english description.
     *
     * @return string
     * @throws \Exception
     */
    private function buildCarDescription(): string
    {
        $description = $this->car->getDescription();
        if (!empty($description)) {
            return $description;
        }

        $description = $this->car->getDescription('en');

        return $this->getFaker()
                    ->sentence(str_word_count($description));
    }

    /**
     * Renders localized elements to demonstrate all possible `useMasterLocaleDimension` options.
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function renderUseMasterLocaleExamples(int $topPos): void
    {
        $this->renderFixedSizeText($topPos);
        $this->renderFixedHeightText($topPos);
        $this->renderDontUseMasterLocaleText();

        $topPos += 30;

        //useMasterLocaleDimension options are available for all AbstractBox elements.
        $this->renderCopyBox($topPos);
        $this->renderImageBox($topPos);
        $this->renderTableBox($topPos);
    }

    /**
     * Renders a localized TextBox with random content to demonstrate AbstractBox::USE_MASTER_LOCALE_ALL
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     */
    private function renderFixedSizeText(int $topPos): void
    {
        $textBox = new TextBox(
            ExampleTemplate::ELEMENT_HEADLINE,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            $topPos,
            70,
            20,
            TextBox::FIT_FRAME_TO_CONTENT
        );

        $textBox->addString(
            $this->getFaker()
                 ->words(4, true)
        );
        $textBox->setBoxIdent('fixedDimensionText');

        //We define a localized TextBox
        $textBox->setLocalized();

        //Option AbstractBox::USE_MASTER_LOCALE_ALL:
        //Uses the master locale's position (left, top) and dimensions (width, height).
        //Does not apply any fit adjustments.
        $textBox->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_ALL);

        $this->addCommand($textBox);
    }

    /**
     * Renders a localized TextBox with random content to demonstrate AbstractBox::USE_MASTER_LOCALE_HEIGHT
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     */
    private function renderFixedHeightText(int $topPos): void
    {
        $textBox = new TextBox(
            ExampleTemplate::ELEMENT_HEADLINE,
            ExampleTemplate::PAGE_MARGIN_LEFT + 70 + 10,
            $topPos,
            70,
            26
        );

        $textBox->addString(
            $this->getFaker()
                 ->words(10, true)
        );
        $textBox->setBoxIdent('fixedHeightText');

        //We define a localized TextBox
        $textBox->setLocalized();

        //Option AbstractBox::USE_MASTER_LOCALE_HEIGHT:
        //Uses the master locale's position (left, top) and height.
        //Takes width and fit from the command.
        $textBox->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_HEIGHT);

        $this->addCommand($textBox);
    }

    /**
     * Renders a localized TextBox with random content to demonstrate AbstractBox::USE_MASTER_LOCALE_NONE
     *
     * @return void
     * @throws \Exception
     */
    private function renderDontUseMasterLocaleText(): void
    {
        $textBox = new TextBox(ExampleTemplate::ELEMENT_HEADLINE, 110, 250, 70, 20);

        $textBox->addString(
            $this->getFaker()
                 ->words(4, true)
        );
        $textBox->setBoxIdent('noMasterLocaleSize');

        //We define a localized TextBox
        $textBox->setLocalized();

        //Option AbstractBox::USE_MASTER_LOCALE_NONE:
        //Uses position (left, top) and dimensions (width, height) from the command.
        //Does not use any values from the master locale.
        $textBox->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_NONE);

        $this->addCommand($textBox);
    }

    /**
     * Demonstrates the useMasterLocaleDimension mode AbstractBox::USE_MASTER_LOCALE_ALL for CopyBox
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     */
    private function renderCopyBox(int $topPos): void
    {
        $copyBox = new CopyBox(
            ExampleTemplate::ELEMENT_COPYBOX,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            $topPos,
            10,
            10
        );

        $copyBox->setBoxIdent('demoCopyBox')
                ->setLocalized()
                ->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_ALL);

        $this->addCommand($copyBox);
    }

    /**
     * Demonstrates the `useMasterLocaleDimension` mode with `AbstractBox::USE_MASTER_LOCALE_POSITION` for `ImageBox`.
     *
     * @param int $topPos
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     */
    private function renderImageBox(int $topPos): void
    {
        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBox(
            ExampleTemplate::ELEMENT_IMAGE,
            ExampleTemplate::PAGE_MARGIN_LEFT + 20,
            $topPos,
            60,
            45,
            $asset
        );

        $imageBox->setBoxIdent('demoImageBox')
                 ->setLocalized()
                 ->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_POSITION);

        $this->addCommand($imageBox);
    }

    /**
     * Demonstrates the `useMasterLocaleDimension` mode `AbstractBox::USE_MASTER_LOCALE_POSITION` for `TableBox`
     *
     * @param int $topPos
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     */
    private function renderTableBox(int $topPos): void
    {
        $tableBox = new Table(
            ExampleTemplate::ELEMENT_TABLE,
            ExampleTemplate::PAGE_MARGIN_LEFT + 20 + 60 + 20,
            $topPos,
            80,
            20
        );
        $tableBox->setFit(Table::FIT_FRAME_TO_CONTENT)
                 ->setRowHeight(5);

        $tableBox->addColumn(40, null, ExampleTemplate::STYLE_TABLE_CELL)
                 ->addColumn(40, null, ExampleTemplate::STYLE_TABLE_CELL);


        for ($i = 0; $i <= 2; $i++) {
            $tableBox->startRow();
            $tableBox->addCell($this->getFaker()->words(2, true))
                     ->addCell($this->getFaker()->words(2, true));
        }

        $asset = $this->loadRandomAsset('/Car Images/%');
        $image = new ImageBox('image');
        $image->setAsset($asset)
              ->setFit(ImageBox::FIT_CONTENT_TO_FRAME)
              ->setWidth(10)
              ->setHeight(10);
        $image->setLocalized();

        $paragraph = new Paragraph();
        $paragraph->addComponent($image);

        $tableBox->startRow();
        $tableBox->addCell($paragraph);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $image = new ImageBox('image');
        $image->setAsset($asset)
              ->setFit(ImageBox::FIT_CONTENT_TO_FRAME)
              ->setWidth(10)
              ->setHeight(10);
        $image->setLocalized();

        $paragraph = new Paragraph();
        $paragraph->addComponent($image);
        $tableBox->addCell($paragraph);

        $tableBox->setBoxIdent('demoTableBox')
                 ->setLocalized()
                 ->setUseMasterLocaleDimension(AbstractBox::USE_MASTER_LOCALE_POSITION);

        $this->addCommand($tableBox);
    }

    /**
     * Renders usage description text of demo page
     *
     * @return void
     * @throws \Exception
     */
    private function renderDemoDescription(): void
    {
        $this->addCommand(new SetLayer('Demo description'));

        $message = 'This page demonstrates how to localize documents. ';
        $message .= 'The document contains both localized and non-localized content. ';
        $message .= 'You can manually adjust the positions of all localized car elements in the master language. ';
        $message .= 'The adjusted positions are applied when generating language variants.';

        $textBox = new TextBox(
            ExampleTemplate::ELEMENT_TEXTBOX,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            22,
            ExampleTemplate::CONTENT_WIDTH,
            100,
            TextBox::FIT_FRAME_TO_CONTENT
        );

        $textBox->addParagraph(new Paragraph($message, ExampleTemplate::STYLE_PARAGRAPH_COPYTEXT));
        $textBox->setBoxIdent('demoDescriptionTop');
        $this->addCommand($textBox);

        $message = 'Elements below demonstrate the useMasterLocaleDimension modes for different elements.';

        $textBox = new TextBox(
            ExampleTemplate::ELEMENT_TEXTBOX,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            160,
            ExampleTemplate::CONTENT_WIDTH,
            100,
            TextBox::FIT_FRAME_TO_CONTENT_HEIGHT
        );

        $textBox->addParagraph(new Paragraph($message, ExampleTemplate::STYLE_PARAGRAPH_COPYTEXT));
        $textBox->setBoxIdent('demoDescriptionBottom');
        $this->addCommand($textBox);
    }

    /**
     * Render a layout element for demo purposes
     *
     * @param int $topPos
     *
     * @return void
     * @throws \Exception
     */
    private function rendersLayoutBar(int $topPos): void
    {
        //Layout elements are rendered on the "Layout" layer.
        $this->addCommand(new SetLayer('Layout'));
        $copyBox = new CopyBox(
            ExampleTemplate::ELEMENT_COPYBOX,
            ExampleTemplate::PAGE_MARGIN_LEFT,
            $topPos,
            ExampleTemplate::CONTENT_WIDTH,
            5
        );

        //Manually reset the boxIdent
        $this->setBoxIdentReference('');
        $copyBox->setBoxIdentReferenced('layoutBar' . $topPos);

        $this->addCommand($copyBox);
    }

    /**
     * Sort the generated layers
     *
     * @return void
     * @throws \Exception
     */
    private function sortLayers(): void
    {
        $sorting = [
            'Demo description',
            'Manufacturer',
            'Car',
            '/Car :: \w+/',
            'Layout',
        ];
        $this->addCommand(new SortLayers($sorting));
    }
}
