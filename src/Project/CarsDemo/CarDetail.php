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

use App\Model\Product\Car as CarProduct;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarDetail\CarDetailTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarDetail\DetailRenderer;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\ChapterDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\CarProvider;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\CategoryTreeBuilder;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\ManufacturerTreeBuilder;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class CarDetail
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\src\Project\CarsDemo
 */
class CarDetail extends AbstractCarsDemoProject
{
    /**
     * CarDetail constructor
     *
     * @param ManufacturerTreeBuilder $manufacturerTreeBuilder
     * @param CategoryTreeBuilder     $categoryTreeBuilder
     * @param CarProvider             $carProvider
     * @param CarDetailTemplate       $template
     * @param DetailRenderer          $detailRenderer
     */
    public function __construct(
        private readonly ManufacturerTreeBuilder $manufacturerTreeBuilder,
        private readonly CategoryTreeBuilder $categoryTreeBuilder,
        private readonly CarProvider $carProvider,
        private readonly CarDetailTemplate $template,
        private readonly DetailRenderer $detailRenderer,
    ) {
    }

    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
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
     * {@inheritDoc}
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function setupContent(): void
    {
        $this->setupCategory();
        $this->setupManufacturer();

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
     *
     * @param Category $category
     *
     * @return void
     */
    private function createCategoryChapters(Category $category): void
    {
        $carIds = $this->carProvider->loadIdsByCategory($category, CarProduct::OBJECT_TYPE_ACTUAL_CAR);

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
     * Create chapter data for $manufacturer.
     *
     * @param Manufacturer $manufacturer
     *
     * @return void
     */
    protected function createManufacturerChapter(Manufacturer $manufacturer): void
    {
        $carIds = $this->carProvider->loadIdsByManufacturer($manufacturer, CarProduct::OBJECT_TYPE_ACTUAL_CAR);
        if (empty($carIds)) {
            return;
        }

        $this->chapters[] = ChapterDto::fromDataObject($manufacturer, $carIds);
    }

    /**
     * Create the InDesign commands to render the car chapter
     *
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

        $this->setBoxIdentReference($chapter->name);
        $this->setChapterLayout($chapter, $this->template);

        foreach ($chapter->objectIds as $key => $carId) {
            $car = Car::getById($carId);
            if (!$car instanceof CarProduct) {
                continue;
            }

            if (0 !== $key) {
                //Each car starts on a new page
                $this->addCommand(new NextPage());
            }

            $this->appendToBoxIdentReference($carId);

            $this->renderChapterHeadline($car->getName());
            $this->addCommands($this->detailRenderer->render($car));
        }
    }
}
