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

use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Text\Characters;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Mds\PimPrint\CoreBundle\InDesign\Html\Style as HtmlStyle;

/**
 * Demonstrates the TextBox command for placement of text elements in InDesign document.
 * PimPrint supports InDesign paragraph styles and character styles.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class TextBox extends AbstractStrategy
{
    /**
     * TextBox command offers a variety of possibilities to style and display texts.
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemoLayer();

        $this->basicTextBox();
        $this->characterAndParagraph();
        $this->plainText();
        $this->htmlInlineStyle();
        $this->htmlProgrammaticStyle();
    }

    /**
     * The most basic usage is adding a string via TextBox::addString(). In this case no direct styling will be
     * applied to the text. Text will be styled as defined in the template element.
     *
     * @return void
     * @throws \Exception
     */
    private function basicTextBox(): void
    {
        $this->addCommand(new GoToPage(1));

        //Place the 'headline' text box from InDesign template
        $textBox = new TextBoxCommand('headline', 12.7, 12.7, 184.6, 5);
        $textBox->addString($this->getDemoWords());
        $this->addCommand($textBox);

        //Place the 'copyText' text box from InDesign template
        $textBox = new TextBoxCommand('copyText', 12.7, 22, 184.6, 200);
        $textBox->addString($this->getDemoText(3));
        $this->addCommand($textBox);
    }

    /**
     * To have full styling flexibility for text boxes paragraph and character styles defined in InDesign template
     * can be applied to the text.
     *
     * @return void
     * @throws \Exception
     */
    private function characterAndParagraph(): void
    {
        $this->addCommand(new GoToPage(2));

        //Create a text box command and add content sequentially.
        $textBox = $this->createDemoBox();
        $textBox->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT);

        //Create a paragraph with paragraph style 'Headline'.
        $paragraph = new Paragraph($this->getDemoWords(8), 'Headline');
        //Add the paragraph to the text box.
        $textBox->addParagraph($paragraph);

        //Create a paragraph with paragraph style 'CopyText'.
        $paragraph = new Paragraph($this->getDemoText(1, 'long'), 'CopyText');
        $textBox->addParagraph($paragraph);

        //Create a paragraph with paragraph style 'CopyText' and character style 'Highlight'.
        $paragraph = new Paragraph($this->getDemoText(1, 'short'), 'CopyText', 'Highlight');
        $textBox->addParagraph($paragraph);

        //Create a paragraph with paragraph style 'CopyText_ident'.
        $paragraph = new Paragraph($this->getDemoText(1, 'medium'), 'CopyText_ident');
        $textBox->addParagraph($paragraph);

        //To give flexibility beyond paragraphs we can add chunks of characters with different
        //character styles to a paragraph.

        //Create a paragraph and add content sequentially.
        $paragraph = new Paragraph();
        $paragraph->setParagraphStyle('CopyText'); //Optionally set a paragraph style

        //Create characters without a style.
        $characters = new Characters($this->getDemoWords());
        //Add the characters to the paragraph.
        $paragraph->addComponent($characters);

        //Create characters with character style 'superscript'
        $characters = new Characters($this->getDemoWords(2), 'Superscript');
        $paragraph->addComponent($characters);

        $characters = new Characters($this->getDemoWords());
        //Note that when adding characters to a paragraph text is concatenated without space.
        //The optional prependSpace parameter in addComponents allows to add this space character if needed.
        $paragraph->addComponent($characters, true);

        //Create characters with character style 'Highlight'.
        $characters = new Characters($this->getDemoWords(), 'Highlight');
        $paragraph->addComponent($characters, true);

        $characters = new Characters($this->getDemoWords(20));
        $paragraph->addComponent($characters, true);

        //Characters can also contain a hyperlink.
        $characters = new Characters('Visit www.mds.eu', 'Bold');
        $characters->setHref('https://www.mds.eu');
        $paragraph->addComponent($characters, true);

        //Add the paragraph with sequentially added chars to text box.
        $textBox->addParagraph($paragraph);

        //Paragraphs can contain simple newlines
        $paragraph = new Paragraph(PHP_EOL, 'CopyText');
        //As paragraph components also ImageBox commands can be used to place inline images into text.
        //Mainly used to add images into tables. (See Table command demonstration)
        $asset = $this->loadRandomAsset('/Car Images/%');
        $image = new ImageBoxCommand('image');
        $image->setAsset($asset)
              ->setFit(ImageBoxCommand::FIT_FILL_PROPORTIONALLY)
              ->setWidth(30)
              ->setHeight(20);
        //Add the image box as component to the paragraph.
        $paragraph->addComponent($image);
        $textBox->addParagraph($paragraph);

        $textBox->addParagraph(new Paragraph($this->getDemoWords(20) . ':', 'CopyText'));

        //List items can be styled with InDesign paragraph styles
        for ($i = 0; $i <= 5; $i++) {
            //Create a paragraph with paragraph style 'ListItem'.
            $textBox->addParagraph(
                new Paragraph(
                    $this->getDemoWords(3),
                    'ListItem', //InDesign template paragraph style for ListItems
                    //Add random character style for demo purpose.
                    (bool)rand(0, 1) ? (bool)rand(0, 1) ? 'Highlight' : 'Bold' : ''
                )
            );
        }

        //Place the text box with all content at one in InDesign document.
        $this->addCommand($textBox);
    }

    /**
     * Class InDesign/Text contains a parser to transform text into Paragraph objects.
     * It offers a simper interface to create multi paragraph text as shown in TextBox::characterAndParagraph().
     *
     * @return void
     * @throws \Exception
     */
    private function plainText(): void
    {
        $this->addCommand(new GoToPage(3));

        //Create the text instance.
        $text = new Text();
        $text->setParagraphStyle('CopyText'); //Sets the default paragraph style.

        //Add plain text as paragraph.
        $text->addPlainText($this->getDemoWords(6));

        //Add a plain text as paragraph with paragraph style.
        $text->addPlainText($this->getDemoWords(5), 'Headline');

        //Add a plain text as paragraph with character style.
        $text->addPlainText($this->getDemoWords(8), null, 'UpperSpace');

        //Add some more plain text
        $text->addPlainText($this->getDemoText(2));

        for ($i = 0; $i <= 5; $i++) {
            $text->addPlainText($this->getDemoWords(4), $i == 5 ? 'ListItem_last': 'ListItem');
        }

        //Text allows manual adding of paragraphs to give the full flexibility of paragraphs and characters.
        $paragraph = new Paragraph(
            $this->getDemoWords(5),
            'Headline',
            'UpperSpace'
        );
        $text->addParagraph($paragraph);

        //Create the TextBox command.
        $textBox = $this->createDemoBox();
        $textBox->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT);

        //Add Text instance to the TextBox
        $textBox->addText($text);

        //Place the text box in InDesign document.
        $this->addCommand($textBox);
    }

    /**
     * The HTML parser handles tag class attributes as InDesign paragraph and character styles.
     * Class attributes of block elements are converted to paragraph styles.
     * Class attributes of inline elements are converted to character styles
     *
     * This offers a quick and simple way to create formatted text content in InDesign.
     *
     * @return void
     * @throws \Exception
     */
    protected function htmlInlineStyle(): void
    {
        $this->addCommand(new GoToPage(4));

        //HTML Parser supports inline images.
        $asset = $this->loadRandomAsset('/Car Images/%');
        $imgTag = sprintf(
            '<img src="%s" class="%s" width="184.6" height="100" data-fit="FILL_PROPORTIONALLY">',
            $asset->getFullPath(),
            'image' //class is treated as InDesign element name
            //optional fit parameter can be used with ImageBox fit modes.
            //Or use the factory closure AbstractParser::FACTORY_ELEMENT_IMAGE can create dynamic ImageBox commands.
        );

        //Demo HTML content. Class attributes are defined paragraph and character styles in InDesign template.
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

        //Create Text instance
        $text = new Text();
        //Add HTML to Text.
        $text->addHtml($html);

        //Create TextBox command.
        $textBox = $this->createDemoBox();
        // Add Text to TextBox.
        $textBox->addText($text);

        $this->addCommand($textBox);
    }

    /**
     * Class InDesign/Text uses a HTML parser for transform HTML into Paragraph objects.
     * Styling with InDesign paragraph and character styles can be done programmatically wia Text\HTML\Style
     *
     * Example HTML is generated by:
     * https://loripsum.net/api/long/3/headers/ul/decorate
     *
     * @return void
     * @throws \Exception
     */
    private function htmlProgrammaticStyle(): void
    {
        $this->addCommand(new GoToPage(5));

        //Define HTML\Style
        $style = new HtmlStyle();
        $style->setParagraph('h1', 'Headline')
              ->setParagraph('h2', 'SubHeadline_1')
              ->setParagraph('h3', 'SubHeadline_2')
              ->setParagraph('li', 'ListItem')
            //li tags in ul and ol supports :first and :last pseudo classes
              ->setParagraph('li:last', 'ListItem_last')
              ->setParagraph('p', 'CopyText')
              ->setCharacter('b', 'Bold')
              ->setCharacter('i', 'Highlight');

        $text = new Text();
        //Set $style in HTML parser
        $text->getHTMLParser()
             ->setStyle($style);

        //Add HTML to Text
        $text->addHtml($this->getDemoHtml(4, true, true, true, 'long'));

        //Create TextBox command.
        $textBox = $this->createDemoBox();
        //Add Text to TextBox.
        $textBox->addText($text);

        $this->addCommand($textBox);
    }

    /**
     * Creates a TextBox command instance.
     *
     * @return TextBoxCommand
     * @throws \Exception
     */
    private function createDemoBox()
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
