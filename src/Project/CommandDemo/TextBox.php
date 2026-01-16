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

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Html\Style as HtmlStyle;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Text\Characters;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;

/**
 * Demonstrates the `TextBox` command for placing text elements in an InDesign document.
 * PimPrint supports InDesign paragraph and character styles.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class TextBox extends AbstractStrategy
{
    /**
     * The method generates the InDesign commands to build the demo publication.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    public function build(): void
    {
        $this->initDemo();

        $this->basicTextBox();
        $this->characterAndParagraph();
        $this->plainText();
        $this->htmlInlineStyle();
        $this->htmlProgrammaticStyle();
    }

    /**
     * The most basic usage is adding a string via `TextBox::addString()`.
     * In this case, no direct styling is applied to the text.
     * The template element defines the text styling.
     *
     * @return void
     * @throws \Exception
     */
    private function basicTextBox(): void
    {
        $this->addCommand(new GoToPage(1));

        // Places the "headline" text box from the InDesign template.
        $textBox = new TextBoxCommand('headline', 12.7, 12.7, 184.6, 5);
        $textBox->addString($this->getDemoWords());
        $this->addCommand($textBox);

        // Places the "copyText" text box from the InDesign template.
        $textBox = new TextBoxCommand('copyText', 12.7, 22, 184.6, 200);
        $textBox->addString($this->getDemoText(1, 'max'));
        $this->addCommand($textBox);
    }

    /**
     * To achieve full styling flexibility for text boxes, you can apply paragraph and character styles
     * defined in the InDesign template to the text.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function characterAndParagraph(): void
    {
        $this->addCommand(new GoToPage(2));

        // Creates a text box command and adds content sequentially.
        $textBox = $this->createDemoBox();
        $textBox->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT);

        // Creates a paragraph with the paragraph style "Headline".
        $paragraph = new Paragraph($this->getDemoWords(8), 'Headline');
        //Add the paragraph to the text box.
        $textBox->addParagraph($paragraph);

        // Creates a paragraph with the paragraph style "CopyText".
        $paragraph = new Paragraph($this->getDemoText(1, 'long'), 'CopyText');
        $textBox->addParagraph($paragraph);

        // Creates a paragraph with the paragraph style "CopyText" and character style "Highlight".
        $paragraph = new Paragraph($this->getDemoText(1, 'short'), 'CopyText', 'Highlight');
        $textBox->addParagraph($paragraph);

        // Creates a paragraph with the paragraph style "CopyText_ident".
        $paragraph = new Paragraph($this->getDemoText(1, 'medium'), 'CopyText_ident');
        $textBox->addParagraph($paragraph);

        // To allow more flexibility than paragraphs,
        // you can add character chunks with different character styles to a paragraph.

        // Creates a paragraph command and adds content sequentially.
        $paragraph = new Paragraph();
        $paragraph->setParagraphStyle('CopyText'); // Optionally sets a paragraph style.

        // Creates characters without a style.
        $characters = new Characters($this->getDemoWords());
        // Adds the characters to the paragraph.
        $paragraph->addComponent($characters);

        // Creates characters with the character style "superscript".
        $characters = new Characters($this->getDemoWords(2), 'Superscript');
        $paragraph->addComponent($characters);

        $characters = new Characters($this->getDemoWords());
        // Note that adding characters to a paragraph concatenates text without spaces.
        // Use the optional prependSpace parameter in addComponent to add a space if needed.
        $paragraph->addComponent($characters, true);

        // Creates characters with the character style "Highlight".
        $characters = new Characters($this->getDemoWords(), 'Highlight');
        $paragraph->addComponent($characters, true);

        $characters = new Characters($this->getDemoWords(20));
        $paragraph->addComponent($characters, true);

        // Characters can also contain hyperlinks.
        $characters = new Characters('Visit www.mds.eu', 'Bold');
        $characters->setHref('https://www.mds.eu');
        $paragraph->addComponent($characters, true);

        // Adds the paragraph with sequentially added characters to the text box.
        $textBox->addParagraph($paragraph);

        // Paragraphs can contain simple newlines.
        $paragraph = new Paragraph(PHP_EOL, 'CopyText');
        // You can also use ImageBox commands as paragraph components to place inline images in text.
        // This is mainly used to add images to tables (see the Table command demo).
        $asset = $this->loadRandomAsset('/Car Images/%');
        $image = new ImageBoxCommand('image');
        $image->setAsset($asset)
              ->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY)
              ->setWidth(30)
              ->setHeight(20);
        // Adds the image box as a component to the paragraph.
        $paragraph->addComponent($image);
        $textBox->addParagraph($paragraph);

        $textBox->addParagraph(new Paragraph($this->getDemoWords(20) . ':', 'CopyText'));

        // List items can be styled with InDesign paragraph styles.
        for ($i = 0; $i <= 5; $i++) {
            // Creates a paragraph with the paragraph style "ListItem".
            $textBox->addParagraph(
                new Paragraph(
                    $this->getDemoWords(3),
                    'ListItem', // Uses the InDesign template paragraph style "ListItem".
                    // Adds a random character style for demo purposes.
                    rand(0, 1) ? (bool)rand(0, 1) ? 'Highlight' : 'Bold' : ''
                )
            );
        }

        // Places the text box with all content at once in the InDesign document.
        $this->addCommand($textBox);
    }

    /**
     * The `InDesign\Text` class contains a parser that transforms text into `Paragraph` objects.
     * It provides a simpler interface to create multi-paragraph text, as shown in `TextBox::characterAndParagraph()`.
     *
     * @return void
     * @throws \Exception
     */
    private function plainText(): void
    {
        $this->addCommand(new GoToPage(3));

        // Creates the text instance.
        $text = new Text();
        $text->setParagraphStyle('CopyText'); // Sets the default paragraph style.

        // Adds plain text as a paragraph.
        $text->addPlainText($this->getDemoWords(6));

        // Adds plain text as a paragraph with a paragraph style.
        $text->addPlainText($this->getDemoWords(5), 'Headline');

        // Adds plain text as a paragraph with a paragraph style.
        $text->addPlainText($this->getDemoWords(8), null, 'UpperSpace');

        // Adds more plain text.
        $text->addPlainText($this->getDemoText(3, 'long'));

        for ($i = 0; $i <= 5; $i++) {
            $text->addPlainText($this->getDemoWords(4), $i == 5 ? 'ListItem_last' : 'ListItem');
        }

        // Text allows manual paragraph creation for full control over paragraphs and characters.
        $paragraph = new Paragraph(
            $this->getDemoWords(),
            'Headline',
            'UpperSpace'
        );
        $text->addParagraph($paragraph);

        // Creates the TextBox command.
        $textBox = $this->createDemoBox();
        $textBox->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT);

        // Adds the Text instance to the TextBox.
        $textBox->addText($text);

        // Places the text box in the InDesign document.
        $this->addCommand($textBox);
    }

    /**
     * The HTML parser maps tag class attributes to InDesign paragraph and character styles.
     * Class attributes on block elements map to paragraph styles.
     * Class attributes on inline elements map to character styles.
     *
     * This provides a quick and simple way to create formatted text content in InDesign.
     *
     * @return void
     * @throws \Exception|FilesystemException
     */
    protected function htmlInlineStyle(): void
    {
        $this->addCommand(new GoToPage(4));

        // The HTML parser supports inline images.
        $asset = $this->loadRandomAsset('/Car Images/%');
        $imgTag = sprintf(
            '<img src="%s" class="%s" width="184.6" height="100" data-fit="FILL_PROPORTIONALLY">',
            $asset->getFullPath(),
            'image' // The class is treated as the InDesign element name.
            // You can use the optional fit parameter with ImageBox fit modes.
            // Alternatively, use the AbstractParser::FACTORY_ELEMENT_IMAGE factory closure
            // to create dynamic ImageBox commands.
        );

        // Demo HTML content. Class attributes map to paragraph and character styles
        // defined in the InDesign template.
        $html = <<<EOT
<h1 class="Headline">
    Si dolor summum malum est, dici aliter non potest
</h1>
<p class="CopyText">
    <span class="Highlight">Lorem ipsum dolor sit amet</span>, consectetur adipiscing elit.
    Non enim, si omnia non sequebatur, idcirco non erat ortus illinc. Duo Reges: constructio interrete.
    Itaque e contrario <b class="Bold">moderati aequabilesque habitus</b>, affectiones ususque corporis apti
    esse ad naturam videntur. Ut proverbia non nulla veriora sint quam vestra dogmata. Ut alios omittam, hunc appello,
    quem ille unum secutus est. Tu vero, inquam, ducas licet, si sequetur.
</p>
<ul>
	<li class="ListItem">Quare obscurentur etiam haec, quae secundum naturam esse dicimus, in vita beata;</li>
	<li class="ListItem">Vitiosum est enim in <b class="Bold">dividendo partem in genere numerare.</b></li>
	<li class="ListItem_last">Virtutis, magnitudinis animi, patientiae, fortitudinis fomentis dolor mitigari solet.</li>
</ul>
<h2 class="SubHeadline_1">
    Qui ita affectus, <span class="Highlight">beatum esse numquam</span> probabis
</h2>
<div class="CopyText">
    Miserum hominem! Si dolor summum malum est, dici aliter non potest. <b class="Bold">Cur igitur, inquam,  es tam
    dissimiles eodem nomine appellas?</b> Quid igitur dubitamus in tota eius natura quaerere quid sit effectum? <br>
    Non dolere, inquam, istud quam vim habeat postea videro; Roges enim Aristonem, bonane ei videantur haec: vacuitas
    doloris, divitiae, valitudo; Ergo hoc quidem apparet, nos ad agendum esse natos.
</div>
<br>{$imgTag}
<h3 class="SubHeadline_2">
    Haeret in salebra
</h3>
<p class="CopyText">
    Neque solum ea communia, verum etiam paria esse dixerunt. Laelius clamores sofòw ille so lebat Edere compellans
    gumias ex ordine nostros. <em class="UpperSpace">Duarum enim vitarum</em> nobis erunt instituta capienda. Non ego
    tecum iam ita iocabor, ut isdem his de rebus, cum L. Haec dicuntur fortasse ieiunius; Propter nos enim illam,
    non propter eam nosmet ipsos  diligimus. Haec dicuntur inconstantissime. Sed ne, dum huic obsequor, vobis molestus
    sim. Negat esse eam, inquit, propter se expetendam.
 </p>
EOT;

        // Creates the Text instance.
        $text = new Text();
        // Adds HTML to the Text instance.
        $text->addHtml($html);

        // Creates the TextBox command.
        $textBox = $this->createDemoBox();
        // Adds the Text instance to the TextBox.
        $textBox->addText($text);

        // Places the text box in the InDesign document.
        $this->addCommand($textBox);
    }

    /**
     * The `InDesign\Text` class uses an HTML parser to transform HTML into `Paragraph` objects.
     * You can apply InDesign paragraph and character styles programmatically via `Text\HTML\Style`.
     *
     * @return void
     * @throws \Exception|FilesystemException
     */
    private function htmlProgrammaticStyle(): void
    {
        $this->addCommand(new GoToPage(5));

        // Defines an HTML\Style.
        $style = new HtmlStyle();
        $style->setParagraph('h1', 'Headline')
              ->setParagraph('h2', 'SubHeadline_1')
              ->setParagraph('h3', 'SubHeadline_2')
              ->setParagraph('li', 'ListItem')
            // li tags in ul and ol support the :first and :last pseudo-classes.
              ->setParagraph('li:last', 'ListItem_last')
              ->setParagraph('p', 'CopyText')
              ->setCharacter('b', 'Bold')
              ->setCharacter('i', 'Highlight');

        $text = new Text();
        // Sets the styles in the HTML parser.
        $text->getHTMLParser()
             ->setStyle($style);

        // Adds HTML to the Text instance.
        $text->addHtml($this->getDemoHtml());

        // Creates the TextBox command.
        $textBox = $this->createDemoBox();
        // Adds the Text instance to the TextBox.
        $textBox->addText($text);

        // Places the text box in the InDesign document.
        $this->addCommand($textBox);
    }

    /**
     * Creates a `TextBox` command instance.
     *
     * @return TextBoxCommand
     * @throws \Exception
     */
    private function createDemoBox(): TextBoxCommand
    {
        return new TextBoxCommand(
            'textBox',
            12.7,
            12.7,
            184.6,
            200,
            TextBoxCommand::FIT_FRAME_TO_CONTENT
        );
    }
}
