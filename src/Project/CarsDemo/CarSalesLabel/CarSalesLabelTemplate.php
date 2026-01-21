<?php
/**
 * mds. Agenturgruppe GmbH
 *
 * This source file is available under the terms of the
 * mds. Commercial License (MCL)
 *
 * Full copyright and license information is available in
 * LICENSE.md, which is distributed with this source code.
 *
 * @copyright Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license   mds. Commercial License (MCL)
 */

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel;

use Mds\PimPrint\CoreBundle\InDesign\Command\CopyBox;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\Template;
use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4LandscapeTemplate;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\Template\PageLayoutInterface;

/**
 * Class CarSalesLabelTemplate
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel
 */
class CarSalesLabelTemplate extends A4LandscapeTemplate implements PageLayoutInterface
{
    /**
     * Best practice
     *
     * @see \Mds\PimPrint\CoreBundle\InDesign\Template\BestPractices\ContentSizesPositions
     */
    const CONTENT_WIDTH = self::PAGE_WIDTH - self::PAGE_MARGIN_LEFT - self::PAGE_MARGIN_RIGHT;

    const CONTENT_HEIGHT = self::PAGE_HEIGHT - self::PAGE_MARGIN_TOP - self::PAGE_MARGIN_BOTTOM;

    /**
     * Variable is used for automatic pagination with CheckPage
     */
    const CONTENT_ORIGIN_TOP = self::PAGE_MARGIN_TOP;

    const CONTENT_ORIGIN_LEFT = self::PAGE_MARGIN_LEFT;

    const CONTENT_BOTTOM = self::PAGE_HEIGHT - self::PAGE_MARGIN_BOTTOM;

    const CONTENT_RIGHT = self::PAGE_WIDTH - self::PAGE_MARGIN_RIGHT;

    /**
     * CarSalesLabelDemo InDesign Template file margins
     */
    const PAGE_MARGIN_TOP = 10;

    /**
     * InDesign element names from the CarsDemo template file used for rendering.
     */
    const ELEMENT_IMAGE = 'image';

    /**
     * InDesign style names
     */
    const STYLE_PARAGRAPH_TITLE = 'Title';

    const STYLE_PARAGRAPH_PRICE = 'Price';

    const STYLE_PARAGRAPH_SUBHEADLINE = 'Subheadline';

    const STYLE_PARAGRAPH_TAX_INFO = 'Tax info';

    const STYLE_PARAGRAPH_TABLE_COPY_BIG_LEFT = 'Table copy big left';

    const STYLE_PARAGRAPH_TABLE_COPY_BIG_RIGHT = 'Table copy big right';

    const STYLE_PARAGRAPH_TABLE_COPY_SMALL_LEFT = 'Table copy small left';

    const STYLE_PARAGRAPH_TABLE_COPY_SMALL_RIGHT = 'Table copy small right';

    const STYLE_TABLE_TECHNICAL = 'Technical';

    const STYLE_TABLE_INFORMATION = 'Information';

    const STYLE_TABLE_CELL_SMALL_TABLE_HEADER = 'Small table header';

    const STYLE_TABLE_CELL_SMALL_ROWS_RIGHT = 'Small rows right';

    const STYLE_TABLE_CELL_BIG_ROWS_LEFT = 'Big rows left';

    const STYLE_TABLE_CELL_BIG_ROWS_RIGHT = 'Big rows right';

    /**
     * Misc size and positions
     */
    const TITLE_Y_POSITION = 14;

    const MANUFACTURER_LOGO_MAX_WIDTH = 80;

    const MANUFACTURER_LOGO_HEIGHT = 25;

    const PRODUCTION_DETAILS_Y_POSITION = 75;

    const PRODUCTION_DETAILS_FRIST_WIDTH = 34.4;

    const PRODUCTION_DETAILS_SECOND_WIDTH = 51;

    const PRODUCTION_DETAILS_WIDTH = self::PRODUCTION_DETAILS_FRIST_WIDTH + self::PRODUCTION_DETAILS_SECOND_WIDTH;

    const PRODUCTION_DETAILS_HEIGHT = self::PRODUCTION_DETAILS_ROW_HEIGHT * 5;

    const PRODUCTION_DETAILS_ROW_HEIGHT = 15;

    const GENERAL_DETAILS_X_POSITION = 105.667;

    const GENERAL_DETAILS_HEIGHT = self::PRODUCTION_DETAILS_HEIGHT * 4;

    const TECHNICAL_DETAILS_X_POSITION = 208.292;

    const TECHNICAL_DETAILS_Y_POSITION = 64.937;

    const DIMENSION_DETAILS_FIRST_WIDTH = 32;

    const DIMENSION_DETAILS_SECOND_WIDTH = 41;

    const DIMENSION_DETAILS_WIDTH = self::DIMENSION_DETAILS_FIRST_WIDTH + self::DIMENSION_DETAILS_SECOND_WIDTH;

    const DIMENSION_DETAILS_HEIGHT = 3 * self::DIMENSION_DETAILS_ROW_HEIGHT + self::DIMENSION_DETAILS_HEADER_HEIGHT;

    const DIMENSION_DETAILS_ROW_HEIGHT = 12;

    const DIMENSION_DETAILS_HEADER_HEIGHT = 7;

    const ENGINE_DETAILS_Y_POSITION = 126.088;

    const ENGINE_DETAILS_HEIGHT = 5 * self::DIMENSION_DETAILS_ROW_HEIGHT + self::DIMENSION_DETAILS_HEADER_HEIGHT;

    const PRICE_Y_POSITION = 170;

    const TAX_Y_POSITION = 196.582;

    /**
     * Creates all page layout elements.
     *
     * @param string|null $chapterName
     *
     * @return Template
     * @throws \Exception
     */
    public function getPageLayoutCommands(?string $chapterName): Template
    {
        $template = new Template();

        $layer = new SetLayer('Layout');
        $template->addCommand($layer);

        $copyBox = new CopyBox('header');
        $copyBox->setUseTemplatePosition(true);
        $template->addCommand($copyBox);

        $copyBox = new CopyBox('checker');
        $copyBox->setUseTemplatePosition(true);
        $template->addCommand($copyBox);

        $copyBox = new CopyBox('border');
        $copyBox->setUseTemplatePosition(true);
        $template->addCommand($copyBox);

        return $template;
    }
}
