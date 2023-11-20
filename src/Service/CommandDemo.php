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

use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractCommand;
use Mds\PimPrint\CoreBundle\Project\AbstractProject;
use Mds\PimPrint\CoreBundle\Service\InDesign\PublicationTreeBuilder;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Mds\PimPrint\DemoBundle\Project\CommandDemo\AbstractStrategy;

/**
 * Project to demonstrate all PimPrint InDesign commands.
 *
 * - Install mds.PimPrint InDesign Plugin.
 * - Create server connection in InDesign Plugin to your Pimcore demo server with installed PimPrint DemoBundle.
 * - Activate server connection.
 * - Select Project Command Demo in PimPrint Plugin.
 * - Create a new empty A4 single page or facing page portrait document in InDesign with at least 10 pages.
 *
 * @package Mds\PimPrint\DemoBundle\Project
 */
class CommandDemo extends AbstractProject
{
    /**
     * PublicationTreeBuilder service.
     *
     * @var PublicationTreeBuilder
     */
    private PublicationTreeBuilder $treeBuilder;

    /**
     * CommandDemo constructor.
     *
     * @param PublicationTreeBuilder $treeBuilder
     */
    public function __construct(PublicationTreeBuilder $treeBuilder)
    {
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * Returns the publication select options in the InDesign plugin for this project.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        $demos = [
            'CopyBox',
            'ImageBox',
            'TextBox',
            'Table',
            'Page Handling',
            'Groups',
            'Layers',
            'Localization',
            'Relative Positioning',
            'Messages',
        ];
        $return = [];
        foreach ($demos as $demo) {
            $return[] = $this->treeBuilder->buildTreeElement(str_replace(' ', '', $demo), $demo);
        }

        return $return;
    }

    /**
     * Method called to build the InDesign commands to generate the publication.
     *
     * @return void
     * @throws \Exception
     */
    public function buildPublication(): void
    {
        $this->startRendering();

        $strategy = AbstractStrategy::factory(
            $this->pluginParams()
                 ->get(PluginParameters::PARAM_PUBLICATION),
            $this
        );
        $strategy->build();

        $this->stopRendering();
    }

    /**
     * Change method signature to have addCommand accessible in demo strategy context.
     *
     * @param AbstractCommand $command
     *
     * @return AbstractProject
     * @throws \Exception
     */
    public function addCommand(AbstractCommand $command): AbstractProject
    {
        return parent::addCommand($command);
    }

    /**
     * Change method signature to have addCommand accessible in demo strategy context.
     *
     * @param AbstractCommand[] $commands
     *
     * @return AbstractProject
     * @throws \Exception
     */
    public function addCommands(array $commands): AbstractProject
    {
        return parent::addCommands($commands);
    }

    /**
     * Change method signature to have addCommand accessible in demo strategy context.
     *
     * @param string $message
     *
     * @return AbstractProject
     */
    public function addPreMessage(string $message): AbstractProject
    {
        return parent::addPreMessage($message);
    }
}
