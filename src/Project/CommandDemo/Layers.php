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

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\RemoveEmptyLayers;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;

/**
 * Demonstrates layer functions when placing elements in InDesign Document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Layers extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->boxLayers(12.7);

        //The layer behaviour of TextBox can either be the same as normal boxes or be automatic language dependent.
        $this->textLayers(40);
        $this->textLayersLanguage(50);

        //Empty layers can be removed with RemoveEmptyLayers command.
        $this->addCommand(
            new RemoveEmptyLayers()
        );
    }

    /**
     * Demonstrates the placement of elements on layers and usage of SetLayer command.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function boxLayers(float $topPosition): void
    {
        //Element is placed on the same layer as in the template document. If the layer doesn't exist it's created.
        $this->addCommand(
            new CopyBox('image', 12.7, $topPosition)
        );

        //Layer name that a element is placed on can be set via LayerTrait
        $box = new CopyBox('image', 50, $topPosition);
        $box->setLayer('Layer A');
        $this->addCommand($box);

        //New layers can be created with SetLayer command. All following boxes are added to the last set layer.
        $this->addCommand(
            new SetLayer('Layer B')
        );
        $this->addCommand(
            new CopyBox('copyBox', 100, $topPosition)
        );
        $this->addCommand(
            new CopyBox('copyBox', 120, $topPosition)
        );

        $this->addCommand(
            new SetLayer('Layer C')
        );
        $this->addCommand(
            new CopyBox('copyBox', 140, $topPosition)
        );
        $this->addCommand(
            new CopyBox('copyBox', 160, $topPosition)
        );

        //New layers are only created when a element is placed on it. This layer won't be created in the document.
        $this->addCommand(new SetLayer('Empty layer'));
    }

    /**
     * Demonstrates the language independent layer placement of TextBox.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function textLayers(float $topPosition): void
    {
        //We deactivate the default use language layer option.
        TextBox::setDefaultUseLanguageLayer(false);

        //Target layer should be "Text Layer".
        $this->addCommand(
            new SetLayer('Text Layer')
        );

        //Element is placed on layer "Text Layer".
        $box = new TextBox('copyText', 12.7, $topPosition, 70, 5);
        $box->addString("Element on 'Text Layer'");
        $this->addCommand($box);

        //We activate the default value to demonstrate the setter in TextBox.
        TextBox::setDefaultUseLanguageLayer(true);

        //Layer name that a element is placed on can be set via LayerTrait
        $box = new TextBox('copyText', 90, $topPosition, 70, 5);
        $box->addString("Element on 'Direct Text Layer'")
            ->setUseLanguageLayer(false) //TextBox has a setter for the instance.
            ->setLayer("Direct Text Layer");
        $this->addCommand($box);
    }

    /**
     * Demonstrates the language dependent layer placement of TextBox.
     *
     * @param float $topPosition
     *
     * @return void
     * @throws \Exception
     */
    private function textLayersLanguage(float $topPosition): void
    {
        //We activate the default use language layer option.
        TextBox::setDefaultUseLanguageLayer(true);

        //TextBoxes are always placed on language dependent layers.
        //The current active language short code will added as postfix to the target layer name.
        //eg. "Layer A" will be "Layer A de"
        $language = $this->pluginParams()
                         ->get(PluginParameters::PARAM_LANGUAGE);

        $this->addCommand(
            new SetLayer('Layers Text')
        );

        //Element is placed on layer "Text Layer" with the language iso postfix.
        $box = new TextBox('copyText', 12.7, $topPosition, 70, 5);
        $box->addString("Element on 'Text Layer $language'");
        $this->addCommand($box);

        //Layer name that a element is placed on can be set via LayerTrait.
        $box = new TextBox('copyText', 90, $topPosition, 70, 5);
        $box->addString("Element on 'Direct Text Layer $language'")
            ->setLayer("Direct Text Layer");
        $this->addCommand($box);
    }
}
