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

use App\Model\Product\AccessoryPart as AccessoryPartProduct;
use League\Flysystem\FilesystemException;
use Mds\PimPrint\CoreBundle\InDesign\Command\NextPage;
use Mds\PimPrint\CoreBundle\InDesign\Command\Variable;
use Mds\PimPrint\CoreBundle\InDesign\Text;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractProject;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractTemplate;
use Mds\PimPrint\DemoBundle\Project\DataPrint\ListTemplate;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\ListRenderTrait;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\SalesInformationTrait;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\AccessoryPart\Listing as AccessoryPartListing;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class DataPrintAccessoryPartList.
 *
 * @package Mds\PimPrint\DemoBundle\Service
 */
class DataPrintAccessoryPartList extends AbstractProject
{
    use ListRenderTrait;
    use SalesInformationTrait;

    /**
     * Width of all known size columns.
     *
     * @var float
     */
    const COLUMN_WIDTH_KNOWN =
        ListTemplate::COLUMN_WIDTH_EAN
        + ListTemplate::COLUMN_WIDTH_PRICE
        + ListTemplate::COLUMN_WIDTH_IMAGE
        + ListTemplate::COLUMN_WIDTH_CONDITION
        + ListTemplate::COLUMN_WIDTH_MILEAGE;

    /**
     * Space for free defined columns
     *
     * @var float
     */
    const COLUMN_FREE_SPACE = ListTemplate::TABLE_WIDTH - self::COLUMN_WIDTH_KNOWN;

    /**
     * Column amounts for easy calculation.
     *
     * @var int
     */
    const AMOUNT_COLUMNS = 7;

    const AMOUNT_KNOWN_COLUMNS = 5;

    const AMOUNT_DYNAMIC_COLUMNS = self::AMOUNT_COLUMNS - self::AMOUNT_KNOWN_COLUMNS;

    /**
     * Width of dynamic columns.
     *
     * @var float
     */
    const DYNAMIC_COLUMN_WIDTH = self::COLUMN_FREE_SPACE / self::AMOUNT_DYNAMIC_COLUMNS;

    /**
     * Column definition for list tables. Must be defined in using classes.
     *
     * @see \Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\ListRenderTrait::$tableColumnDefinition
     *
     * @var array
     */
    protected array $tableColumnDefinition = [
        [
            'ident'       => 'image', //We use the named column feature of Table.
            'translation' => '',
            'width'       => ListTemplate::COLUMN_WIDTH_IMAGE,
            'rowStyle'    => ListTemplate::STYLE_TABLE_CELL_ROW_CENTER,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
            'cellStyle'   => '',
        ],
        [
            'ident'       => 'ean',
            'translation' => 'EAN',
            'width'       => ListTemplate::COLUMN_WIDTH_EAN,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'ident'       => 'name',
            'translation' => 'general.name',
            'width'       => self::DYNAMIC_COLUMN_WIDTH,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'ident'       => 'availableIn',
            'translation' => 'general.available-in',
            'width'       => self::DYNAMIC_COLUMN_WIDTH,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'ident'       => 'condition',
            'translation' => 'general.condition',
            'width'       => ListTemplate::COLUMN_WIDTH_CONDITION,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW_CENTER,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
        [
            'ident'       => 'mileage',
            'translation' => 'general.milage',
            'width'       => ListTemplate::COLUMN_WIDTH_MILEAGE,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_ROW_RIGHT,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD_CENTER,
        ],
        [
            'ident'       => 'price',
            'translation' => 'Price',
            'width'       => ListTemplate::COLUMN_WIDTH_PRICE,
            'cellStyle'   => ListTemplate::STYLE_TABLE_CELL_PRICE,
            'headStyle'   => ListTemplate::STYLE_TABLE_CELL_HEAD,
        ],
    ];

    /**
     * Returns the publication select options in the InDesign plugin for this project.
     *
     * @return array
     */
    public function getPublicationsTree(): array
    {
        return $this->publicationLoader->buildCarPublicationTree(
            Category::getByPath('/Product Data/Categories/products/spare parts')
        );
    }

    /**
     * Only categories with accessory parts assigned are rendered in the publication.
     *
     * @param Category $category
     *
     * @return bool
     */
    protected function hasCategoryRenderableElements(Category $category): bool
    {
        return !empty($this->loadAccessoryPartForCategory($category));
    }

    /**
     * Only categories with accessory parts assigned are rendered in the publication.
     *
     * @param Manufacturer $manufacturer
     *
     * @return bool
     */
    protected function hasManufacturerRenderableElements(Manufacturer $manufacturer): bool
    {
        return !empty($this->loadAccessoryPartForManufacturer($manufacturer));
    }

    /**
     * Renders Category headline and adds Category layout elements.
     *
     * @param Category $category
     *
     * @throws \Exception
     */
    protected function renderCategory(Category $category)
    {
        //Set boxIdentReference for content aware updates.
        $this->setBoxIdentReference($category->getId());

        $label = $category->getName();

        //Create the page layout for the category.
        $this->renderPageLayout($label);

        //A category always starts on a new page, because we have the category name on the page layout.
        $this->addCommand(new NextPage());
        $this->renderSubTitle($label, Variable::VARIABLE_Y_POSITION, AbstractTemplate::CONTENT_ORIGIN_TOP);

        //Build table structure.
        $this->initTable(Variable::VARIABLE_Y_POSITION);
        $this->setupTableStructure($this->tableColumnDefinition);

        //Add AccessoryPart to table.
        foreach ($this->loadAccessoryPartForCategory($category) as $accessoryPart) {
            $this->addTableRowAccessoryPart($accessoryPart);
        }

        //Add the Table with SplitTable to CommandQueue.
        $this->addSplitTable();
    }

    /**
     * Renders Manufacturer headline and adds Manufacturer layout elements.
     *
     * @param Manufacturer $manufacturer
     *
     * @throws \Exception
     */
    protected function renderManufacturer(Manufacturer $manufacturer)
    {
        //Set boxIdentReference for content aware updates.
        $this->setBoxIdentReference($manufacturer->getId());

        $label = $manufacturer->getName();
        $logo = $manufacturer->getLogo();

        //Create the page layout for the category.
        $this->renderPageLayout($label, $logo);

        //A manufacturer always starts on a new page, because we have the category name on the page layout.
        $this->addCommand(new NextPage());
        $this->renderSubTitle($label, Variable::VARIABLE_Y_POSITION, AbstractTemplate::CONTENT_ORIGIN_TOP);

        //Build table structure.
        $this->initTable();
        $this->setupTableStructure($this->tableColumnDefinition);

        //Add cars to table.
        foreach ($this->loadAccessoryPartForManufacturer($manufacturer) as $accessoryPart) {
            $this->addTableRowAccessoryPart($accessoryPart);
        }

        //Add the Table with SplitTable to CommandQueue.
        $this->addSplitTable();
    }

    /**
     * Adds $accessoryPart variant as row to the table.
     *
     * @param AccessoryPart $accessoryPart
     *
     * @throws \Exception
     */
    private function addTableRowAccessoryPart(AccessoryPart $accessoryPart)
    {
        $accessoryPart = AccessoryPartProduct::getById($accessoryPart->getId());
        $this->getTable()
             ->startRow();

        foreach ($this->buildCellContentForAccessoryPart($accessoryPart) as $ident => $content) {
            if (false === $this->getTable()
                               ->hasColumn($ident)) {
                continue;
            }
            $this->getTable()
                 ->addCell($content, $ident);
        }
    }

    /**
     * Builds cell contents for $accessoryPart.
     *
     * @param AccessoryPart $accessoryPart
     *
     * @return array
     * @throws \Exception
     */
    protected function buildCellContentForAccessoryPart(AccessoryPart $accessoryPart): array
    {
        $return = [];
        $image = $this->buildListImage($accessoryPart);
        if ($image instanceof Text) {
            $return['image'] = $image;
        }

        $return['ean'] = $accessoryPart->getErpNumber();
        $return['name'] = $accessoryPart->getGeneratedName();

        try {
            $salesInformation = $this->getSalesInformation($accessoryPart);
            $availability = $salesInformation->getAvailabilityType();
            if (false === empty($availability)) {
                $return['availableIn'] = $this->translator->trans(strtolower("attribute.$availability"));
            }
            $condition = $salesInformation->getCondition();
            if (false === empty($condition)) {
                $return['condition'] = $this->translator->trans(strtolower("attribute.$availability"));
            }
        } catch (\Exception $e) {
            //we simply don't fill the columns
        }
        $return['mileage'] = $this->getMileageFormatted($accessoryPart);
        $return['price'] = $this->getPriceEurFormatted($accessoryPart);

        return $return;
    }

    /**
     * Builds an Text element with $accessoryPart image for display in lists.
     *
     * @param AccessoryPart $accessoryPart
     *
     * @return Text|null
     * @throws \Exception
     * @throws FilesystemException
     */
    protected function buildListImage(AccessoryPart $accessoryPart): ?Text
    {
        $asset = $accessoryPart->getImage();
        if ($asset instanceof Hotspotimage) {
            $asset = $asset->getImage();
        }
        if (false === $asset instanceof Asset) {
            return null;
        }

        return $this->buildTableImageElement($asset);
    }

    /**
     * Loads published and viewable accessory parts assigned to $category.
     *
     * @param Category $category
     *
     * @return AccessoryPart[]
     */
    private function loadAccessoryPartForCategory(Category $category): array
    {
        $listing = $this->createAccessoryPartListing();
        $listing->addConditionParam(
            'mainCategory__id LIKE :categoryId',
            ['categoryId' => $category->getId()]
        );

        return $this->filterVisibility($listing->load());
    }

    /**
     * Loads published and viewable accessory parts assigned to $manufacturer.
     *
     * @param Manufacturer $manufacturer
     *
     * @return AccessoryPart[]
     */
    private function loadAccessoryPartForManufacturer(Manufacturer $manufacturer): array
    {
        $listing = $this->createAccessoryPartListing();
        $listing->addConditionParam(
            'manufacturer__id LIKE :manufacturerId',
            ['manufacturerId' => $manufacturer->getId()]
        );

        return $this->filterVisibility($listing->load());
    }

    /**
     * Creates basic AccessoryPart listing.
     *
     * @return AccessoryPartListing
     */
    private function createAccessoryPartListing(): AccessoryPartListing
    {
        $listing = new AccessoryPartListing();
        $listing->setUnpublished(false)
                ->setOrderKey('generatedName')
                ->setOrder('ASC');

        return $listing;
    }

    /**
     * Filters $parts for visibility for current user.
     *
     * @param array $parts
     *
     * @return array
     */
    private function filterVisibility(array $parts): array
    {
        $return = [];
        foreach ($parts as $part) {
            if (true === $part->isAllowed('view')) {
                $return[] = $part;
            }
        }

        return $return;
    }
}
