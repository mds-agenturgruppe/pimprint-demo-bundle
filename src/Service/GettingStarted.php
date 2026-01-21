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

namespace Mds\PimPrint\DemoBundle\Service;

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\Project\RenderingProject;
use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class GettingStarted
 *
 * @package Mds\PimPrint\DemoBundle\Service
 */
class GettingStarted extends RenderingProject
{
    /**
     * GettingStarted constructor.
     *
     * @param PublicationTreeBuilder $treeBuilder
     */
    public function __construct(private readonly PublicationTreeBuilder $treeBuilder)
    {
    }

    /**
     * Returns the publication select options in the InDesign plugin for this project.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return [
            $this->treeBuilder->buildTreeElement('basicConcept', 'Basic Concept')
        ];
    }

    /**
     * Demonstrates the PimPrint basic concept.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    public function buildPublication(): void
    {
        //Initialize the InDesign rendering
        $this->startRendering();

        //Copy the element named 'templateElement' from the template document into the generated document.
        $command = new CopyBox('templateElement', 10, 10); //Position left/top 10 mm
        $this->addCommand($command);
    }
}
