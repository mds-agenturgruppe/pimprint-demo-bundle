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

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\Project\AbstractProject;
use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;

/**
 * Class GettingStarted
 *
 * @package Mds\PimPrint\DemoBundle\Service
 */
class GettingStarted extends AbstractProject
{
    /**
     * PublicationTreeBuilder instance.
     *
     * @var PublicationTreeBuilder
     */
    private $treeBuilder;

    /**
     * GettingStarted constructor.
     *
     * @param PublicationTreeBuilder $treeBuilder
     */
    public function __construct(PublicationTreeBuilder $treeBuilder)
    {
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * {@inheritDoc}
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
     * @throws \Exception
     */
    public function buildPublication(): void
    {
        //Initialize the InDesign rendering
        $this->startRendering();

        //Copy the element named 'templateElement' from template document into the generated document.
        $command = new CopyBox('templateElement', 10, 10);
        $this->addCommand($command);
    }
}
