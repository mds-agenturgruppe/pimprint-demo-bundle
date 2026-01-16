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

use App\Model\Product\Car;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupEnd;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupStart;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarList\CarListTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarList\ListRenderer;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\ChapterDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\CarProvider;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\CategoryTreeBuilder;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\ManufacturerTreeBuilder;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class CarList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarDemo
 */
class CarList extends AbstractCarsDemoProject
{
    /**
     * CarList
     *
     * @param CategoryTreeBuilder     $categoryTreeBuilder
     * @param ManufacturerTreeBuilder $manufacturerTreeBuilder
     * @param CarProvider             $carProvider
     * @param CarListTemplate         $template
     * @param ListRenderer            $listRenderer
     */
    public function __construct(
        private readonly CategoryTreeBuilder $categoryTreeBuilder,
        private readonly ManufacturerTreeBuilder $manufacturerTreeBuilder,
        private readonly CarProvider $carProvider,
        private readonly CarListTemplate $template,
        private readonly ListRenderer $listRenderer,
    ) {
    }

    /**
     * Selectable for rendering are Category DataObjects from the path:
     *  /Product Data/Categories/products/cars
     * or Manufacturers DataObjects
     *
     * @return array
     *
     */
    public function getPublicationsTree(): array
    {
        return [
            $this->categoryTreeBuilder->getPublicationsTree('/Product Data/Categories/products/cars'),
            $this->manufacturerTreeBuilder->getPublicationsTree(),
        ];
    }

    /**
     * Generates InDesign Commands to render the selected publication in InDesign.
     *
     * @return void
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
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     */
    protected function setupContent(): void
    {
        $this->setupCategory();
        $this->setupManufacturer();

        if (!$this->manufacturer && !$this->category) {
            throw new \Exception('Please select an Category or Manufacturer.');
        }

        $this->createChapters();
    }

    /**
     * Create the chapters data for the selected category or manufacturer
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
     * Create the chapter data for $category and all its child categories
     *
     * @param Category $category
     *
     * @return void
     */
    private function createCategoryChapters(Category $category): void
    {
        $carIds = $this->carProvider->loadIdsByCategory($category, Car::OBJECT_TYPE_ACTUAL_CAR);
        if (!empty($carIds)) {
            $this->chapters[] = ChapterDto::fromDataObject($category, $carIds);
        }

        foreach ($category->getChildren() as $child) {
            if (!$child instanceof Category) {
                continue;
            }
            $this->createCategoryChapters($child);
        }
    }

    /**
     * Create chapter data for $manufacturer
     *
     * @param Manufacturer $manufacturer
     *
     * @return void
     */
    private function createManufacturerChapter(Manufacturer $manufacturer): void
    {
        $carIds = $this->carProvider->loadIdsByManufacturer($manufacturer, Car::OBJECT_TYPE_ACTUAL_CAR);
        if (empty($carIds)) {
            return;
        }

        $this->chapters[] = ChapterDto::fromDataObject($manufacturer, $carIds);
    }

    /**
     * Create the InDesign commands to render a single chapter
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

        $this->setBoxIdentReference($chapter->referenceId);
        $this->setChapterLayout($chapter, $this->template);

        $this->renderChapterHeadline($chapter->name);

        // Number of elements per row and number of rows per page.
        $elementsInRow = 3;
        $rowsOnPage = 3;

        $marginLeft = 0;
        $marginTop = CarListTemplate::TOP_MARGIN;
        $counter = 0;

        foreach ($chapter->objectIds as $carId) {
            $car = Car::getById($carId);
            if (!$car instanceof Car) {
                continue;
            }

            // Append "Box ident reference" for content-aware updates.
            $this->appendToBoxIdentReference($carId);

            // Start a new group.
            $this->addCommand(new GroupStart());

            // Render all elements for the car list item.
            $this->addCommands($this->listRenderer->render($car));

            $groupEnd = new GroupEnd();
            $groupEnd->setMoveTo(true)
                     ->setLeftRelative(Variable::VARIABLE_X_POSITION, $marginLeft)
                     ->setTopRelative(Variable::VARIABLE_Y_POSITION, $marginTop)
                     ->setVariable(Variable::VARIABLE_X_POSITION, Variable::POSITION_RIGHT);

            $marginLeft = AbstractCarsDemoTemplate::BOX_MARGIN;

            // Display three elements per row.
            if (++$counter % $elementsInRow === 0) {
                // Create a line break by updating the xPos and yPos variables.
                // Register the bottom position as the new yPos variable.
                $groupEnd->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);
                $this->addCommand($groupEnd);

                // Reset the xPos variable to the left margin.
                $this->addCommand(
                    new Variable(Variable::VARIABLE_X_POSITION, AbstractCarsDemoTemplate::CONTENT_ORIGIN_LEFT)
                );

                $marginLeft = 0;
                $marginTop = CarListTemplate::ROW_MARGIN;
            } else {
                $this->addCommand($groupEnd);
            }

            // After three rows (9 elements per page), add a page break. Not for the last car.
            if (($counter % ($elementsInRow * $rowsOnPage) === 0) && ($counter !== count($chapter->objectIds))) {
                // Create a new page.
                $this->addCommand(new NextPage());

                // Reset the yPos variable.
                $this->addCommand(
                    new Variable(Variable::VARIABLE_Y_POSITION, CarListTemplate::LINE_TOP)
                );

                $marginTop = CarListTemplate::TOP_MARGIN;
            }
        }
    }
}
