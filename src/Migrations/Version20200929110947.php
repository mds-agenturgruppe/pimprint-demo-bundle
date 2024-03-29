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

namespace Mds\PimPrint\DemoBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractProject;
use Pimcore\Model\Property\Predefined;

/**
 * Adds predefined properties for PimPrint Demo.
 *
 * @package Mds\PimPrint\DemoBundle\Migrations
 */
class Version20200929110947 extends AbstractMigration
{
    /**
     * Properties to create.
     *
     * @var array
     */
    protected array $properties = [
        [
            "name"        => "PimPrint Template",
            "description" => "InDesign template file for PimPrint rendering",
            "key"         => AbstractProject::PROPERTY_TEMPLATE,
            "type"        => "asset",
            "ctype"       => "object",
            "inheritable" => true,
        ],
        [
            "name"        => "PimPrint Asset",
            "description" => "Alternative Asset for PimPrint rendering",
            "key"         => "pimprint_asset",
            "type"        => "asset",
            "ctype"       => "asset",
        ]
    ];

    /**
     * Up method.
     *
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        $this->addProperties($this->properties);
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
        $this->removeProperties($this->properties);
    }

    /**
     * Adds $properties to predefined properties.
     *
     * @param array $properties
     */
    private function addProperties(array $properties)
    {
        foreach ($properties as $definition) {
            $property = Predefined::getByKey($definition['key']);
            if ($property instanceof Predefined) {
                $this->write(sprintf("Predefined Property '%s' already exists.", $definition['key']));
                continue;
            }

            $property = new Predefined();
            $property->setValues($definition);
            $property->save();
            $this->write(sprintf("Predefined Property '%s' added.", $definition['key']));
        }
    }

    /**
     * Removes $properties from predefined properties.
     *
     * @param array $properties
     */
    private function removeProperties(array $properties)
    {
        foreach ($properties as $definition) {
            $property = Predefined::getByKey($definition['key']);
            if ($property instanceof Predefined) {
                $property->delete();
                $this->writeMessage(sprintf("Predefined Property '%s' removed.", $definition['key']));
            }
        }
    }
}
