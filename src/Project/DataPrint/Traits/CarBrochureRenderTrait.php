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

namespace Mds\PimPrint\DemoBundle\Project\DataPrint\Traits;

use AppBundle\Model\Product\Car as CarProduct;
use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupEnd;
use Mds\PimPrint\CoreBundle\InDesign\Command\GroupStart;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variables\MaxValue;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\DemoBundle\Project\DataPrint\BrochureTemplate;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Car;
use Pimcore\Model\DataObject\Data\Hotspotimage;

/**
 * Trait CarBrochureRenderTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait CarBrochureRenderTrait
{
    /**
     * Renders $car and all color variants.
     *
     * @param Car $car
     *
     * @throws \Exception
     */
    private function renderVirtualCar(Car $car)
    {
        //We add the car itself to the InDesign element group, to have no automatic page break between car and
        //the first two variants.
        $this->addCommand(new GroupStart());

        $car = CarProduct::getById($car->getId());
        //Appends to boxIdentReference for content aware updates.
        $this->appendToBoxIdentReference($car->getId());
        $this->renderSubTitle($car->getOSName(), 'groupYPos');
        $this->renderCarDescription($car);
        $this->renderCarVariantsColumned($this->loadVariantsForCar($car));

//        Add CarBrochureTemplate::HEADLINE_LINE_Y_SPACE spacing to registered yPos variable
        $this->addCommand(
            new Variable(
                Variable::VARIABLE_Y_POSITION,
                '=[yPos] + ' . BrochureTemplate::HEADLINE_LINE_Y_SPACE
            )
        );
    }

    /**
     * Renders a description block for $car if a description is available.
     *
     * @param CarProduct $car
     *
     * @throws \Exception
     */
    protected function renderCarDescription(CarProduct $car)
    {
        $content = $car->getDescription();
        if (empty($content)) {
            return;
        }
        $asset = $this->getMainImageForCar($car);
        $this->addCommand(new Variable('logoBottom', 0));
        if ($asset instanceof Image) {
            $logo = new ImageBox(BrochureTemplate::ELEMENT_IMAGE_FLOW, BrochureTemplate::CONTENT_ORIGIN_LEFT);
            try {
                $logo->setAsset($asset);
            } catch (\Exception $e) {
                //For SVG logos we force a thumbnail.
                $logo->setAsset($asset, 'product_detail');
            }
            $logo->setAsset($asset, 'product_detail')
                 ->setWidth(BrochureTemplate::CAR_DETAIL_LOGO_WIDTH)
                 ->setHeight(BrochureTemplate::CAR_DETAIL_LOGO_HEIGHT)
                 ->setFit(ImageBox::FIT_PROPORTIONALLY)
                 ->setTopRelative(
                     'groupYPos',
                     BrochureTemplate::ELEMENT_Y_SPACE
                 )
                 ->setVariable('logoBottom', Variable::POSITION_BOTTOM)
                 ->setBoxIdentReferenced('image');
            $this->addCommand($logo);
        }

        $text = new Text(BrochureTemplate::STYLE_PARAGRAPH_COPYTEXT);
        $text->addHtml($content);
        $description = new TextBox(BrochureTemplate::ELEMENT_COPYTEXT);
        $description->addText($text)
                    ->setHeight(150) //Creates a very large box and adjusts box to content when element is places
                    ->setWidth(BrochureTemplate::CONTENT_WIDTH)
                    ->setResize(TextBox::RESIZE_WIDTH_HEIGHT)
                    ->setFit(TextBox::FIT_FRAME_TO_CONTENT)
                    ->setLeft(BrochureTemplate::CONTENT_ORIGIN_LEFT)
                    ->setTopRelative(
                        'groupYPos',
                        BrochureTemplate::ELEMENT_Y_SPACE
                    )
                    ->setVariable('textBottom', Variable::POSITION_BOTTOM)
                    ->setBoxIdentReferenced('description');
        $this->addCommand($description);

//        We define the max value of logoBottom and textBottom as new groupYPos value.
        $this->addCommand(new MaxValue('groupYPos', ['logoBottom', 'textBottom']));
    }

    /**
     * Renders variant $cars in two columned brochure layout.
     *
     * @param array $cars
     *
     * @throws \Exception
     */
    private function renderCarVariantsColumned(array $cars)
    {
        $first = true;
        $rightColumn = false;
        $counter = 0;

        //Groups can be positioned when adding the GroupEnd command.
        $groupEnd = new GroupEnd($this->createCheckNewPage(), true);
        $groupEnd->setTopRelative(Variable::VARIABLE_Y_POSITION, BrochureTemplate::BLOCK_Y_SPACE)
                 ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM)
                 ->setLeft(BrochureTemplate::PAGE_MARGIN_LEFT);

        foreach ($cars as $car) {
            $rightColumn = 0 === ++$counter % 2;
            if (false === $first && false === $rightColumn) {
                //We start a new group when we in the second left column
                $this->addCommand(new GroupStart());
            }
            $first = false;
            $this->renderCarVariantColumn($car, $rightColumn);
            if (true === $rightColumn) {
                $this->addCommand($groupEnd);
            }
        }
        if (false === $rightColumn) {
            $this->addCommand($groupEnd);
        }
    }

    /**
     * Renders $car variant in brochure layout.
     *
     * @param Car  $car
     * @param bool $rightColumn
     *
     * @throws \Exception
     */
    private function renderCarVariantColumn(Car $car, bool $rightColumn)
    {
        $leftPos = BrochureTemplate::CONTENT_ORIGIN_LEFT;
        if (true === $rightColumn) {
            $leftPos = BrochureTemplate::COLUMN_RIGHT_ORIGIN_LEFT;
        }
        //Appends boxIdentReference for content aware updates.
        $this->appendToBoxIdentReference($car->getId());

        $headline = new TextBox(BrochureTemplate::ELEMENT_HEADLINE);
        $headline->addString($this->getVariantHeadline($car))
                 ->setWidth(BrochureTemplate::COLUMN_WIDTH)
                 ->setTopRelative('groupYPos', BrochureTemplate::ELEMENT_Y_SPACE)
                 ->setLeft($leftPos)
                 ->setResize(AbstractBox::RESIZE_WIDTH)
                 ->setVariable('colYPos', Variable::POSITION_BOTTOM)
                 ->setBoxIdentReferenced('headline');
        $this->addCommand($headline);

        $line = new CopyBox(BrochureTemplate::ELEMENT_STYLE_BOX);
        $line->setWidth(BrochureTemplate::COLUMN_WIDTH)
             ->setTop(0)
             ->setLeft($leftPos)
             ->setResize(AbstractBox::RESIZE_WIDTH_HEIGHT)
             ->setTopRelative('colYPos', BrochureTemplate::HEADLINE_LINE_Y_SPACE)
             ->setVariable('colYPos', Variable::POSITION_BOTTOM)
             ->setBoxIdentReferenced('styleBox');

        $this->addCommand($line);

        $description = new TextBox(BrochureTemplate::ELEMENT_COPYTEXT);
        $description->addString($this->getVariantDescription($car))
                    ->setWidth(BrochureTemplate::COLUMN_WIDTH)
                    ->setHeight(50)
                    ->setFit(TextBox::FIT_FRAME_TO_CONTENT_HEIGHT)
                    ->setTopRelative('colYPos', BrochureTemplate::HEADLINE_LINE_Y_SPACE)
                    ->setLeft($leftPos)
                    ->setVariable('colYPos', Variable::POSITION_BOTTOM)
                    ->setBoxIdentReferenced('description');
        $this->addCommand($description);

        $asset = $car->getMainImage();
        if ($asset instanceof Hotspotimage) {
            $asset = $asset->getImage();
        }
        if (true === $asset instanceof Asset) {
            $image = new ImageBox(BrochureTemplate::ELEMENT_IMAGE_ROUNDED);
            $image->setAsset($asset)
                  ->setFit(ImageBox::FIT_FILL_PROPORTIONALLY)
                  ->setWidth(BrochureTemplate::CAR_VARIANT_IMAGE_WIDTH)
                  ->setHeight(BrochureTemplate::CAR_VARIANT_IMAGE_HEIGHT)
                  ->setLeft($leftPos)
                  ->setTopRelative('colYPos', BrochureTemplate::HEADLINE_LINE_Y_SPACE)
                  ->setVariable('assetTop', Variable::POSITION_TOP)
                  ->setVariable('assetRight', Variable::POSITION_RIGHT)
                  ->setBoxIdentReferenced('image');
            $this->addCommand($image);
        }

        $content = $this->getVariantSalesInformation($car);
        $text = new Text();
        $text->addHtml($content);
        $textBox = new TextBox(BrochureTemplate::ELEMENT_COPYTEXT_BOTTOM);
        $textBox->addText($text)
                ->setTopRelative('assetTop')
                ->setLeftRelative('assetRight', BrochureTemplate::HEADLINE_LINE_Y_SPACE)
                ->setWidth(BrochureTemplate::CAR_VARIANT_SALES_TEXT_WIDTH)
                ->setHeight(BrochureTemplate::CAR_VARIANT_IMAGE_HEIGHT)
                ->setBoxIdentReferenced('salesInfo');
        $this->addCommand($textBox);
    }
}
