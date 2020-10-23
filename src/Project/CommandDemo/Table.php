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

use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\SplitTable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table as TableCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Html\FragmentParser;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Html\Style as HtmlStyle;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;

/**
 * Demonstrates the Table command for placement of table elements in InDesign document.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Table extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     *
     * @return void
     * @throws \Exception
     */
    public function build(): void
    {
        $this->initDemoLayer();

        $this->basicTable();
        $this->namedColumns();
        $this->stylingTables();
        $this->enhancedTableContent();
        $this->htmlTable();
        $this->splitTable();
    }

    /**
     * Demonstrates the basic usage of placing a table defining columns and adding rows with cells.
     *
     * @return void
     * @throws \Exception
     */
    private function basicTable(): void
    {
        $this->addCommand(new GoToPage(1));

        //Create the Table command and set position and size initially.
        //As all commands all parameters can be set with setter methods.
        $tableBox = new TableCommand('tableBox', 12.7, 20, 176, 150);
        $tableBox->setFit(TableCommand::FIT_FRAME_TO_CONTENT);

        //Set the default height for rows
        $tableBox->setRowHeight(5);

        //In a first step the columns of the table are defined. Each column expects at least a width in mm.
        $tableBox->addColumn(20)
                 ->addColumn(20)
                 ->addColumn(30)
                 ->addColumn(40);

        //Rows and cells are added sequentially to table.
        $tableBox->startRow();

        //The simplest cell content is passing plain test into the cell.
        $tableBox->addCell('Cell 1/1')
                 ->addCell('Cell 1/2')
                 ->addCell('Cell 1/3')
                 ->addCell('Cell 1/4');

        //If we add more cells to a row, than columns defined an exception is thrown
        try {
            $tableBox->addCell('Not existent cell');
        } catch (\Exception $e) {
            $this->placeText($e->getMessage());
        }

        //Start the next row
        $tableBox->startRow()
            //Table cells support colspan parameter
                 ->addCell('Cell 2/1')
                 ->addCell('Cell 2/2', null)
                 ->addCell('Cell 2/3', null, 2);

        $tableBox->startRow()
            //Table cells support colspan parameter
                 ->addCell('Cell 3/1', null, 2)
                 ->addCell('Cell 3/2', null)
                 ->addCell('Cell 3/3', null);

        $tableBox->startRow()
            //Add one empty "spacing cell" with colspan 4.
                 ->addCell('', null, 4);

        $tableBox->startRow()
            //Not all cells of a row must contain content. This row has only the first two cells.
                 ->addCell('Cell 4/1', null)
                 ->addCell('Cell 4/2', null);

        $tableBox->startRow()
            //Cell content can be set in arbitrary order. With the ident parameter the column number can be defined.
                 ->addCell('Cell 5/2', 2)
                 ->addCell('Cell 5/3', 3)
                 ->addCell('Cell 5/1', 1)
                 ->addCell('Cell 5/4'); //If the ident parameter omitted the next empty cell is used
        //Also see Table::namedColumns() example for ident parameter usage.

        $tableBox->startRow()
                 ->addCell('Content will not be rendered in InDesign.');
        //An already started row can be removed from the table.
        $tableBox->abortRow();

        //A row can contain any cells at all.
        $tableBox->startRow(10); //Each row can have a individual own height

        //Add some random rows and cells
        for ($row = 6; $row <= 20; $row++) {
            $tableBox->startRow();
            for ($column = 1; $column <= 4; $column++) {
                $tableBox->addCell("Cell $row/$column", null);
                (bool)rand(0, 1) ? $column = 4 : false;
            }
        }

        //Place the table on InDesign page.
        $this->addCommand($tableBox);
    }

    /**
     * Table command offers named columns. This allows to access cells in a explicit way.
     *
     * @return void
     * @throws \Exception
     */
    private function namedColumns(): void
    {
        $this->addCommand(new GoToPage(2));

        $tableBox = $this->getDemoTable();

        //When defining columns names for columns can be set with the 'ident' param.
        //The order of appearance in the table is the order in which the columns are added to the table.
        $tableBox->addColumn(10, 'counter')
                 ->addColumn(40, 'title')
                 ->addColumn(40, 'property')
                 ->addColumn(80, 'description');

        $tableBox->startRow()
            //When adding cells, columns can be accessed via its ident.
                 ->addCell('1', 'counter')
                 ->addCell('Column "title"', 'title')
                 ->addCell('Column "property"', 'property')
                 ->addCell('Column "description"', 'description');

        //When accessing a not defined column an exception is thrown.
        try {
            $tableBox->startRow()
                     ->addCell('Not existent column', 'notDefined');
        } catch (\Exception $e) {
            $tableBox->abortRow();
            $this->placeText($e->getMessage());
        }

        $tableBox->startRow()
            //Order when adding named cells doesn't matter.
                 ->addCell('Added first', 'property')
                 ->addCell('Added second', 'description')
                 ->addCell('Added third', 'title')
                 ->addCell('2', 'counter');

        $tableBox->startRow()
            //As in basic example rows can have empty cells
                 ->addCell('Row with empty cells', 'description')
                 ->addCell('3', 'counter');

        $tableBox->startRow()
            //As in basic example cells can have colspans.
                 ->addCell('4', 'counter', 2);

        $tableBox->startRow()
            //In named columns mode cell content can be overwritten.
                 ->addCell('property', 'property')
                 ->addCell('description', 'description')
                 ->addCell('title', 'title')
                 ->addCell('5', 'counter')
                 ->addCell('Overwritten content in column "description"', 'description');

        $this->addCommand($tableBox);
    }

    /**
     * Tables can be styled with table and cell styles in the InDesign template.
     *
     * @return void
     * @throws \Exception
     */
    private function stylingTables(): void
    {
        $this->addCommand(new GoToPage(3));

        $tableBox = $this->getDemoTable(12.7);

        //Set the table style defined in InDesign template
        $tableBox->setTableStyle('PriceTable');

        //When defining a column the default cell style in InDesign template can be set.
        $tableBox->addColumn(40, null, 'ProductLabel')
                 ->addColumn(22, null, 'Price_A')
                 ->addColumn(22, null, 'Price_B')
                 ->addColumn(22, null, 'Price_C')
                 ->addColumn(22, null, 'Price_D')
                 ->addColumn(22, null, 'Price_E');

        $headCellStyle = 'TableHead';
        $tableBox->startRow(10)
            //When adding cells the cell a style parameter can be passed to overwrite the default column style.
                 ->addCell('Article', null, 1, $headCellStyle)
                 ->addCell('Price A [€]', null, 1, $headCellStyle)
                 ->addCell('Price B [€]', null, 1, $headCellStyle)
                 ->addCell('Price C [€]', null, 1, $headCellStyle)
                 ->addCell('Price D [€]', null, 1, $headCellStyle)
                 ->addCell('Price E [€]', null, 1, $headCellStyle);

        $tableBox->startRow()
            //When adding cells without style parameter the cell style defined for the column is being used.
                 ->addCell($this->getDemoWords(3))
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice());

        $tableBox->startRow()
            //When adding cells with style the optional perpendStyle parameter allows just to prepend the style to
            //the default column style.
                 ->addCell($this->getDemoWords(3))
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice(), null, 1, '_strike', true)
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice(), null, 1, '_strike', true)
                 ->addCell($this->getDemoPrice());

        $tableBox->startRow(15)
            //Adding a blank spacer row with text
                 ->addCell($this->getDemoWords(10), null, 6, 'TableCell');

        //Add some random rows and cells
        for ($row = 1; $row <= 10; $row++) {
            $tableBox->startRow()
                     ->addCell($this->getDemoWords(3));

            for ($column = 1; $column <= 4; $column++) {
                switch ((bool)rand(0, 1)) {
                    case true:
                        $tableBox->addCell($this->getDemoPrice());
                        break;

                    default:
                        $tableBox->addCell($this->getDemoPrice(), null, 1, '_strike', true);
                }
                (bool)rand(0, 2) ? false : $column = 5;
            }
        }

        $this->addCommand($tableBox);
    }

    /**
     * Table content can be complex texts, images and arbitrary template elements though the usage of CopyBox command.
     *
     * @return void
     * @throws \Exception
     */
    private function enhancedTableContent(): void
    {
        $this->addCommand(new GoToPage(4));

        //In this demo we place rich formatted text into a table. We use paragraph and character styles defined in
        //the InDesign template element "textBox". We have to place this element in the Document to have the styles
        //availible in this demo.
        $this->addCommand(new TextBoxCommand('textBox', -10, -10, 1, 1));

        //Create the table box and add columns
        $tableBox = $this->getDemoTable(12.7)
                         ->setTableStyle('PriceTable')
                         ->addColumn(30, 'label')
                         ->addColumn(154.6, 'content');

        //To format cell content any InDesign\Text object can be used.
        $text = new Text();
        $text->setParagraphStyle('CopyText')
             ->addPlainText($this->getDemoWords(6))
             ->addPlainText($this->getDemoWords(5), 'Headline')
             ->addPlainText($this->getDemoWords(8), null, 'Highlight')
             ->addPlainText($this->getDemoText(1, 'short'));

        //Use Text instance as cell content.
        $tableBox->startRow()
                 ->addCell(
                     $this->createLabelContent('Programmatically created text'),
                     'label'
                 )
                 ->addCell($text, 'content');

        //All functionality of InDesign\Text, including HTML content, can be used as cell content.
        $style = new HtmlStyle();
        $style->setParagraph('h1', 'Headline')
              ->setParagraph('h2', 'SubHeadline_1')
              ->setParagraph('h3', 'SubHeadline_1')
              ->setParagraph('li', 'ListItem')
              ->setParagraph('li:last', 'ListItem_last')
              ->setParagraph('p', 'CopyText')
              ->setCharacter('b', 'Bold')
              ->setCharacter('i', 'Highlight');

        $text = new Text();
        $text->addHtml($this->getDemoHtml(2, true, true, true, 'short'), $style);

        $tableBox->startRow()
                 ->addCell(
                     $this->createLabelContent('Parsed HTML content'),
                     'label'
                 )
                 ->addCell($text, 'content');

        $asset = $this->loadRandomAsset('/Car Images/%');
        $image = new ImageBoxCommand('image');
        $image->setAsset($asset)
              ->setFit(ImageBoxCommand::FIT_CONTENT_TO_FRAME)
              ->setWidth(30)
              ->setHeight(20);
        $paragraph = new Paragraph(PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL, 'CopyText');
        $paragraph->addComponent($image);

        $tableBox->startRow(100)
                 ->addCell(
                     $this->createLabelContent('Images via ImageBox command'),
                     'label'
                 )
                 ->addCell($paragraph, 'content');

        $this->addCommand($tableBox);
    }

    /**
     * FragmentParser offers functionality to parse complete HTML fragments.
     * The parsing process creates multiple InDesign commands.
     *
     * @throws \Exception
     */
    private function htmlTable()
    {
        $this->addCommand(new GoToPage(5));
        $this->addCommand(new Variable(Variable::VARIABLE_Y_POSITION, 12.7));

        $factory = function (string $element, \DomElement $node = null) {
            $ySpacing = 5;
            switch ($element) {
                case FragmentParser::FACTORY_ELEMENT_TEXT:
                    return new Text();
                case FragmentParser::FACTORY_ELEMENT_TEXT_BOX:
                    $textBox = new TextBoxCommand('textBox', 12.7, 0, 184.6);
                    $textBox->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT);
                    $textBox->setTopRelative(Variable::VARIABLE_Y_POSITION, $ySpacing);
                    $textBox->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);

                    return $textBox;
                case FragmentParser::FACTORY_ELEMENT_TABLE:
                    $table = new TableCommand('tableBox', 12.7, 20, 184.6, 200);
                    $table->setFit(TableCommand::FIT_FRAME_TO_CONTENT_HEIGHT);
                    $table->setTopRelative(Variable::VARIABLE_Y_POSITION, $ySpacing);
                    $table->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);

                    return $table;
            }

            return null;
        };
        $parser = new FragmentParser($factory);
        $parser->getStyle()
               ->setCell('th', 'TableHead')
               ->setCell('td', 'TableCell');

        $html = <<<EOT
<div class="CopyText">
    Miserum hominem! Si dolor summum malum est, dici aliter non potest. <b class="Bold">Cur igitur, inquam,  es tam
    dissimiles eodem nomine appellas?</b> Quid igitur dubitamus in tota eius natura quaerere quid sit effectum? <br>
    Non dolere, inquam, istud quam vim habeat postea videro; Roges enim Aristonem, bonane ei videantur haec: vacuitas
    doloris, divitiae, valitudo; Ergo hoc quidem apparet, nos ad agendum esse natos.
</div>
<table class="PriceTable">
    <thead>
        <tr>
            <th>Table Head 1</th>
            <th>Table Head 2</th>
            <th>Table Head 3</th>
            <th>Table Head 4</th>
            <th>Table Head 5</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Cell Content 1/1</td>
            <td>Cell Content 1/2</td>
            <td>Cell Content 1/3</td>
            <td>Cell Content 1/4</td>
            <td>Cell Content 1/5</td>
        </tr>
        <tr>
            <td>Cell Content 2/1</td>
            <td>Cell Content 2/2</td>
            <td>Cell Content 2/3</td>
            <td>Cell Content 2/4</td>
            <td>Cell Content 2/5</td>
        </tr>
        <tr>
            <td>Cell Content 3/1</td>
            <td>Cell Content 3/2</td>
            <td>Cell Content 3/3</td>
            <td>Cell Content 3/4</td>
            <td>Cell Content 3/5</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td class="Price_A">Table Footer 1</td>
            <td class="Price_B">Table Footer 2</td>
            <td class="Price_C">Table Footer 3</td>
            <td class="Price_D">Table Footer 4</td>
            <td class="Price_E">Table Footer 5</td>
        </tr>
    </tfoot>
</table>
<div class="CopyText">
    Neque solum ea communia, verum etiam paria esse dixerunt. Laelius clamores sofòw ille so lebat Edere compellans
    gumias ex ordine nostros. <em class="UpperSpace">Duarum enim vitarum</em> nobis erunt instituta capienda. Non ego
    tecum iam ita iocabor, ut isdem his de rebus, cum L. Haec dicuntur fortasse ieiunius; Propter nos enim illam,
    non propter eam nosmet ipsos  diligimus. Haec dicuntur inconstantissime. Sed ne, dum huic obsequor, vobis molestus
    sim. Negat esse eam, inquit, propter se expetendam.
</div>
EOT;

        $this->addCommands($parser->parse($html));
    }

    /**
     * Demonstrates the SplitTable command.
     * This command offers splits up tables onto multiple pages repeating the header and footer rows.
     *
     * @throws \Exception
     */
    private function splitTable()
    {
        $this->addCommand(new GoToPage(6));
        $topPosition = 12.7;

        //Sample headline
        $headline = new TextBoxCommand('textBox', 12.7, $topPosition);
        $headline->addString('SplitTable Headline')
                 ->setWidth(50)
                 ->setHeight(10)
                 ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
                 ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);
        $this->addCommand($headline);

        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString($this->getDemoText(1))
                 ->setWidth(184.6)
                 ->setHeight(50)
                 ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
                ->setTopRelative(Variable::VARIABLE_Y_POSITION, 5)
                 ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);
        $this->addCommand($text);

        //Create the table.
        $tableBox = $this->getDemoTable();
        //Give the table the maximum height that is can use on a single page with it's preCommands content.
        $tableBox->setHeight(265)
            ->setWidth(180);
        $tableBox->setTableStyle('PriceTable')
                 ->addColumn(36)
                 ->addColumn(36)
                 ->addColumn(36)
                 ->addColumn(36)
                 ->addColumn(36);

        //Position the table with 2mm margin to variable yPos.
        $tableBox->setTopRelative(Variable::VARIABLE_Y_POSITION, 2.5)
        //Assign table bottom to Variable::VARIABLE_Y_POSITION in InDesign.
                 ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);

        //TableCommand::ROW_TYPE_HEADER rows will be repeated automatically when tables are split over multiple pages.
        $cellStyle = 'TableHead';
        $tableBox->startRow(10, TableCommand::ROW_TYPE_HEADER)
                 ->addCell('Table Head 1', null, 1, $cellStyle)
                 ->addCell('Table Head 2', null, 1, $cellStyle)
                 ->addCell('Table Head 3', null, 1, $cellStyle)
                 ->addCell('Table Head 4', null, 1, $cellStyle)
                 ->addCell('Table Head 5', null, 1, $cellStyle);

        //For demo purpose we add some random rows to the table.
        for ($i = 1; $i <= rand(60, 100); $i++) {
            $tableBox->startRow()
                     ->addCell("Cell Content $i/1")
                     ->addCell("Cell Content $i/2")
                     ->addCell("Cell Content $i/3")
                     ->addCell("Cell Content $i/4")
                     ->addCell($this->getDemoWords(3));
        }

        //TableCommand::ROW_TYPE_FOOTER rows will be repeated automatically when tables are split over multiple pages.
        $cellStyle = 'TableFoot';
        $tableBox->startRow(10, TableCommand::ROW_TYPE_FOOTER)
                 ->addCell('Table Foot 1', null, 1, $cellStyle)
                 ->addCell('Table Foot 2', null, 1, $cellStyle)
                 ->addCell('Table Foot 3', null, 1, $cellStyle)
                 ->addCell('Table Foot 4', null, 1, $cellStyle)
                 ->addCell('Table Foot 5', null, 1, $cellStyle);

        //CheckNewPage command defined the maximum y-Position where content can be rendered on the page.
        //If a element would be placed underneath this y-Position it is placed on the next page at then new y-Position
        //parameter value.
        $checkNewPage = new CheckNewPage(284, $topPosition);

        //Create the SplitTable command and pass the Table to split and the CheckNewPage page break definition.
        $splitTable = new SplitTable($tableBox, $checkNewPage);

        //We want to have $headline repeated on each page before the table.
        $splitTable->addPreCommand($headline);

        //Add the SplitTable command to CommandQueue.
        $this->addCommand($splitTable);

        //Place a box after the table.
        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString('Textbox after split table')
                 ->setWidth(50)
                 ->setHeight(10)
                 ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
                 ->setTopRelative(Variable::VARIABLE_Y_POSITION, 5);
        $this->addCommand($text);
    }

    /**
     * Creates InDesign\Text instance for label cell.
     *
     * @param string $content
     *
     * @return Text
     * @throws \Exception
     */
    private function createLabelContent(string $content): Text
    {
        $text = new Text('CopyText');
        $text->addPlainText($content);

        return $text;
    }

    /**
     * Creates a Table command instance.
     *
     * @param float $topPosition
     *
     * @return TableCommand
     * @throws \Exception
     */
    private function getDemoTable(float $topPosition = 20): TableCommand
    {
        $table = new TableCommand('tableBox', 12.7, $topPosition, 184.6, 200);
        $table->setRowHeight(5)
              ->setFit(TableCommand::FIT_FRAME_TO_CONTENT);

        return $table;
    }

    /**
     * Generates a dummy price.
     *
     * @param int $min
     * @param int $max
     *
     * @return string
     */
    private function getDemoPrice($min = 10, $max = 99): string
    {
        return (string)rand($min, $max) . '.' . rand(10, 99);
    }
}
