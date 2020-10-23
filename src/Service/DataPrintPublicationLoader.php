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
use Mds\PimPrint\CoreBundle\Service\UserHelper;
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
     * PimPrint UserHelper.
     *
     * @var UserHelper
     */
    private $userHelper;

    /**
     * PimPrint PluginParameters.
     *
     * @var PluginParameters
     */
    private $pluginParameters;

    /**
     * Currently rendered publication.
     *
     * @var Category|Manufacturer|AccessoryPart
     */
    private $renderedElement;

    /**
     * DataPrintPublicationLoader constructor.
     *
     * @param UserHelper       $userHelper
     * @param PluginParameters $pluginParameters
     */
    public function __construct(UserHelper $userHelper, PluginParameters $pluginParameters)
    {
        $this->userHelper = $userHelper;
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
    public function buildCarPublicationTree(Category $rootCategory = null)
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
     * @return Category|Manufacturer
     * @throws \Exception
     */
    public function getRenderedElement()
    {
        if (null === $this->renderedElement) {
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
    private function buildManufacturerTree()
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
     * For categories we want to use the Category name.
     *
     * @param AbstractObject $object
     *
     * @return string
     */
    protected function getObjectLabel(AbstractObject $object)
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
     * For demonstration purpose we use the PimPrint UserHelper to access the current user
     * to return the first content language. Language is used for i18n display of Category names in publicationTree.
     *
     * @return string
     */
    protected function getContentLanguage()
    {
        return $this->userHelper->getUser()
                                ->getContentLanguages()[0];
    }
}
