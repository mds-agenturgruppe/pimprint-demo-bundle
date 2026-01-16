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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarList;

use App\Model\Product\Car;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto\CarInformationDto;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper\AbstractHelper;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\AbstractCarsDemoTemplate;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Data\ImageGallery;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ListRenderer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\ElementRenderer
 */
class ListRenderer extends AbstractHelper
{
    /**
     * Variable name to store the bottom position of the image box
     *
     * @var string
     */
    const VARIABLE_IMAGE_BOTTOM = 'imageBottom';

    /**
     * Variable name to store the bottom position of the text box
     *
     * @var string
     */
    const VARIABLE_TEXTBOX_BOTTOM = 'textBoxBottom';

    /**
     * Car object
     *
     * @var Car
     */
    private Car $car;

    /**
     * Car information
     *
     * @var CarInformationDto
     */
    private CarInformationDto $carInformation;

    /**
     * Generate commands to render $car as a list element
     *
     * @param Car $car
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     */
    public function render(Car $car): array
    {
        $this->car = $car;
        $this->carInformation = new CarInformationDto($car);

        return [
            $this->getImageBox(),
            $this->getNameTextBox(),
            $this->getInformationTextBox(),
            $this->getPriceTextBox()
        ];
    }

    /**
     * Create `ImageBox` for car image
     *
     * @return ImageBox|null
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getImageBox(): ?ImageBox
    {
        $gallery = $this->car->getGallery();
        if (!$gallery instanceof ImageGallery) {
            return null;
        }

        $hotspotImage = current($gallery->getItems());
        if (!$hotspotImage instanceof Hotspotimage) {
            return null;
        }
        $asset = $hotspotImage->getImage();
        if (!$asset instanceof Image) {
            return null;
        }

        $imageBox = new ImageBox(
            AbstractCarsDemoTemplate::ELEMENT_IMAGE,
            0,
            0,
            AbstractCarsDemoTemplate::BOX_WIDTH,
            CarListTemplate::IMAGE_BOX_HEIGHT,
            $asset,
            ImageBox::FIT_FILL_PROPORTIONALLY
        );
        $imageBox->setVariable(self::VARIABLE_IMAGE_BOTTOM, Variable::POSITION_BOTTOM);
        $imageBox->setBoxIdentReferenced('image');

        return $imageBox;
    }

    /**
     * Create `TextBox` for car name
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getNameTextBox(): TextBox
    {
        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_SUBHEADING);
        $text->addString((string)$this->car->getName());

        $textBox = $this->boxGenerator()
                        ->getTextBoxTopRelative(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            0,
                            self::VARIABLE_IMAGE_BOTTOM,
                            AbstractCarsDemoTemplate::BOX_MARGIN_SMALL
                        );
        $textBox->setVariable(self::VARIABLE_TEXTBOX_BOTTOM, Variable::POSITION_BOTTOM);
        $textBox->setBoxIdentReferenced('name');
        $textBox->addText($text);

        return $textBox;
    }

    /**
     * Create `TexBox` with car information
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getInformationTextBox(): TextBox
    {
        $parts = array_filter(
            [
                $this->carInformation->bodyStyle,
                $this->carInformation->productionYear,
                $this->carInformation->power,
            ]
        );

        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_COPY_SMALL);
        $text->addString(implode(' | ', $parts));

        $textBox = $this->boxGenerator()
                        ->getTextBoxTopRelative(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            0,
                            self::VARIABLE_TEXTBOX_BOTTOM,
                            AbstractCarsDemoTemplate::BOX_MARGIN_SMALL,
                            AbstractCarsDemoTemplate::BOX_WIDTH,
                            CarListTemplate::TEXT_BOX_HEIGHT,
                            TextBox::FIT_NO_ADJUST
                        );
        $textBox->setVariable(self::VARIABLE_TEXTBOX_BOTTOM, Variable::POSITION_BOTTOM);
        $textBox->setBoxIdentReferenced('info');
        $textBox->addText($text);

        return $textBox;
    }

    /**
     * Create `TextBox` with price
     *
     * @return TextBox
     * @throws ContainerExceptionInterface
     * @throws FilesystemException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function getPriceTextBox(): TextBox
    {
        $text = new Text(AbstractCarsDemoTemplate::STYLE_PARAGRAPH_PRICE);
        $text->addString($this->carInformation->price);

        $textBox = $this->boxGenerator()
                        ->getTextBoxTopRelative(
                            AbstractCarsDemoTemplate::ELEMENT_TEXTBOX,
                            0,
                            self::VARIABLE_TEXTBOX_BOTTOM,
                            AbstractCarsDemoTemplate::BOX_MARGIN_SMALL,
                        );
        $textBox->setBoxIdentReferenced('price');
        $textBox->addText($text);

        return $textBox;
    }
}
