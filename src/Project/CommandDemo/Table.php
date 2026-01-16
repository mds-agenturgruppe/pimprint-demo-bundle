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
use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\GoToPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox as ImageBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\SplitTable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table as TableCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox as TextBoxCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Html\FragmentParser;
use Mds\PimPrint\CoreBundle\InDesign\Html\Style as HtmlStyle;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Demonstrates the `Table` command for placing table elements in an InDesign document.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
class Table extends AbstractStrategy
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

        $this->basicTable();
        $this->namedColumns();
        $this->stylingTables();
        $this->enhancedTableContent();
        $this->htmlTable();
        $this->splitTable();
    }

    /**
     * Demonstrates basic table placement by defining columns and adding rows with cells.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function basicTable(): void
    {
        $this->addCommand(new GoToPage(1));

        // Create the Table command and set its initial position and size.
        // Like all commands, you can set all parameters using setter methods.
        $tableBox = new TableCommand('tableBox', 12.7, 20, 176, 150);
        $tableBox->setFit(TableCommand::FIT_FRAME_TO_CONTENT);

        // Sets the default row height.
        $tableBox->setRowHeight(5);

        // First, define the table columns. Each column requires at least a width in mm.
        $tableBox->addColumn(20)
                 ->addColumn(20)
                 ->addColumn(30)
                 ->addColumn(40);

        // Then add rows and cells sequentially to the table.
        $tableBox->startRow();

        // The simplest cell content is plain text.
        $tableBox->addCell('Cell 1/1')
                 ->addCell('Cell 1/2')
                 ->addCell('Cell 1/3')
                 ->addCell('Cell 1/4');

        // If a row has more cells than defined columns, an \Exception is thrown.
        try {
            $tableBox->addCell('Not existent cell');
        } catch (\Exception $e) {
            $this->placeText($e->getMessage());
        }

        // Starts the next row.
        $tableBox->startRow()
            // Table cells support the colspan parameter.
                 ->addCell('Cell 2/1')
                 ->addCell('Cell 2/2', null)
                 ->addCell('Cell 2/3', null, 2);

        $tableBox->startRow()
            // Table cells support the colspan parameter.
                 ->addCell('Cell 3/1', null, 2)
                 ->addCell('Cell 3/2', null)
                 ->addCell('Cell 3/3', null);

        $tableBox->startRow()
            // Adds one empty spacing cell with colspan 4.
                 ->addCell('', null, 4);

        $tableBox->startRow()
            // Not all cells in a row need content. This row uses only the first two cells.
                 ->addCell('Cell 4/1', null)
                 ->addCell('Cell 4/2', null);

        $tableBox->startRow()
            // You can set cell content in any order.
            // Use the ident parameter to define the column index.
                 ->addCell('Cell 5/2', 2)
                 ->addCell('Cell 5/3', 3)
                 ->addCell('Cell 5/1', 1)
                 ->addCell('Cell 5/4'); // If you omit the ident parameter, the table uses the next empty cell.
        // See the Table::namedColumns() example for ident usage.

        $tableBox->startRow()
                 ->addCell('Content will not be rendered in InDesign.');
        // You can remove an already started row from the table.
        $tableBox->abortRow();

        // A row may contain no cells.
        $tableBox->startRow(10); // Each row can have its own height.

        // Adds some random rows and cells.
        for ($row = 6; $row <= 20; $row++) {
            $tableBox->startRow();
            for ($column = 1; $column <= 4; $column++) {
                $tableBox->addCell("Cell $row/$column", null);
                rand(0, 1) ? $column = 4 : false;
            }
        }

        // Places the table on the InDesign page.
        $this->addCommand($tableBox);
    }

    /**
     * The `Table` command supports named columns, which allow explicit access to cells.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function namedColumns(): void
    {
        $this->addCommand(new GoToPage(2));

        $tableBox = $this->getDemoTable();

        // When defining columns, you can set column names using the 'ident' parameter.
        // The order in the table matches the order in which you add the columns.
        $tableBox->addColumn(10, 'counter')
                 ->addColumn(40, 'title')
                 ->addColumn(40, 'property')
                 ->addColumn(80, 'description');

        $tableBox->startRow()
            // When adding cells, you can access columns by their ident.
                 ->addCell('1', 'counter')
                 ->addCell('Column "title"', 'title')
                 ->addCell('Column "property"', 'property')
                 ->addCell('Column "description"', 'description');

        // Accessing an undefined column throws an \Exception.
        try {
            $tableBox->startRow()
                     ->addCell('Not existent column', 'notDefined');
        } catch (\Exception $e) {
            $tableBox->abortRow();
            $this->placeText($e->getMessage());
        }

        $tableBox->startRow()
            // The order of adding named cells does not matter.
                 ->addCell('Added first', 'property')
                 ->addCell('Added second', 'description')
                 ->addCell('Added third', 'title')
                 ->addCell('2', 'counter');

        $tableBox->startRow()
            // Rows can contain empty cells, as in the basic example.
                 ->addCell('Row with empty cells', 'description')
                 ->addCell('3', 'counter');

        $tableBox->startRow()
            // Cells can use colspan, as in the basic example.
                 ->addCell('4', 'counter', 2);

        $tableBox->startRow()
            // In named-column mode, you can overwrite cell content.
                 ->addCell('property', 'property')
                 ->addCell('description', 'description') // set description
                 ->addCell('title', 'title')
                 ->addCell('5', 'counter')
                 ->addCell('Overwritten content in column "description"', 'description'); // overwrite

        $this->addCommand($tableBox);
    }

    /**
     * Tables can use table and cell styles defined in the InDesign template.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function stylingTables(): void
    {
        $this->addCommand(new GoToPage(3));

        $tableBox = $this->getDemoTable(12.7);

        // Sets the table style defined in the InDesign template.
        $tableBox->setTableStyle('PriceTable');

        // When defining a column, you can set the default cell style from the template.
        $tableBox->addColumn(40, style: 'ProductLabel')
                 ->addColumn(22, style: 'Price_A')
                 ->addColumn(22, style: 'Price_B')
                 ->addColumn(22, style: 'Price_C')
                 ->addColumn(22, style: 'Price_D')
                 ->addColumn(22, style: 'Price_E');

        $headCellStyle = 'TableHead';
        $tableBox->startRow(10)
            // When adding cells, you can pass a cell style to override the default column style.
                 ->addCell('Article', style: $headCellStyle)
                 ->addCell('Price A [€]', style: $headCellStyle)
                 ->addCell('Price B [€]', style: $headCellStyle)
                 ->addCell('Price C [€]', style: $headCellStyle)
                 ->addCell('Price D [€]', style: $headCellStyle)
                 ->addCell('Price E [€]', style: $headCellStyle);

        $tableBox->startRow()
            // If you omit the style parameter, the column’s default cell style is used.
                 ->addCell($this->getDemoWords(3))
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice());

        $tableBox->startRow()
            // When adding cells with a style, the optional appendStyle parameter
            // allows you to append the style text to the default column style name.
            // => PriceTable -> PriceTable_strike
                 ->addCell($this->getDemoWords(3))
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice(), null, 1, '_strike', true)
                 ->addCell($this->getDemoPrice())
                 ->addCell($this->getDemoPrice(), null, 1, '_strike', true)
                 ->addCell($this->getDemoPrice());

        $tableBox->startRow(15)
            // Adds a blank spacer row with text.
                 ->addCell($this->getDemoWords(10), null, 6, 'TableCell');

        // Adds some random rows and cells.
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
                rand(0, 2) ? false : $column = 5;
            }
        }

        $this->addCommand($tableBox);
    }

    /**
     * Table content can include complex text, images, and arbitrary template elements
     * through the use of the `CopyBox` command.
     *
     * @return void
     * @throws FilesystemException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     */
    private function enhancedTableContent(): void
    {
        $this->addCommand(new GoToPage(4));

        // In this demo, we place rich-formatted text into a table.
        // We use paragraph and character styles defined in the InDesign template element: "textBox".
        // We must place this element in the document to make the styles available.
        $this->addCommand(new TextBoxCommand('textBox', -10, -10, 1, 1));

        // Creates the table box and adds columns.
        $tableBox = $this->getDemoTable(12.7)
                         ->setTableStyle('PriceTable')
                         ->addColumn(30, 'label')
                         ->addColumn(154.6, 'content');

        // You can use any InDesign\Text object to format cell content.
        $text = new Text();
        $text->setParagraphStyle('CopyText')
             ->addPlainText($this->getDemoWords(6))
             ->addPlainText($this->getDemoWords(5), 'Headline')
             ->addPlainText($this->getDemoWords(8), null, 'Highlight')
             ->addPlainText($this->getDemoText(1, 'short'));

        // Use a Text instance as cell content.
        $tableBox->startRow()
                 ->addCell(
                     $this->createLabelContent('Programmatically created text'),
                     'label'
                 )
                 ->addCell($text, 'content');

        // You can use all InDesign\Text features, including HTML, as cell content.
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
        $text->addHtml($this->getDemoHtml(2, length: 'short'), $style);

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
        $paragraph = new Paragraph();
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
     * The `FragmentParser` parses complete HTML fragments.
     * The parsing process creates multiple InDesign commands.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function htmlTable(): void
    {
        $this->addCommand(new GoToPage(5));
        $this->addCommand(new Variable(Variable::VARIABLE_Y_POSITION, 12.7 - 5));

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
     * Demonstrates the `SplitTable` command.
     * This command splits tables across multiple pages and repeats the header and footer rows.
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    private function splitTable(): void
    {
        $this->addCommand(new GoToPage(6));
        $topPosition = 12.7;

        // Sample headline
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

        // Create the table.
        $tableBox = $this->getDemoTable();
        // Sets the maximum height the table can use on a single page, including its preCommands content.
        $tableBox->setHeight(265)
                 ->setWidth(180);
        $tableBox->setTableStyle('PriceTable')
                 ->addColumn(36)
                 ->addColumn(36)
                 ->addColumn(36)
                 ->addColumn(36)
                 ->addColumn(36);

        // Positions the table with a 2mm margin relative to the yPos variable.
        $tableBox->setTopRelative(Variable::VARIABLE_Y_POSITION, 2.5)
            // Assigns the table bottom to Variable::VARIABLE_Y_POSITION in InDesign.
                 ->setVariable(Variable::VARIABLE_Y_POSITION, Variable::POSITION_BOTTOM);

        // TableCommand::ROW_TYPE_HEADER rows repeat automatically when the table splits across pages.
        $cellStyle = 'TableHead';
        $tableBox->startRow(10, TableCommand::ROW_TYPE_HEADER)
                 ->addCell('Table Head 1', style: $cellStyle)
                 ->addCell('Table Head 2', style: $cellStyle)
                 ->addCell('Table Head 3', style: $cellStyle)
                 ->addCell('Table Head 4', style: $cellStyle)
                 ->addCell('Table Head 5', style: $cellStyle);

        // For demo purposes, we add some random rows to the table.
        $cellStyle = 'TableCell';
        for ($i = 1; $i <= rand(60, 100); $i++) {
            $tableBox->startRow()
                     ->addCell("Cell Content $i/1", style: $cellStyle)
                     ->addCell("Cell Content $i/2", style: $cellStyle)
                     ->addCell("Cell Content $i/3", style: $cellStyle)
                     ->addCell("Cell Content $i/4", style: $cellStyle)
                     ->addCell($this->getDemoWords(3), style: $cellStyle);
        }

        // TableCommand::ROW_TYPE_FOOTER rows also repeat automatically when the table splits across pages.
        $cellStyle = 'TableFoot';
        $tableBox->startRow(10, TableCommand::ROW_TYPE_FOOTER)
                 ->addCell('Table Foot 1', style: $cellStyle)
                 ->addCell('Table Foot 2', style: $cellStyle)
                 ->addCell('Table Foot 3', style: $cellStyle)
                 ->addCell('Table Foot 4', style: $cellStyle)
                 ->addCell('Table Foot 5', style: $cellStyle);

        // The CheckNewPage command defines the maximum y-position where content can be rendered on the page.
        // If an element is placed below this y-position, it is placed on the next page at the new y-position.
        $checkNewPage = new CheckNewPage(284, $topPosition);

        // Create the SplitTable command and pass the table to split and the CheckNewPage page-break definition.
        $splitTable = new SplitTable($tableBox, $checkNewPage);

        // Repeat the $headline on each page before the table.
        $splitTable->addPreCommand($headline);

        // Add the SplitTable command to the CommandQueue.
        $this->addCommand($splitTable);

        // Places a box after the table.
        $text = new TextBoxCommand('textBox', 12.7);
        $text->addString('Textbox after split table')
             ->setWidth(50)
             ->setHeight(10)
             ->setFit(TextBoxCommand::FIT_FRAME_TO_CONTENT)
             ->setTopRelative(Variable::VARIABLE_Y_POSITION, 5);
        $this->addCommand($text);
    }

    /**
     * Creates an `InDesign\Text` instance for the label cell.
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
     * Creates a `Table` command instance.
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
    private function getDemoPrice(int $min = 10, int $max = 99): string
    {
        return rand($min, $max) . '.' . rand(10, 99);
    }
}
