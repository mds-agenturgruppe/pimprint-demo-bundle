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

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupEnd;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupStart;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryList\AccessoryListTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryList\ListRenderer;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\ChapterDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AccessoryPartProvider;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\CategoryTreeBuilder;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\ManufacturerTreeBuilder;
use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class AccessoryList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo
 */
class AccessoryList extends AbstractCarsDemoProject
{
    /**
     * AccessoryList
     *
     * @param ManufacturerTreeBuilder $manufacturerTreeBuilder
     * @param CategoryTreeBuilder     $categoryTreeBuilder
     * @param AccessoryPartProvider   $accessoryPartProvider
     * @param AccessoryListTemplate   $template
     * @param ListRenderer            $listRenderer
     */
    public function __construct(
        private readonly ManufacturerTreeBuilder $manufacturerTreeBuilder,
        private readonly CategoryTreeBuilder $categoryTreeBuilder,
        private readonly AccessoryPartProvider $accessoryPartProvider,
        private readonly AccessoryListTemplate $template,
        private readonly ListRenderer $listRenderer,
    ) {
    }

    /**
     * Selectable for rendering are Category DataObjects from the path:
     * /Product Data/Categories/products/spare parts
     * or Manufacturer DataObjects
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return [
            $this->categoryTreeBuilder->getPublicationsTree('/Product Data/Categories/products/spare parts'),
            $this->manufacturerTreeBuilder->getPublicationsTree(),
        ];
    }

    /**
     * Generate InDesign Commands to render the selected publication in InDesign.
     *
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function buildPublication(): void
    {
        try {
            $this->setupContent();
        } catch (\Exception $exception) {
            //Show all exception messages from the content setup process to the user in the InDesign plugin.
            $this->addPreMessage($exception->getMessage());

            return;
        }

        $this->startRendering();
        $this->setDocumentProperties();

        foreach ($this->chapters as $chapter) {
            $this->registerVariables();
            $this->renderChapter($chapter);
        }

        $this->stopRendering();
    }

    /**
     * Sets up the content and prepares it for the rendering.
     * AccessoryParts can be rendered either for a category or a manufacturer.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setupContent(): void
    {
        $this->setupCategory();
        $this->setupManufacturer();

        if (!$this->category && !$this->manufacturer) {
            //If no category or manufacturer is selected, we break here.
            //The exception message is displayed to the user in the InDesign plugin.
            throw new \Exception('Please select an category of manufacturer.');
        }

        if ($this->manufacturer) {
            //If the document is created for a manufacturer, the logo is not rendered for each accessory part.
            $this->listRenderer->setShowManufacturerLogo(false);
        }

        $this->createChapters();
    }

    /**
     * Create the chapters data for the selected category or manufacturer.
     *
     * @return void
     */
    private function createChapters(): void
    {
        switch (true) {
            case isset($this->category):
                $this->createCategoryChapters($this->category);

                return;

            case isset($this->manufacturer):
                $this->createManufacturerChapter($this->manufacturer);

                return;
        }
    }

    /**
     * Create the chapter data for $category and all its child categories.
     * Chapters consist of the respective Category name and the assigned AccessoryParts object Ids.
     *
     * @param Category $category
     *
     * @return void
     */
    private function createCategoryChapters(Category $category): void
    {
        $accessoryParts = $this->accessoryPartProvider->loadIdsByCategory($category);
        if (!empty($accessoryParts)) {
            $this->chapters[] = ChapterDto::fromDataObject($category, $accessoryParts);
        }

        foreach ($category->getChildren() as $child) {
            if (!$child instanceof Category) {
                continue;
            }
            $this->createCategoryChapters($child);
        }
    }

    /**
     * Create chapter data for $manufacturer.
     * Chapters consist of the Manufacturer name and the assigned AccessoryParts object Ids.
     *
     * @param Manufacturer $manufacturer
     *
     * @return void
     */
    private function createManufacturerChapter(Manufacturer $manufacturer): void
    {
        $accessoryParts = $this->accessoryPartProvider->loadIdsByManufacturer($manufacturer);
        if (empty($accessoryParts)) {
            return;
        }

        $this->chapters[] = ChapterDto::fromDataObject($manufacturer, $accessoryParts);
    }

    /**
     * Create the InDesign commands to render an accessory part list $chapter.
     *
     * @param ChapterDto $chapter
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function renderChapter(ChapterDto $chapter): void
    {
        // Skip chapters without content.
        if ($chapter->objectIds == []) {
            return;
        }

        //"Box ident reference" for content-aware updates is (re)set for each chapter.
        $this->setBoxIdentReference($chapter->referenceId);
        $this->setChapterLayout($chapter, $this->template);

        $this->renderChapterHeadline($chapter->name);
        $this->renderManufacturerLogo();

        //Render all accessory parts for the current chapter
        foreach ($chapter->objectIds as $accessoryId) {
            $accessory = AccessoryPart::getById($accessoryId);
            if (!$accessory instanceof AccessoryPart) {
                continue;
            }

            //append "Box ident reference" for content-aware updates.
            $this->appendToBoxIdentReference($accessoryId);

            //Start a new Group.
            $this->addCommand(new GroupStart());

            //Render all elements for an accessory part list element
            $this->addCommands($this->listRenderer->render($accessory));

            //We end the group and append the CheckNewPage command
            //This will move the accessory part element to a new page if the element exceeds the page content margins.
            $groupEnd = new GroupEnd($this->template->getCheckNewPage());

            //The group is positioned relative to the rendered group before
            $groupEnd->setMoveTo(true)
                     ->setLeftRelative(Variable::VARIABLE_X_POSITION)
                     ->setTopRelative(Variable::VARIABLE_Y_POSITION, AbstractCarsDemoTemplate::BOX_MARGIN)
                     ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);
            $this->addCommand($groupEnd);
        }
    }

    /**
     * Renders the manufacturer logo on the page header
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     */
    private function renderManufacturerLogo(): void
    {
        if ($this->manufacturer === null) {
            return;
        }

        $asset = $this->manufacturer->getLogo();
        if (!$asset) {
            return;
        }

        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE_UP_RIGHT,
            184,
            10,
            AbstractCarsDemoTemplate::MANUFACTURER_LOGO_SIZE,
            AbstractCarsDemoTemplate::MANUFACTURER_LOGO_SIZE,
            $asset
        );
        $this->addCommand($imageBox);
    }
}
