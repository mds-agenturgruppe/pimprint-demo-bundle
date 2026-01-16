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

namespace Mds\PimPrint\DemoBundle\Project\Traits;

use Faker\Factory;
use Faker\Generator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getFaker(): Generator
    {
        if (!isset($this->faker)) {
            $this->faker = Factory::create($this->getLanguage());
        }

        return $this->faker;
    }
}
