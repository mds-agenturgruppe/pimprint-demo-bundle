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

use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\Template;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\Service\SpecialChars;
use Mds\PimPrint\DemoBundle\Project\DataPrint\BrochureTemplate;
use Pimcore\Model\Asset;

/**
 * Trait PageLayoutTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait PageLayoutTrait
{
    /**
     * Helper variable to iterate over all facing page sided. Only needed cause the example projects generate
     * single and facing page documents.
     *
     * @var array
     */
    protected $facingPageSides = [
        Template::SIDE_FACING_BOTH,
        Template::SIDE_FACING_LEFT,
        Template::SIDE_FACING_RIGHT,
    ];

    /**
     * Sets Layers, opens pages and sets useful variables in InDesign document.
     *
     * @throws \Exception
     */
    protected function startPages()
    {
        //Project uses language independent text layers.
        TextBox::setDefaultUseLanguageLayer(false);

        $this->addCommand(new SetLayer('Content'));
        $this->addCommand(new GoToPage(0, false));

        //Best practice ist to have content top and left positions defined as variables inside the document.
        $this->addCommand(new Variable('originTop', BrochureTemplate::CONTENT_ORIGIN_TOP));
        $this->addCommand(new Variable('originLeft', BrochureTemplate::CONTENT_ORIGIN_LEFT));

        //Best practice to have the current yPos where to render elements as variable inside the document.
        //We start yPos at BrochureTemplate::CONTENT_ORIGIN_TOP.
        $this->addCommand(new Variable(Variable::VARIABLE_Y_POSITION, BrochureTemplate::CONTENT_ORIGIN_TOP));
    }

    /**
     * Sets page layout for left and right page for $label.
     * An optional $asset can be placed into the footer of the page.
     *
     * @param string     $label
     * @param Asset|null $asset
     *
     * @throws \Exception
     */
    protected function renderPageLayout(string $label, Asset $asset = null)
    {
        //Create the Template command
        $template = new Template();

        //Layout elements are placed into a 'Layout' layer.
        $template->addCommand(new SetLayer('Layout'));
        //As example projects can generate single and facing pages we have to register the SetLayer for all pages.
        foreach ($this->facingPageSides as $side) {
            $template->addCommand(new SetLayer('Layout'), $side);
        }
        $this->createLayoutLeftPage($label, $template);
        $this->createLayoutRightPage($label, $template);
        if ($asset instanceof Asset) {
            $this->createFooterAsset($asset, $template);
        }

        //Default layer for all content is 'Content'. We set this layer after rendering the page layout elements.
        $template->addCommand(new SetLayer('Content'));
        //As example projects can generate single and facing pages we have to register the SetLayer for all pages.
        foreach ($this->facingPageSides as $side) {
            $template->addCommand(new SetLayer('Content'), $side);
        }

        $this->addCommand($template);
    }

    /**
     * Creates layout commands in $template for left side with $label.
     *
     * @param string   $label
     * @param Template $template
     *
     * @throws \Exception
     */
    protected function createLayoutLeftPage(string $label, Template $template)
    {
        //The textbox is 90° rotated to have vertical texts, we have to consider that in positioning.
        //InDesign rotates the anchor-point (normal top-left) to the actual bottom-left position.
        $textBox = new TextBox(
            BrochureTemplate::ELEMENT_LAYOUT_BAR,
            BrochureTemplate::LAYOUT_BAR_LEFT_LEFT,
            BrochureTemplate::LAYOUT_BAR_TOP
        );
        $textBox->setResize(TextBox::RESIZE_NO_RESIZE);
        $textBox->addString($label);
        //Register for use on left side of facing page documents.
        $template->addCommand($textBox, Template::SIDE_FACING_LEFT);
        //Register for use on single page documents.
        $template->addCommand($textBox);

        $textBox = new TextBox(
            BrochureTemplate::ELEMENT_FOOTER_LEFT,
            BrochureTemplate::FOOTER_LEFT_LEFT,
            BrochureTemplate::FOOTER_TOP,
            BrochureTemplate::FOOTER_WIDTH,
            BrochureTemplate::FOOTER_HEIGHT
        );
        $textBox->addText($this->buildFooterText($label));
        //Register for use on left side of facing page documents.
        $template->addCommand($textBox, Template::SIDE_FACING_LEFT);
        //Register for use on single page documents.
        $template->addCommand($textBox);
    }

    /**
     * Creates layout commands in $template for right side with $label.
     *
     * @param string   $label
     * @param Template $template
     *
     * @throws \Exception
     */
    protected function createLayoutRightPage(string $label, Template $template)
    {
        //The textbox is 90° rotated to have vertical texts, we have to consider that in positioning.
        //InDesign rotates the anchor-point (normal top-left) to the actual bottom-left position.
        $textBox = new TextBox(
            BrochureTemplate::ELEMENT_LAYOUT_BAR,
            BrochureTemplate::LAYOUT_BAR_RIGHT_LEFT,
            BrochureTemplate::LAYOUT_BAR_TOP
        );
        $textBox->setResize(TextBox::RESIZE_NO_RESIZE);
        $textBox->addString($label);
        //Register for use on right side of facing page documents.
        $template->addCommand($textBox, Template::SIDE_FACING_RIGHT);

        $textBox = new TextBox(
            BrochureTemplate::ELEMENT_FOOTER_RIGHT,
            BrochureTemplate::FOOTER_RIGHT_LEFT,
            BrochureTemplate::FOOTER_TOP,
            BrochureTemplate::FOOTER_WIDTH,
            BrochureTemplate::FOOTER_HEIGHT
        );
        $textBox->addText($this->buildFooterText($label, false));
        //Register for use on right side of facing page documents.
        $template->addCommand($textBox, Template::SIDE_FACING_RIGHT);
    }

    /**
     * Builds Text element for footer box with pageNumber and $label.
     *
     * @param string $label
     * @param bool   $left
     *
     * @return Text
     * @throws \Exception
     */
    protected function buildFooterText(string $label, $left = true)
    {
        $pageNumber = sprintf(
            '<span class="%s">%s</span>',
            BrochureTemplate::STYLE_CHARACTER_PAGINA,
            SpecialChars::AUTO_PAGE_NUMBER
        );
        $separator = sprintf(
            '&#160;<span class="%s">|</span>&#160;',
            BrochureTemplate::STYLE_CHARACTER_SPACER
        );

        $style = BrochureTemplate::STYLE_PARAGRAPH_FOOTER_RIGHT;
        $parts = [$label, $separator, $pageNumber];
        if (true === $left) {
            $style = BrochureTemplate::STYLE_PARAGRAPH_FOOTER;
            $parts = [$pageNumber, $separator, $label];
        }

        $text = new Text($style);
        $text->addHtml(implode('', $parts));

        return $text;
    }

    /**
     * Places $asset into the footer of left and right page.
     *
     * @param Asset    $asset
     * @param Template $template
     *
     * @throws \Exception
     */
    protected function createFooterAsset(Asset $asset, Template $template)
    {
        $imageBox = new ImageBox(BrochureTemplate::ELEMENT_IMAGE);
        try {
            $imageBox->setAsset($asset);
        } catch (\Exception $e) {
            //For SVG logos we force a thumbnail.
            $imageBox->setAsset($asset, 'product_detail');
        }
        $imageBox->setWidth(BrochureTemplate::FOOTER_IMAGE_WIDTH)
                 ->setHeight(BrochureTemplate::FOOTER_IMAGE_HEIGHT)
                 ->setFit(ImageBox::FIT_PROPORTIONALLY)
                 ->setTop(BrochureTemplate::FOOTER_IMAGE_TOP)
                 ->setLeft(BrochureTemplate::FOOTER_IMAGE_LEFT);
        //Register for use on both sides of facing page documents.
        $template->addCommand($imageBox, Template::SIDE_FACING_BOTH);
        //Register for use on single page documents.
        $template->addCommand($imageBox);
    }
}
