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

namespace Mds\PimPrint\DemoBundle\Project\CommandDemo;

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\SortLayers;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table as TableBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Mds\PimPrint\DemoBundle\Project\LocalizationDemo\ExampleTemplate;

/**
 * Demonstrates the localization of page-elements.
 * Localized elements are automatically placed on language-specific layers.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Localization extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    public function build(): void
    {
        $this->initDemo();

        $this->conceptExplanation();

        $this->notLocalizedElements();
        $this->localizedElements();
        $this->sortLayers();
    }

    /**
     * This method does not render any content into the document.
     * It only explains the localization API of page-elements.
     *
     * @return void
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\Traits\DefaultLocalizedParamsTrait
     */
    private function conceptExplanation(): void
    {
//        All commands that creates page-elements in the InDesign document (CopyBox, TextBox, Table and ImageBox)
//        can be defined as localized or not.
//        If a page element is defined as localized it is automatically placed on a layer that has the current rendered
//        locale appended to the layer name.

//        By default, all of these elements are created not localized.
        $copyBox = new CopyBox('copyBox', 10, 10);

//        Each box can be set to localized manually
        $copyBox->setLocalized(true);

//        The locale of the box is set automatically to the currently rendered locale.
//        For special the locale can be set manually
        $copyBox->setLocale('en');

//        For convenience reasons the default localization behaviour can be changed for each element separately.
//        When setDefaultLocalized is set to true, all new instances of this element are created localized.
        CopyBox::setDefaultLocalized(true);
        TextBox::setDefaultLocalized(true);
        TableBox::setDefaultLocalized(true);
        ImageBox::setDefaultLocalized(true);

//        For this demo purpose we set the default to false again.
        CopyBox::setDefaultLocalized(false);
        TextBox::setDefaultLocalized(false);
        TableBox::setDefaultLocalized(false);
        ImageBox::setDefaultLocalized(false);
    }

    /**
     * Places not localized elements into the rendered document.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function notLocalizedElements(): void
    {
//        The following page-elements will be created not localized.
//        They will be placed on the current active demo layer 'Localization Demo'.
//        Note: Layer is created in \Mds\PimPrint\DemoBundle\Project\CommandDemo\AbstractStrategy::initDemoLayer()

        $copyBox = new CopyBox('copyBox', 12.7, 12.7);
//        For demo purpose we set not localized (default behaviour)
        $copyBox->setLocalized(false);
        $this->addCommand($copyBox);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBox('image', 30, 12.7, 60, 40, $asset, ImageBox::FIT_FILL_PROPORTIONALLY);
//        For demo purpose we set not localized (default behaviour)
        $imageBox->setLocalized(false);
        $this->addCommand($imageBox);

        $textBox = new TextBox('textBox', 12.7, 60, 100, 10);
        //For demo purpose we set not localized (default behaviour)
        $textBox->setLocalized(false);
        $textBox->addString(
            'Change the rendered locale in the PlugIn and render this demo publication in different locales.'
        );
        $this->addCommand($textBox);
    }

    /**
     * Demonstrates the placement of localized page-elements.
     * Localized elements are automatically placed on language-specific layers.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function localizedElements(): void
    {
//        The following page-elements will be created not localized.
//        They will be placed on the current active demo layer 'Localization Demo :: locale'.
//        Note: Layer is created in \Mds\PimPrint\DemoBundle\Project\CommandDemo\AbstractStrategy::initDemoLayer()

        $copyBox = new CopyBox('copyBox', 12.7, 100);
        $copyBox->setLocalized();
        $this->addCommand($copyBox);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBox('image', 30, 100, 60, 40, $asset, ImageBox::FIT_FILL_PROPORTIONALLY);
        $imageBox->setLocalized();
        $this->addCommand($imageBox);

        //TextBox will be rendered on a separate localized layer 'Localized Text :: locale'
        $this->addCommand(new SetLayer('Localized Text'));

        $textBox = new TextBox('textBox', 100, 100, 60, 10);
        $textBox->setLocalized();
        $textBox->addString('Text content in locale ' . $this->project->getLanguage());
        $this->addCommand($textBox);

        $this->renderLocalizedTable();
    }

    /**
     * Render localized table
     *
     * @return void
     * @throws FilesystemException
     * @throws \Exception
     */
    private function renderLocalizedTable(): void
    {
        $tableBox = new TableBox('tableBox', 12.7, 150, 80, 20);
        $tableBox->setFit(TableBox::FIT_FRAME_TO_CONTENT)
                 ->setLocalized();

        $tableBox->setRowHeight(5);

        $tableBox->addColumn(40, null, 'ProductLabel')
                 ->addColumn(40, null, 'ProductLabel');

        $tableBox->startRow();
        $tableBox->addCell('Table in locale ' . $this->project->getLanguage());

        $asset = $this->loadRandomAsset('/Car Images/%');
        $image = new ImageBox('image');
        $image->setAsset($asset)
              ->setFit(ImageBox::FIT_CONTENT_TO_FRAME)
              ->setWidth(10)
              ->setHeight(10);
        //In localized tables all components in cells must be localized too.
        $image->setLocalized();

        $paragraph = new Paragraph();
        $paragraph->addComponent($image);
        $tableBox->addCell($paragraph);

        $this->addCommand($tableBox);
    }

    /**
     * Demonstrates the sorting of layers.
     *
     * @return void
     * @throws \Exception
     */
    private function sortLayers(): void
    {
//        Layers are sorted by defining an array with the order of layer names
        $order = [
            'Localization Demo',            //Exact layer name
            '/Localization Demo :: \w+/',   //Regex for localized "Localization Demo" layers
            '/Localized Text :: \w+/',      //Regex for localized "Localized Text" layers
        ];

        $this->addCommand(
            new SortLayers($order)
        );
    }
}
