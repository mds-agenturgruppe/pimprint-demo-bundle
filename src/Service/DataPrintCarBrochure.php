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

namespace Mds\PimPrint\DemoBundle\Service;

use AppBundle\Model\Product\Car as CarProduct;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractCarProject;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\CarBrochureRenderTrait;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class DataPrintCarsBrochure
 *
 * @package Mds\PimPrint\DemoBundle\Service
 */
class DataPrintCarBrochure extends AbstractCarProject
{
    use CarBrochureRenderTrait;

    /**
     * Renders Category headline and adds Category layout elements.
     *
     * @param Category $category
     *
     * @throws \Exception
     */
    protected function renderCategory(Category $category)
    {
        $label = $category->getName();

        //Create the page layout for the category.
        $this->renderPageLayout($label);

        //A category always starts on a new page, because we have the category name on the page layout.
        $this->addCommand(new NextPage());
        $this->renderTitle($label);
        foreach ($this->loadCarsForCategory($category, CarProduct::OBJECT_TYPE_VIRTUAL_CAR) as $car) {
            $this->renderVirtualCar($car);
        }
    }

    /**
     * Renders Manufacturer headline and adds Manufacturer layout elements.
     *
     * @param Manufacturer $manufacturer
     *
     * @throws \Exception
     */
    protected function renderManufacturer(Manufacturer $manufacturer)
    {
        //Set boxIdentReference for content aware updates.
        $this->setBoxIdentReference($manufacturer->getId());

        $label = $manufacturer->getName();
        $logo = $manufacturer->getLogo();

        //Create the page layout for the category.
        $this->renderPageLayout($label, $logo);

        //A manufacturer always starts on a new page, because we have the category name on the page layout.
        $this->addCommand(new NextPage());
        $this->renderTitle($label);
        foreach ($this->loadCarsForManufacturer($manufacturer, CarProduct::OBJECT_TYPE_VIRTUAL_CAR) as $car) {
            $this->renderVirtualCar($car);
        }
    }
}
