<?php
/**
 * mds. Agenturgruppe GmbH
 *
 * This source file is available under the terms of the
 * mds. Commercial License (MCL)
 *
 * Full copyright and license information is available in
 * LICENSE.md, which is distributed with this source code.
 *
 * @copyright Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license   mds. Commercial License (MCL)
 */

namespace Mds\PimPrint\DemoBundle\Project\CommandDemo;

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\RemoveEmptyLayers;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\SortLayers;

/**
 * Demonstrates layer functions when placing elements in an InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Layers extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->setDocumentSettings();

        $this->boxLayers(12.7);
        $this->sortLayers();

        // Empty layers can be removed with the RemoveEmptyLayers command.
        $this->addCommand(
            new RemoveEmptyLayers()
        );
    }

    /**
     * Demonstrates placing elements on layers and using the `SetLayer` command.
     *
     * @param float $topPosition the top-position of the elements
     *
     * @return void
     * @throws \Exception
     */
    private function boxLayers(float $topPosition): void
    {
        // The element is placed on the same layer as in the template document.
        // If the layer does not exist in the generated document, InDesign creates it automatically.
        $this->addCommand(
            new CopyBox('image', 12.7, $topPosition)
        );

        // You can set the layer name when copying an element using `LayerTrait`.
        $box = new CopyBox('image', 60, $topPosition);
        $box->setLayer('Layer A');
        $this->addCommand($box);

        // You can create new layers using the `SetLayer` command.
        // All following boxes are added to the last created layer.
        $this->addCommand(
            new SetLayer('Layer B')
        );
        $this->addCommand(
            new CopyBox('copyBox', 110, $topPosition)
        );
        $this->addCommand(
            new CopyBox('copyBox', 130, $topPosition)
        );

        $this->addCommand(
            new SetLayer('Layer C')
        );
        $this->addCommand(
            new CopyBox('copyBox', 150, $topPosition)
        );
        $this->addCommand(
            new CopyBox('copyBox', 170, $topPosition)
        );

        // New layers are created only when an element is placed on them.
        // This layer is not created in the document.
        $this->addCommand(new SetLayer('Empty layer'));
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
        // This example orders the layers created in boxLayers() in reverse order.
        $order = [
            'Layer C', // Exact layer name
            'Layer B', // Exact layer name
            'Layer A', // Exact layer name
        ];
        // All layers not defined in the $order array keep their current position.

        $this->addCommand(
            new SortLayers($order)
        );
    }
}
