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

declare(strict_types=1);

namespace Mds\PimPrint\DemoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\AbstractCarsDemoProject;
use Mds\PimPrint\DemoBundle\Project\CarsDemo\CarSalesLabel;
use Pimcore\Model\Property\Predefined;
use Pimcore\Model\WebsiteSetting;

/**
 * Class Version20260109071852
 *
 * @package Mds\PimPrint\DemoBundle\Migrations
 */
final class Version20260109071852 extends AbstractMigration
{
    /**
     * WebsiteSettings to create.
     *
     * @var array
     */
    private array $settings = [
        AbstractCarsDemoProject::WEBSITE_SETTING_TEMPLATE_OVERWRITE,
        CarSalesLabel::WEBSITE_SETTING_TEMPLATE_OVERWRITE,
    ];

    /**
     * Predefined Properties to create.
     *
     * @var array
     */
    private array $properties = [
        [
            "name"        => "PimPrint Category Template",
            "description" => "InDesign CarsDemo template for Category",
            "key"         => AbstractCarsDemoProject::PROPERTY_TEMPLATE_OVERWRITE,
            "type"        => "asset",
            "ctype"       => "object",
            "inheritable" => true,
        ],
    ];

    /**
     * Returns the description of the migration.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'Create WebsiteSettings and PredefinedProperty for template overwrites';
    }

    /**
     * Up method.
     *
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->createSettings();
        $this->createProperties();
    }

    /**
     * Down method.
     *
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $this->removeSettings();
        $this->removeProperties();
    }

    /**
     * Creates WebsiteSettings.
     *
     * @return void
     */
    private function createSettings(): void
    {
        foreach ($this->settings as $name) {
            $setting = new WebsiteSetting();
            $setting->setName($name);
            $setting->setType('asset');
            $setting->setSiteId(null);

            $setting->save();
            $this->write('WebsiteSetting created: ' . $name);
        }
    }

    /**
     * Removes WebsiteSettings.
     *
     * @return void
     */
    private function removeSettings(): void
    {
        foreach ($this->settings as $name) {
            try {
                $setting = WebsiteSetting::getByName($name, null);
                if ($setting instanceof WebsiteSetting) {
                    $setting->delete();
                    $this->write('WebsiteSetting removed: ' . $name);
                }
            } catch (\Exception $exception) {
                $this->write($exception->getMessage());
            }
        }
    }

    /**
     * Creates PredefinedProperties.
     *
     * @return void
     */
    private function createProperties(): void
    {
        foreach ($this->properties as $definition) {
            $property = Predefined::getByKey($definition['key']);
            if ($property instanceof Predefined) {
                $this->write(sprintf("PredefinedProperty '%s' already exists.", $definition['key']));
                continue;
            }

            $property = new Predefined();
            $property->setValues($definition);
            $property->save();
            $this->write(sprintf("PredefinedProperty '%s' created.", $definition['key']));
        }
    }

    /**
     * Removes PredefinedProperties.
     *
     * @return void
     */
    private function removeProperties(): void
    {
        foreach ($this->properties as $definition) {
            $property = Predefined::getByKey($definition['key']);
            if ($property instanceof Predefined) {
                $property->delete();
                $this->write(sprintf("PredefinedProperty '%s' removed.", $definition['key']));
            }
        }
    }
}
