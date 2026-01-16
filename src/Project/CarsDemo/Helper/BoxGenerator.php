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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper;

use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;

/**
 * Class BoxGenerator
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper
 */
class BoxGenerator
{
    /**
     * Returns the default `TextBox` command used in CarsDemo renderings.
     *
     * Uses `TextBox::FIT_FRAME_TO_CONTENT` to fit the text frame to the content.
     *
     * @param string $elementName
     * @param float  $left
     * @param float  $top
     * @param float  $width
     * @param float  $height
     * @param string $fit
     *
     * @return TextBox
     * @throws \Exception
     */
    public function getTextBox(
        string $elementName,
        float $left,
        float $top,
        float $width = 100,
        float $height = 100,
        string $fit = TextBox::FIT_FRAME_TO_CONTENT
    ): TextBox {
        return new TextBox($elementName, $left, $top, $width, $height, $fit);
    }

    /**
     * Creates a `TextBox` positioned relative to the top using
     * the $topRelativeVariable and $topRelativeMargin variables.
     *
     * @param string $elementName
     * @param float  $left
     * @param string $topRelativeVariable
     * @param float  $topRelativeMargin
     * @param float  $width
     * @param float  $height
     * @param string $fit
     *
     * @return TextBox
     * @throws \Exception
     */
    public function getTextBoxTopRelative(
        string $elementName,
        float $left,
        string $topRelativeVariable,
        float $topRelativeMargin,
        float $width = 100,
        float $height = 100,
        string $fit = TextBox::FIT_FRAME_TO_CONTENT
    ): TextBox {
        $textBox = $this->getTextBox($elementName, $left, 1, $width, $height, $fit);
        $textBox->setTopRelative($topRelativeVariable, $topRelativeMargin);

        return $textBox;
    }
}
