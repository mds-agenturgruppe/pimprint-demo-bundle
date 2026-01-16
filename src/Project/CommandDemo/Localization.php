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
use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\SortLayers;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table as TableBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;

/**
 * Demonstrates localization of page elements.
 * Localized elements are automatically placed on language-specific layers.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Localization extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
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
     * This method does not render content into the document.
     * It only explains the localization API for page elements.
     *
     * @return void
     * @throws \Exception
     * @see \Mds\PimPrint\CoreBundle\InDesign\Command\Traits\DefaultLocalizedParamsTrait
     */
    private function conceptExplanation(): void
    {
        // All commands that create page elements in the InDesign document (CopyBox, TextBox, Table, and ImageBox)
        // can be defined as localized or not.
        // If a page element is defined as localized, it is automatically placed on a layer
        // with the current rendered locale appended to the layer name.
        // By default, all these elements are created as not localized.
        $copyBox = new CopyBox('copyBox', 10, 10);

        // You can set each box to be localized manually.
        $copyBox->setLocalized(true);

        // The box locale is set automatically to the currently rendered locale.
        // For special use-cases, you can set the locale manually.
        $copyBox->setLocale('en');

        // For convenience, you can change the default localization behavior per element.
        // When setDefaultLocalized is true, all new instances of the element are localized.
        CopyBox::setDefaultLocalized(true);
        TextBox::setDefaultLocalized(true);
        TableBox::setDefaultLocalized(true);
        ImageBox::setDefaultLocalized(true);

        // For this demo, reset the default behavior to false.
        CopyBox::setDefaultLocalized(false);
        TextBox::setDefaultLocalized(false);
        TableBox::setDefaultLocalized(false);
        ImageBox::setDefaultLocalized(false);
    }

    /**
     * Places non-localized elements into the rendered document.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function notLocalizedElements(): void
    {
        // The following page elements are created as not localized.
        // They are placed on the current active demo layer "Localization Demo".
        // Note: The layer is created in \Mds\PimPrint\DemoBundle\Project\CommandDemo\AbstractStrategy::initDemoLayer().

        $copyBox = new CopyBox('copyBox', 12.7, 12.7);
        // For demo purposes, we use not localized elements (default behavior).
        $copyBox->setLocalized(false);
        $this->addCommand($copyBox);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBox('image', 30, 12.7, 60, 40, $asset, ImageBox::FIT_FILL_PROPORTIONALLY);
        // For demo purposes, we use not localized elements (default behavior).
        $imageBox->setLocalized(false);
        $this->addCommand($imageBox);

        $textBox = new TextBox('textBox', 12.7, 60, 100, 10);
        // For demo purposes, we use not localized elements (default behavior).
        $textBox->setLocalized(false);
        $textBox->addString(
            'Change the rendered locale in the plugin and render this demo publication in different locales.'
        );
        $this->addCommand($textBox);
    }

    /**
     * Demonstrates placing localized page elements.
     * Localized elements are automatically placed on language-specific layers.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function localizedElements(): void
    {
        // The following page elements are created as localized.
        // They are placed on the current active demo layer "Localization Demo :: locale".
        // Note: The layer is created in \Mds\PimPrint\DemoBundle\Project\CommandDemo\AbstractStrategy::initDemoLayer().

        $copyBox = new CopyBox('copyBox', 12.7, 100);
        $copyBox->setLocalized();
        $this->addCommand($copyBox);

        $asset = $this->loadRandomAsset('/Car Images/%');
        $imageBox = new ImageBox('image', 30, 100, 60, 40, $asset, ImageBox::FIT_FILL_PROPORTIONALLY);
        $imageBox->setLocalized();
        $this->addCommand($imageBox);

        // The TextBox is rendered on a separate localized layer "Localized Text :: locale".
        $this->addCommand(new SetLayer('Localized Text'));

        $textBox = new TextBox('textBox', 100, 100, 60, 10);
        $textBox->setLocalized();
        $textBox->addString('Text content in locale ' . $this->project->getLanguage());
        $this->addCommand($textBox);

        $this->renderLocalizedTable();
    }

    /**
     * Renders a localized table
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
        // In localized tables, all elements in cells must be localized too.
        $image->setLocalized();

        $paragraph = new Paragraph();
        $paragraph->addComponent($image);
        $tableBox->addCell($paragraph);

        $this->addCommand($tableBox);
    }

    /**
     * Demonstrates layer sorting
     *
     * @return void
     * @throws \Exception
     */
    private function sortLayers(): void
    {
        // Layers are sorted by defining an array with the order of layer names.
        $order = [
            'Localization Demo',            // Exact layer name
            '/Localization Demo :: \w+/',   // Regex for localized "Localization Demo" layers
            '/Localized Text :: \w+/',      // Regex for localized "Localized Text" layers
        ];

        $this->addCommand(
            new SortLayers($order)
        );
    }
}
