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
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel\CarSalesLabelTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel\LabelRenderer;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\ChapterDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\CarProvider;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\TreeBuilder\ManufacturerTreeBuilder;
use Pimcore\Model\DataObject\Manufacturer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class CarSalesLabel
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo
 */
class CarSalesLabel extends AbstractCarsDemoProject
{
    /**
     * Website Setting to overwrite the used template by a Pimcore asset.
     *
     * @var string
     */
    const WEBSITE_SETTING_TEMPLATE_OVERWRITE = 'pimPrint_carSalesLabel_template';

    /**
     * SalesLabel constructor
     *
     * @param ManufacturerTreeBuilder $manufacturerTreeBuilder
     * @param CarProvider             $carHelper
     * @param CarSalesLabelTemplate   $template
     * @param LabelRenderer           $labelRenderer
     */
    public function __construct(
        private readonly ManufacturerTreeBuilder $manufacturerTreeBuilder,
        private readonly CarProvider $carHelper,
        private readonly CarSalesLabelTemplate $template,
        private readonly LabelRenderer $labelRenderer,
    ) {
    }

    /**
     * Selectable for rendering are Manufacturer DataObjects or Car DataObjects
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return $this->manufacturerTreeBuilder->getPublicationsTreeWithCars();
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
     * {@inheritDoc}
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    protected function setupContent(): void
    {
        $this->setupManufacturer();
        $this->setupCar();

        if (!$this->manufacturer && !$this->car) {
            throw new \Exception('Please select an Manufacturer or Car.');
        }

        $this->createChapters();
    }

    /**
     * Create the chapters data for the selected manufacturer or car.
     *
     * @return void
     */
    private function createChapters(): void
    {
        switch (true) {
            case isset($this->manufacturer):
                $this->createManufacturerChapter($this->manufacturer);
                break;
            case isset($this->car):
                $this->createCarChapter($this->car);
        }
    }

    /**
     * Creates chapters for $manufacturer
     *
     * @param Manufacturer $manufacturer
     *
     * @return void
     */
    protected function createManufacturerChapter(Manufacturer $manufacturer): void
    {
        $carIds = $this->carHelper->loadIdsByManufacturer($manufacturer, Car::OBJECT_TYPE_ACTUAL_CAR);
        if (empty($carIds)) {
            return;
        }

        $this->chapters[] = ChapterDto::fromDataObject($manufacturer, $carIds);
    }

    /**
     * Creates chapter for a $car
     *
     * @param Car $car
     *
     * @return void
     */
    private function createCarChapter(Car $car): void
    {
        if ($car->getObjectType() === Car::OBJECT_TYPE_ACTUAL_CAR) {
            $this->chapters[] = ChapterDto::fromDataObject($car, [$car->getId()]);

            return;
        }
        foreach ($car->getChildren() as $childCar) {
            if (!$childCar instanceof Car) {
                continue;
            }
            $this->createCarChapter($childCar);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     */
    protected function setDocumentProperties(): void
    {
        // Transfers all document settings (dimensions, margins, facing page definition) from the template class.
        // Car sales label will set the document to a single-page
        $this->addCommand(new DocumentSetup($this->template));

        // Adds 50 pages to the document
        $this->addCommand(new DocumentSetup(numberOfPages: 50));
    }

    /**
     * Create the InDesign commands to render the chapter
     *
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function renderChapter(ChapterDto $chapter): void
    {
        // Skip chapters without content.
        if ($chapter->objectIds === []) {
            return;
        }

        $this->setBoxIdentReference($chapter->referenceId);
        $this->setChapterLayout($chapter, $this->template);

        foreach ($chapter->objectIds as $key => $carId) {
            $car = Car::getById($carId);
            if (!$car instanceof Car) {
                continue;
            }
            $this->appendToBoxIdentReference($carId);

            // Place all car page elements on the layer "Content".
            $this->addCommand(new SetLayer('Content'));

            if (0 !== $key) {
                // Start each car on a new page.
                $this->addCommand(new NextPage());
            }

            $this->labelRenderer->render($car);
        }
    }
}
