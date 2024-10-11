<?php
/**
 * mds Agenturgruppe GmbH
 *
 * This source file is licensed under GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 */

namespace Mds\PimPrint\DemoBundle\Project\Traits;

use Faker\Factory;
use Faker\Generator;

/**
 * Trait FakerGeneratorTrait
 *
 * @package Mds\PimPrint\DemoBundle\Project\Traits
 */
trait FakerGeneratorTrait
{
    /**
     * Faker generator instance
     *
     * @var Generator
     */
    private Generator $faker;

    /**
     * Returns Faker Generator
     *
     * @return Generator
     * @throws \Exception
     */
    private function getFaker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create($this->getLanguage());
        }

        return $this->faker;
    }
}
