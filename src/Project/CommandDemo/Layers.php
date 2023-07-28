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
use Mds\PimPrint\CoreBundle\InDesign\Command\SortLayers;

/**
 * Demonstrates layer functions when placing elements in InDesign Document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Layers extends AbstractStrategy
{
    /**
     * Method generated the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->setDocumentSettings();

        $this->boxLayers(12.7);
        $this->sortLayers();

//        Empty layers can be removed with RemoveEmptyLayers command.
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
//        Element is placed on the same layer as in the template document.
//        If the layer doesn't exist in the generated document the layer is created automatically.
        $this->addCommand(
            new CopyBox('image', 12.7, $topPosition)
        );

//        Layer name that an element is placed on can be set via LayerTrait
        $box = new CopyBox('image', 50, $topPosition);
        $box->setLayer('Layer A');
        $this->addCommand($box);

//        New layers can be created with SetLayer command. All following boxes are added to the last set layer.
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

//        New layers are only created when an element is placed on it. This layer won't be created in the document.
        $this->addCommand(new SetLayer('Empty layer'));
    }

    /**
     * Demonstrates the sorting of layers
     *
     * @return void
     * @throws \Exception
     */
    private function sortLayers(): void
    {
//        Layers are sorted by defining an array with the order of layer names
//        In this example we order the layers created in boxLayers() in reverse order
        $order = [
            'Layer C', //Exact layer name
            'Layer B', //Exact layer name
            'Layer A', //Exact layer name
        ];
//        All layers not defined in the order array are left where they are.

        $this->addCommand(
            new SortLayers($order)
        );
    }
}
