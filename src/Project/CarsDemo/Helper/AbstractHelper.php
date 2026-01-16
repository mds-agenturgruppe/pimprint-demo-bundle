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

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper;

use Mds\PimPrint\CoreBundle\Service\CommandQueue;
use Pimcore\Localization\IntlFormatter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractHelper
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Helper
 */
abstract class AbstractHelper implements ServiceSubscriberInterface
{
    use ServiceMethodsSubscriberTrait;

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public static function getSubscribedServices(): array
    {
        return [
            BoxGenerator::class        => BoxGenerator::class,
            IntlFormatter::class       => IntlFormatter::class,
            TranslatorInterface::class => TranslatorInterface::class,
            TranslationHelper::class   => TranslationHelper::class,
            CommandQueue::class        => CommandQueue::class,
        ];
    }

    /**
     * Returns BoxGenerator
     *
     * @return BoxGenerator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function boxGenerator(): BoxGenerator
    {
        return $this->container->get(BoxGenerator::class);
    }

    /**
     * Returns Pimcore Translator
     *
     * @return TranslatorInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function translator(): TranslatorInterface
    {
        return $this->container->get(TranslatorInterface::class);
    }

    /**
     * Returns TranslationHelper
     *
     * @return TranslationHelper
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function translationHelper(): TranslationHelper
    {
        return $this->container->get(TranslationHelper::class);
    }

    /**
     * Returns Pimcore IntlFormatter
     *
     * @return IntlFormatter
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function intlFormatter(): IntlFormatter
    {
        return $this->container->get(IntlFormatter::class);
    }

    /**
     * Returns PimPrint CommandQueue
     *
     * @return CommandQueue
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function commandQueue(): CommandQueue
    {
        return $this->container->get(CommandQueue::class);
    }
}
