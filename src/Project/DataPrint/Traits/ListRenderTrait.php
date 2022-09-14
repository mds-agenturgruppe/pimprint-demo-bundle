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

use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\CheckNewPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\ImageBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SplitTable;
use Mds\PimPrint\CoreBundle\InDesign\Command\Table;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\CoreBundle\InDesign\Text\Paragraph;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractTemplate;
use Mds\PimPrint\DemoBundle\Project\DataPrint\ListTemplate;
use Pimcore\Model\Asset;

/**
 * Trait ListRenderTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint\Traits
 */
trait ListRenderTrait
{
    /**
     * Table command.
     *
     * @var Table|null
     */
    protected ?Table $table = null;

    /**
     * SplitTable command.
     *
     * @var SplitTable|null
     */
    protected ?SplitTable $splitTable = null;

    /**
     * Initializes Table and SplitTable command.
     *
     * @param string $yPosVariable
     *
     * @return void
     * @throws \Exception
     */
    protected function initTable(string $yPosVariable = Variable::VARIABLE_Y_POSITION): void
    {
        $table = new Table(ListTemplate::ELEMENT_TABLE);
        $table->setLeft(AbstractTemplate::CONTENT_ORIGIN_LEFT)
              ->setWidth(ListTemplate::TABLE_WIDTH)
              ->setHeight(AbstractTemplate::CONTENT_HEIGHT)
              ->setLineHeight(null)
              ->setFit(Table::FIT_FRAME_TO_CONTENT_HEIGHT)
              ->setTableStyle(ListTemplate::STYLE_TABLE)
              ->setTopRelative($yPosVariable, AbstractTemplate::BLOCK_Y_SPACE)
              ->setVariable($yPosVariable, Variable::POSITION_BOTTOM);

        $this->initSplitTable($yPosVariable);

        $this->table = $table;
    }

    /**
     * Initializes the SplitTable command to split the table dynamically over n pages.
     *
     * @param string $yPosVariable
     *
     * @return void
     * @throws \Exception
     */
    private function initSplitTable(string $yPosVariable): void
    {
        $splitTable = new SplitTable();

        //Define a CheckNewPage command with ListTemplate::CONTENT_HEIGHT as maxYPos
        $checkPage = new CheckNewPage(
            AbstractTemplate::CONTENT_BOTTOM,
            AbstractTemplate::CONTENT_ORIGIN_TOP
        );
        $splitTable->setCheckNewPage($checkPage);

        //Register ListTemplate::CONTENT_ORIGIN_TOP as table $yPosVariable on each new page.
        $variable = new Variable($yPosVariable, AbstractTemplate::CONTENT_ORIGIN_TOP - AbstractTemplate::BLOCK_Y_SPACE);
        $splitTable->addPreCommand($variable);

        $this->splitTable = $splitTable;
    }

    /**
     * Returns table command.
     *
     * @return Table
     * @throws \Exception
     */
    protected function getTable(): Table
    {
        if (false === $this->table instanceof Table) {
            $this->initTable();
        }

        return $this->table;
    }

    /**
     * Return splitTable command.
     *
     * @return SplitTable
     * @throws \Exception
     */
    protected function getSplitTable(): SplitTable
    {
        //Just to ensure, that SplitTable was initialized.
        $this->getTable();

        return $this->splitTable;
    }

    /**
     * Adds the $table with $splitTable to CommandQueue.
     *
     * @return void
     * @throws \Exception
     */
    protected function addSplitTable(): void
    {
        $splitTable = $this->getSplitTable();
        $splitTable->setTable($this->getTable());
        $this->addCommand($splitTable);
    }

    /**
     * Sets columns in table. Creates columns in table and adds head_row with column names.
     *
     * @param array $definition
     *
     * @return void
     * @throws \Exception
     * @throws FilesystemException
     */
    protected function setupTableStructure(array $definition): void
    {
        if (empty($definition)) {
            return;
        }
        $this->createColumns($definition);
        $this->createHeadRow($definition);
    }

    /**
     * Creates columns defined in $this->tableColumnDefinition.
     *
     * @param array $definition
     *
     * @return void
     * @throws \Exception
     */
    protected function createColumns(array $definition): void
    {
        foreach ($definition as $columnDefinition) {
            $this->getTable()
                 ->addColumn($columnDefinition['width'], $columnDefinition['ident'], $columnDefinition['cellStyle']);
        }
    }

    /**
     * Creates head row defined in $this->tableColumnDefinition.
     *
     * @param array $definition
     *
     * @return void
     * @throws \Exception
     */
    protected function createHeadRow(array $definition): void
    {
        $this->getTable()
             ->startRow(null, Table::ROW_TYPE_HEADER);

        foreach ($definition as $columnDefinition) {
            $content = '';
            if (false === empty($columnDefinition['translation'])) {
                $content = $this->translator->trans($columnDefinition['translation']);
                //Some placeholders have : in there value. For column headlines we don't want them to show up.
                $content = str_replace(':', '', $content);
            }
            $this->getTable()
                 ->addCell($content, $columnDefinition['ident'], 1, $columnDefinition['headStyle']);
        }
    }

    /**
     * Builds a Text element for $asset to be places in lists.
     *
     * @param Asset $asset
     *
     * @return Text
     * @throws \Exception
     * @throws FilesystemException
     */
    protected function buildTableImageElement(Asset $asset): Text
    {
        $image = new ImageBox(AbstractTemplate::ELEMENT_IMAGE);
        $image->setAsset($asset)
              ->setFit(ImageBox::FIT_FILL_PROPORTIONALLY)
              ->setWidth(ListTemplate::TABLE_IMAGE_WIDTH)
              ->setHeight(ListTemplate::TABLE_IMAGE_HEIGHT);
        $paragraph = new Paragraph();
        $paragraph->addComponent($image);
        $text = new Text();
        $text->addParagraph($paragraph);

        return $text;
    }
}
