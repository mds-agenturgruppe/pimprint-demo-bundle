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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo;

use App\Model\Product\Car;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentTemplateSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\Project\RenderingProject;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\ChapterDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\PageLayoutInterface;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;
use Pimcore\Model\WebsiteSetting;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class AbstractRenderingProject
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package src\Project\CarsDemo
 */
abstract class AbstractCarsDemoProject extends RenderingProject
{
    /**
     * Pimcore Website-Setting to overwrite the used template using a Pimcore asset.
     *
     * @var string
     */
    const WEBSITE_SETTING_TEMPLATE_OVERWRITE = 'pimPrint_carsDemo_template';

    /**
     * Pimcore property name to overwrite the used template using a Pimcore asset.
     *
     * @var string
     */
    const PROPERTY_TEMPLATE_OVERWRITE = 'pimPrint_carsDemo_template';

    /**
     * Chapters to render
     *
     * @var ChapterDto[]
     */
    protected array $chapters = [];

    /**
     * Manufacturer object
     *
     * @var Manufacturer|null
     */
    protected ?Manufacturer $manufacturer = null;

    /**
     * Category object
     *
     * @var Category|null
     */
    protected ?Category $category = null;

    /**
     * Car object
     *
     * @var Car|null
     */
    protected ?Car $car = null;

    /**
     * Variable to check whether a chapter has already been rendered
     *
     * @var bool
     */
    private bool $chapterRendered = false;

    /**
     * Sets up the content and prepares it for rendering.
     *
     * @return void
     */
    abstract protected function setupContent(): void;

    /**
     * Returns an InDesign template filename or a Pimcore `Asset` object.
     *
     * Shows example to override the template at runtime.
     *
     * @return Asset|string
     * @throws \Exception
     */
    protected function getTemplate(): Asset|string
    {
        $asset = $this->getObjectTemplateOverwrite();
        if ($asset) {
            // If the rendered DataObject overrides the template, use it first.
            return $asset;
        }

        $asset = $this->getGlobalTemplateOverwrite();
        if ($asset) {
            // Otherwise, use the template overridden via the website setting.
            return $asset;
        }

        // Use the project’s default template from the Symfony config.
        return parent::getTemplate();
    }

    /**
     * Loads the Pimcore `Asset` used as the InDesign template file from the website setting.
     * This override applies to every rendering when an asset is set in the website setting.
     *
     * @return ?Asset
     * @throws \Exception
     */
    private function getGlobalTemplateOverwrite(): ?Asset
    {
        $setting = WebsiteSetting::getByName(static::WEBSITE_SETTING_TEMPLATE_OVERWRITE, null);
        if ($setting instanceof WebsiteSetting) {
            $asset = $setting->getData();
            if ($asset instanceof Asset) {
                return $asset;
            }
        }

        return null;
    }

    /**
     * Example for overriding or dynamically selecting the InDesign template based on the rendered content.
     *
     * In this example, you can change the InDesign template file in the Pimcore Admin Panel
     * for all CarsDemo projects that render content by a category.
     * You can also override the template by adding a Pimcore property to the category DataObject.
     *
     * @return ?Asset
     */
    private function getObjectTemplateOverwrite(): ?Asset
    {
        if (!$this->category) {
            return null;
        }

        $asset = $this->category->getProperty(static::PROPERTY_TEMPLATE_OVERWRITE);
        if ($asset instanceof Asset) {
            return $asset;
        }

        return null;
    }

    /**
     * Loads the `Category` from the request sent by the PimPrint InDesign plugin.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setupCategory(): void
    {
        $category = Category::getById(
            $this->pluginParams()
                 ->get(PluginParameters::PARAM_PUBLICATION)
        );
        if ($category instanceof Category && $category->isPublished()) {
            $this->category = $category;
        }
    }

    /**
     * Loads the `Manufacturer` from the request sent by the PimPrint InDesign plugin.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setupManufacturer(): void
    {
        $manufacturer = Manufacturer::getById(
            $this->pluginParams()
                 ->get(PluginParameters::PARAM_PUBLICATION)
        );
        if ($manufacturer instanceof Manufacturer && $manufacturer->isPublished()) {
            $this->manufacturer = $manufacturer;
        }
    }

    /**
     * Loads the `Car` from the request sent by the PimPrint InDesign plugin.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setupCar(): void
    {
        $car = Car::getById(
            $this->pluginParams()
                 ->get(PluginParameters::PARAM_PUBLICATION)
        );
        if ($car instanceof Car && $car->isPublished()) {
            $this->car = $car;
        }
    }

    /**
     * You can adjust document settings for the currently open InDesign document where generation runs.
     *
     * @return void
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setDocumentProperties(): void
    {
        // Adds 100 pages to the document.
        $this->addCommand(new DocumentSetup(numberOfPages: 100));

        // Transfers all document settings (dimensions, margins, and bleeds) from the InDesign template file.
        $this->addCommand(new DocumentTemplateSetup(facingPages: false));
    }

    /**
     * Initializes variables for dynamic positioning.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function registerVariables(): void
    {
        // Initializes xPos.
        $this->addCommand(new Variable(Variable::VARIABLE_X_POSITION, AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT));

        // Initializes yPos.
        $this->addCommand(
            new Variable(
                Variable::VARIABLE_Y_POSITION,
                AbstractCarsDemoTemplate::CONTENT_ORIGIN_TOP - AbstractCarsDemoTemplate::BOX_MARGIN
            )
        );
    }

    /**
     * Handles the base page layout and pagination logic for a chapter.
     *
     * - Adds the InDesign commands that create page layout elements.
     * - Creates a page break, because each chapter starts on a new page.
     *
     * @param ChapterDto          $chapter
     * @param PageLayoutInterface $template
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setChapterLayout(ChapterDto $chapter, PageLayoutInterface $template): void
    {
        $this->addCommand($template->getPageLayoutCommands($chapter->name));

        if ($this->chapterRendered) {
            // A chapter is already rendered, so we need to create a page break.
            $this->addCommand(new NextPage());

            return;
        }

        // Open the first page after registering the template commands to execute them immediately.
        $this->addCommand(new GoToPage(1));
        $this->chapterRendered = true;
    }

    /**
     * create the chapter headline
     *
     * @param string|null $chapterName
     *
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function renderChapterHeadline(?string $chapterName): void
    {
        if ($chapterName === null) {
            return;
        }

        // Creates a TextBox for the headline.
        $textBox = new TextBox(
            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
            AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT,
            AbstractCarsDemoTemplate::PAGE_MARGIN_TOP,
            AbstractCarsDemoTemplate::CONTENT_WIDTH,
            100, // Use an arbitrary height. FIT_FRAME_TO_CONTENT resizes the box automatically.
            TextBox::FIT_FRAME_TO_CONTENT
        );

        // Append a unique ident for "Box ident reference" content-aware updates.
        $textBox->setBoxIdentReferenced('headline');

        // Creates the text content with a paragraph style.
        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_TITLE_PAGE);
        $text->addString($chapterName);
        $textBox->addText($text);

        $this->addCommand($textBox);
    }
}
