<?php

/**
 * Copyright 2015-2020 info@neomerx.com
 * Modification Copyright 2021-2022 info@whoaphp.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Whoa\Passport\Package;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Contracts\Passport\PassportAccountManagerInterface;
use Whoa\Contracts\Settings\Packages\PassportSettingsInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Passport\Authentication\AccountManager;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Whoa\Passport\Contracts\PassportServerInterface;
use Whoa\Passport\Entities\DatabaseSchema;
use Whoa\Passport\Package\PassportSettings as C;
use Whoa\Passport\PassportServer;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @package Whoa\Passport
 */
abstract class BasePassportContainerConfigurator
{
    /**
     * @param WhoaContainerInterface $container
     * @return void
     */
    protected static function baseConfigureContainer(WhoaContainerInterface $container): void
    {
        $accountManager = null;
        $factory = function (
            PsrContainerInterface $container
        ) use (&$accountManager): PassportAccountManagerInterface {
            if ($accountManager === null) {
                $accountManager = new AccountManager($container);
                if (($logger = static::getLoggerIfEnabled($container)) !== null) {
                    $accountManager->setLogger($logger);
                }
            }

            return $accountManager;
        };
        $container[AccountManagerInterface::class] = $factory;
        $container[PassportAccountManagerInterface::class] = $factory;

        $container[DatabaseSchemaInterface::class] = function (
            PsrContainerInterface $container
        ): DatabaseSchemaInterface {
            $settings = $container->get(SettingsProviderInterface::class)->get(C::class);

            return new DatabaseSchema(
                $settings[PassportSettingsInterface::KEY_USER_TABLE_NAME],
                $settings[PassportSettingsInterface::KEY_USER_PRIMARY_KEY_NAME]
            );
        };

        $container[PassportServerInterface::class] = function (
            PsrContainerInterface $container
        ): PassportServerInterface {
            $integration = $container->get(PassportServerIntegrationInterface::class);
            $passportServer = new PassportServer($integration);

            if (($logger = static::getLoggerIfEnabled($container)) !== null) {
                $passportServer->setLogger($logger);
            }

            return $passportServer;
        };
    }

    /**
     * @param PsrContainerInterface $container
     *
     * @return null|LoggerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected static function getLoggerIfEnabled(PsrContainerInterface $container): ?LoggerInterface
    {
        $logger = null;
        $settings = $container->get(SettingsProviderInterface::class)->get(C::class);
        if ($settings[PassportSettingsInterface::KEY_IS_LOG_ENABLED] === true && $container->has(
                LoggerInterface::class
            ) === true) {
            $logger = $container->get(LoggerInterface::class);
        }

        return $logger;
    }
}
