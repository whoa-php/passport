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

use Doctrine\DBAL\Connection;
use Whoa\Contracts\Application\ContainerConfiguratorInterface;
use Whoa\Contracts\Container\ContainerInterface as WhoaContainerInterface;
use Whoa\Passport\Adaptors\Generic\PassportServerIntegration;
use Whoa\Passport\Adaptors\Generic\TokenRepository;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * @package Whoa\Passport
 */
class PassportContainerConfigurator extends BasePassportContainerConfigurator implements ContainerConfiguratorInterface
{
    /** @var callable Configurator method name */
    public const CONFIGURATOR = [self::class, self::CONTAINER_METHOD_NAME];

    /**
     * @inheritdoc
     */
    public static function configureContainer(WhoaContainerInterface $container): void
    {
        static::baseConfigureContainer($container);

        $container[PassportServerIntegrationInterface::class] = function (
            PsrContainerInterface $container
        ): PassportServerIntegrationInterface {
            return new PassportServerIntegration($container);
        };

        $container[TokenRepositoryInterface::class] = function (
            PsrContainerInterface $container
        ): TokenRepositoryInterface {
            $connection = $container->get(Connection::class);
            $schema = $container->get(DatabaseSchemaInterface::class);

            return new TokenRepository($connection, $schema);
        };
    }
}
