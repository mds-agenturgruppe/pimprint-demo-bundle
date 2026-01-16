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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo;

use League\Flysystem\FilesystemException;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\AccessoryPriceListTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\CustomField\SecondLanguageSelect;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AccessoryPriceList\PriceListRenderer;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\ChapterDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\CategoryTreeBuilder;
use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\AccessoryPart\Listing;
use Pimcore\Model\DataObject\Category;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class AccessoryPriceList
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo
 */
class AccessoryPriceList extends AbstractCarsDemoProject
{
    /**
     * AccessoryPriceList
     *
     * @param CategoryTreeBuilder        $treeBuilder
     * @param SecondLanguageSelect       $secondLanguageSelect
     * @param AccessoryPriceListTemplate $template
     * @param PriceListRenderer          $priceListRenderer
     */
    public function __construct(
        private readonly CategoryTreeBuilder $treeBuilder,
        private readonly SecondLanguageSelect $secondLanguageSelect,
        private readonly AccessoryPriceListTemplate $template,
        private readonly PriceListRenderer $priceListRenderer,
    ) {
    }

    /**
     * Add the custom select field "SecondLanguageSelect" to the InDesign Plugin
     *
     * @return void
     * @throws \Exception
     */
    protected function initCustomFormFields(): void
    {
        $this->addCustomFormField($this->secondLanguageSelect);
    }

    /**
     * Selectable for rendering are Category DataObjects from the path:
     * /Product Data/Categories/products/spare parts
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return [$this->treeBuilder->getPublicationsTree('/Product Data/Categories/products/spare parts')];
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
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setupContent(): void
    {
        $this->setupCategory();
        $this->createChapters($this->category);

        //The second language is selected in the custom form field
        $this->priceListRenderer->setSecondLanguage(
            $this->pluginParams()->getCustomField($this->secondLanguageSelect->getParam())
        );
    }

    /**
     * Create the chapter data for $category and all its child categories.
     *
     * @param Category $category
     *
     * @return void
     */
    private function createChapters(Category $category): void
    {
        $listing = new Listing();
        $listing->filterByMainCategory($category);

        $this->chapters[] = ChapterDto::fromDataObject($category, $listing->loadIdList());

        foreach ($category->getChildren() as $child) {
            if ($child instanceof Category) {
                $this->createChapters($child);
            }
        }
    }

    /**
     * Create the InDesign commands to render the accessory parts tables
     *
     * @param ChapterDto $chapter
     *
     * @return void
     * @throws FilesystemException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function renderChapter(ChapterDto $chapter): void
    {
        // Skip chapters without content.
        if ($chapter->objectIds == []) {
            return;
        }

        $this->setBoxIdentReference($chapter->referenceId);
        $this->setChapterLayout($chapter, $this->template);

        $this->renderChapterHeadline($chapter->name);

        $table = $this->priceListRenderer->createTable();

        foreach ($chapter->objectIds as $accessoryId) {
            $accessoryPart = AccessoryPart::getById($accessoryId);
            if (!$accessoryPart instanceof AccessoryPart) {
                continue;
            }

            $table->startRow();
            foreach ($this->priceListRenderer->createRowElement($accessoryPart) as $content) {
                $table->addCell(
                    content: $content ?? '',
                    style:   AccessoryPriceListTemplate::STYLE_TABLE_CELL_LEFT
                );
            }
        }

        //The table splits over multiple pages if necessary
        $splitTable = $this->priceListRenderer->createSplitTable();
        $splitTable->setTable($table);
        $table->setBoxIdentReferenced('table');

        $this->addCommand($splitTable);
    }
}
