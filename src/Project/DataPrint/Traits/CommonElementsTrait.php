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

namespace Mds\PimPrint\DemoBundle\Project\DataPrint\Traits;

use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\DemoBundle\Project\DataPrint\BrochureTemplate;

/**
 * Trait CommonElementsTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait CommonElementsTrait
{
    /**
     * Renders title element with content $label.
     *
     * We can position the title element statically at the top of the page, because title elements are rendered for
     * categories or manufacturers.
     *
     * @param string $label
     * @param string $yPosVariable
     *
     * @throws \Exception
     */
    protected function renderTitle(string $label, $yPosVariable = Variable::VARIABLE_Y_POSITION)
    {
        $box = new TextBox(
            BrochureTemplate::ELEMENT_TITLE,
            BrochureTemplate::CONTENT_ORIGIN_LEFT,
            BrochureTemplate::CONTENT_ORIGIN_TOP,
            BrochureTemplate::CONTENT_WIDTH,
            BrochureTemplate::TITLE_HEIGHT
        );
        $box->addString($label)
            ->setVariable($yPosVariable, Variable::POSITION_BOTTOM)
            ->setBoxIdentReferenced('title');
        $this->addCommand($box);
    }

    /**
     * Renders a subTitle element with $label.
     *
     * subTitle is positioned relative the registered yPos variable.
     * The element size is used from template and then the frame is adjusted to content.
     *
     * @param string    $label
     * @param string    $yPosVariable
     * @param int|float $top
     *
     * @throws \Exception
     */
    protected function renderSubTitle($label, $yPosVariable = Variable::VARIABLE_Y_POSITION, $top = 0)
    {
        $subTitle = new TextBox(BrochureTemplate::ELEMENT_SUBTITLE);
        $subTitle->addString($label)
                 ->setResize(AbstractBox::RESIZE_NO_RESIZE)
                 ->setFit(TextBox::FIT_FRAME_TO_CONTENT)
                 ->setLeft(BrochureTemplate::CONTENT_ORIGIN_LEFT)
                 ->setTop($top)
                 ->setVariable($yPosVariable, Variable::POSITION_BOTTOM)
                 ->setBoxIdentReferenced('subTitle');
        $this->addCommand($subTitle);
    }

    /**
     * Creates a check new page command to have automatic page breaks.
     *
     * @return CheckNewPage
     * @throws \Exception
     */
    protected function createCheckNewPage()
    {
        return new CheckNewPage(
            BrochureTemplate::CONTENT_HEIGHT + BrochureTemplate::PAGE_MARGIN_TOP,
            BrochureTemplate::PAGE_MARGIN_TOP
        );
    }
}
