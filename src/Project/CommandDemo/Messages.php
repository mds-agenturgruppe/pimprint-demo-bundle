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

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox as CopyBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;

/**
 * Demonstrates how to display arbitrary messages in InDesign Plugin for notification and error purposes.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Messages extends AbstractStrategy
{
    /**
     * Method generated the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemoLayer();
        $this->pluginMessages();
    }

    /**
     * Demonstrates different types of messages displayed in InDesign Plugin.
     *
     * @return void
     * @throws \Exception
     */
    private function pluginMessages(): void
    {
        $margin = 30;
        $this->addCommand(new GoToPage(1));
        $topPosition = 12.7;

        //AbstractProject offers with addPreMessage() a method to send arbitraty messages to InDesign,
        //which are displayed before rendering starts.
        $this->addPreMessage('Demo of Plugin messages');

        //Place a example box.
        $this->addCommand(new CopyBoxCommand('image', 12.7, $topPosition));

        //It does not matter when a PreMessages is added. They are always displayed before rendering starts.
        $this->addPreMessage('Next message');

        //With PageMessages messages can be displayed while rendering in context with the current rendered page.
        $this->addPageMessage('Message displayed while rendering.');

        $topPosition += $margin;
        $this->addCommand(new CopyBoxCommand('image', 12.7, $topPosition));
        $topPosition += $margin;
        $this->addCommand(new CopyBoxCommand('image', 12.7, $topPosition));
        $this->addPageMessage('Placed two more boxes on page.');

        //PageMessages can either be displayed in rendering overlay or added to the page in a text box.
        $this->addPageMessage('OnPage message.', true);

        $this->addPageMessage(
            'OnPage messages can be used to set notices that should be kept in the InDesign document.',
            true
        );

        $this->addCommand(new GoToPage(2));
        $topPosition = 12.7;

        $this->addCommand(new CopyBoxCommand('image', 12.7, $topPosition));
        $this->addPageMessage('Message while generating page 2.');
        $this->addPageMessage('OnPage message on page 2.', true);
    }
}
