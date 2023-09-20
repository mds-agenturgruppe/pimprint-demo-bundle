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

use Mds\PimPrint\CoreBundle\Service\InDesign\AbstractPublicationTreeBuilder;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Pimcore\Security\User\UserLoader;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\AccessoryPart;
use Pimcore\Model\DataObject\Category;
use Pimcore\Model\DataObject\Folder;
use Pimcore\Model\DataObject\Manufacturer;

/**
 * Class DataPrintPublicationLoader
 *
 * @package Mds\PimPrint\DemoBundle\Service
 */
class DataPrintPublicationLoader extends AbstractPublicationTreeBuilder
{
    /**
     * Pimcore UserLoader
     *
     * @var UserLoader
     */
    private UserLoader $userLoader;

    /**
     * PimPrint PluginParameters.
     *
     * @var PluginParameters
     */
    private PluginParameters $pluginParameters;

    /**
     * Currently rendered publication.
     *
     * @var AbstractObject|null
     */
    private ?AbstractObject $renderedElement = null;

    /**
     * Lazy loading current user language
     *
     * @var string|null
     */
    private ?string $contentLangauge = null;

    /**
     * DataPrintPublicationLoader constructor.
     *
     * @param UserLoader       $userLoader
     * @param PluginParameters $pluginParameters
     */
    public function __construct(UserLoader $userLoader, PluginParameters $pluginParameters)
    {
        $this->userLoader = $userLoader;
        $this->pluginParameters = $pluginParameters;
    }

    /**
     * Builds publication tree for InDesign Plugin.
     *
     * DataPrint demo projects use:
     * - Category tree starting at $rootCategory ('cars' or 'spare parts')
     * - Manufacturer DataObjects
     *
     * @param Category|null $rootCategory
     *
     * @return array
     */
    public function buildCarPublicationTree(Category $rootCategory = null): array
    {
        $return = [];
        if ($rootCategory instanceof Category) {
            if ($this->showObjectInTree($rootCategory)) {
                $return[] = $this->buildObjectTree($rootCategory);
            }
        } else {
            $return[] = $this->buildTreeElement(
                'noCategory',
                'Starting category node not found in Database'
            );
        }
        $manufacturerTree[] = $this->buildManufacturerTree();

        return array_merge($return, $manufacturerTree);
    }

    /**
     * Returns in plugin selected publication for generation.
     * In DataPrint demo this can be Category or Manufacturer DataObjects.
     *
     * @internal This method is placed in PublicationLoader to be used in DataPrintCar and DataPrintAccessoryParts demo.
     *           In most projects this functionality is project service specific.
     *
     * @return Category|AccessoryPart|Manufacturer
     * @throws \Exception
     */
    public function getRenderedElement(): Category|AccessoryPart|Manufacturer
    {
        if (null ===$this->renderedElement) {
            $object = AbstractObject::getById($this->pluginParameters->get(PluginParameters::PARAM_PUBLICATION));
            if (false === $object instanceof AbstractObject) {
                throw new \Exception("Could not load object for rendering.");
            }
            if ((
                    false === $object instanceof Category
                    && false === $object instanceof Manufacturer
                    && false === $object instanceof Folder)
                || (method_exists($object, 'getPublished()') && false === $object->getPublished())
                || false === $object->isAllowed('view')) {
                throw new \Exception(
                    sprintf(
                        "Not possible to render Object '%s' (%s) in DataPrint Demo.",
                        $object->getKey(),
                        $object->getId()
                    )
                );
            }
            $this->renderedElement = $object;
        }

        return $this->renderedElement;
    }

    /**
     * Builds publication tree for Manufacturer DataObjects starting at path '/Product Data/Manufacturer'
     *
     * @return array
     */
    private function buildManufacturerTree(): array
    {
        $folder = Folder::getByPath('/Product Data/Manufacturer');
        if ($folder instanceof Folder) {
            if ($this->showObjectInTree($folder)) {
                return $this->buildObjectTree($folder);
            }
        } else {
            return $this->buildTreeElement(
                'noManufacturer',
                "Folder 'Manufacturer' not found in Database"
            );
        }

        return [];
    }

    /**
     * Example of overwriting the template method.
     * For categories, we want to use the Category name.
     *
     * @param AbstractObject $object
     *
     * @return string
     */
    protected function getObjectLabel(AbstractObject $object): string
    {
        if ($object instanceof Category) {
            $label = $object->getName($this->getContentLanguage());
            if (false === empty($label)) {
                return $label;
            }
        }

        return parent::getObjectLabel($object);
    }

    /**
     * Returns first content language from current user.
     * Language is used for i18n display of Category names in publicationTree.
     *
     * @return string
     */
    protected function getContentLanguage(): string
    {
        if (null == $this->contentLangauge) {
            $contentLanguages = $this->userLoader->getUser()
                                                 ->getContentLanguages();
            if (empty($contentLanguages)) {
                $this->contentLangauge = $this->userLoader->getUser()
                                                          ->getLanguage();
            } else {
                $this->contentLangauge = current($contentLanguages);
            }
        }

        return $this->contentLangauge;
    }
}
