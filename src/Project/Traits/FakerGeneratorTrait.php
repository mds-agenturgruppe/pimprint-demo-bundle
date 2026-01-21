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
