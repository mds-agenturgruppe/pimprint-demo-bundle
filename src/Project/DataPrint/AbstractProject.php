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

namespace Mds\PimPrint\DemoBundle\Project\DataPrint;

use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentTemplateSetup;
use Mds\PimPrint\CoreBundle\Project\RenderingProject;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\CommonElementsTrait;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\ElementCollectorTrait;
use Mds\PimPrint\DemoBundle\Project\DataPrint\Traits\PageLayoutTrait;
use Mds\PimPrint\DemoBundle\Service\DataPrintPublicationLoader;
use Pimcore\Localization\IntlFormatter;
use Pimcore\Model\Asset;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractProject
 *
 * @package Mds\PimPrint\DemoBundle\Project\DataPrint
 */
abstract class AbstractProject extends RenderingProject
{
    use ElementCollectorTrait;
    use CommonElementsTrait;
    use PageLayoutTrait;

    /**
     * Predefined property for custom InDesign template file.
     *
     * @var string
     */
    const PROPERTY_TEMPLATE = 'pimprint_template';

    /**
     * Constant for current publication type Category.
     *
     * @var string
     */
    const TYPE_CATEGORY = 'category';

    /**
     * Constant for current publication type Manufacturer.
     *
     * @var string
     */
    const TYPE_MANUFACTURER = 'manufacturer';

    /**
     * Indicates current generated type of publication.
     * Some minimal layout changes are dependent this value.
     *
     * @var string
     */
    protected $currentType;

    /**
     * PublicationLoader for DataPrint projects.
     *
     * @var DataPrintPublicationLoader
     */
    protected DataPrintPublicationLoader $publicationLoader;

    /**
     * Pimcore website translator.
     *
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * Pimcore Intl Formatter.
     *
     * @var IntlFormatter
     */
    protected IntlFormatter $intlFormatter;

    /**
     * DataPrintCarBrochure constructor.
     *
     * @param DataPrintPublicationLoader $publicationLoader
     * @param TranslatorInterface        $translator
     * @param IntlFormatter              $intlFormatter
     */
    public function __construct(
        DataPrintPublicationLoader $publicationLoader,
        TranslatorInterface $translator,
        IntlFormatter $intlFormatter
    ) {
        $this->publicationLoader = $publicationLoader;
        $this->translator = $translator;
        $this->intlFormatter = $intlFormatter;
    }

    /**
     * Generates InDesign Commands to build the selected publication in InDesign.
     *
     * @return void
     * @throws \Exception
     */
    public function buildPublication(): void
    {
        $this->startRendering(false);
        $this->setDocumentSettings();

        $this->startPages();
        $this->renderPages(
            $this->publicationLoader->getRenderedElement()
        );
        $this->stopRendering();
    }

    /**
     * Sets default demo page settings
     *
     * @return void
     * @throws \Exception
     */
    private function setDocumentSettings(): void
    {
//        We transfer the document settings from the template file.
        $command = new DocumentTemplateSetup();
//        Facing pages is used from the manually created document to have the demos
//        work with or without facing pages to demonstrate the dynamic facing page layout creation.
        $command->setFacingPages(false);
        $this->addCommand($command);

//        We set the number of pages to 50, to have enough pages in the document for all command demos.
//        Empty pages will be removed at the end of the rendering.
        $command = new DocumentSetup(null, 50);
        $this->addCommand($command);
    }

    /**
     * Loads template file (asset) from element property.
     * If element has no property, the default template from project configuration is used.
     *
     * @return Asset|string
     * @throws \Exception
     */
    protected function getTemplate(): Asset|string
    {
        $element = $this->publicationLoader->getRenderedElement();
        $template = $element->getProperty(self::PROPERTY_TEMPLATE);
        if ($template instanceof Asset) {
            return $template;
        }

        return parent::getTemplate();
    }
}
