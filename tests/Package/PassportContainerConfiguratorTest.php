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

namespace Whoa\Tests\Passport\Package;

use Doctrine\DBAL\Connection;
use Exception;
use Whoa\Contracts\Authentication\AccountManagerInterface;
use Whoa\Contracts\Passport\PassportAccountManagerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Passport\Contracts\Entities\DatabaseSchemaInterface;
use Whoa\Passport\Contracts\Entities\TokenInterface;
use Whoa\Passport\Contracts\PassportServerIntegrationInterface;
use Whoa\Passport\Contracts\PassportServerInterface;
use Whoa\Passport\Contracts\Repositories\TokenRepositoryInterface;
use Whoa\Passport\Package\MySqlPassportContainerConfigurator;
use Whoa\Passport\Package\PassportContainerConfigurator;
use Whoa\Passport\Package\PassportSettings as C;
use Whoa\Passport\Package\PostgreSqlPassportContainerConfigurator;
use Whoa\Tests\Passport\Data\TestContainer;
use Whoa\Tests\Passport\PassportServerTest;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Whoa\Tests\Passport\Package\PassportContainerConfiguratorTest as T;

/**
 * @package Whoa\Tests\Templates
 */
class PassportContainerConfiguratorTest extends TestCase
{
    const TEST_DEFAULT_CLIENT_ID = 'default_client';
    const TEST_ERROR_URI = 'http://example.app/auth_request_error';
    const TEST_APPROVAL_URI = 'http://example.app/resource_owner_approval';

    /**
     * Test container configurator.
     *
     * @throws Exception
     */
    public function testGenericContainerConfigurator()
    {
        $container                                   = new TestContainer();
        $container[SettingsProviderInterface::class] = $this->createSettingsProvider();
        $container[Connection::class]                = Mockery::mock(Connection::class);
        $container[LoggerInterface::class]           = new NullLogger();

        PassportContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(AccountManagerInterface::class));
        $this->assertNotNull($container->get(PassportAccountManagerInterface::class));
        $this->assertNotNull($container->get(DatabaseSchemaInterface::class));
        $this->assertNotNull($container->get(PassportServerInterface::class));
        $this->assertNotNull($container->get(TokenRepositoryInterface::class));
        /** @var PassportServerIntegrationInterface $integration */
        $this->assertNotNull($integration = $container->get(PassportServerIntegrationInterface::class));
        $this->assertNotNull($userId = $integration->validateUserId(
            PassportServerTest::TEST_USER_NAME, PassportServerTest::TEST_USER_PASSWORD
        ));

        $integration->verifyAllowedUserScope($userId, []);
    }

    /**
     * Test container configurator.
     *
     * @throws Exception
     */
    public function testGenericContainerConfigurator1()
    {
        $container                                   = new TestContainer();
        $container[SettingsProviderInterface::class] = $this->createSettingsProvider();
        $container[Connection::class]                = Mockery::mock(Connection::class);
        $container[LoggerInterface::class]           = new NullLogger();

        PassportContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(AccountManagerInterface::class));
        $this->assertNotNull($container->get(PassportAccountManagerInterface::class));
        $this->assertNotNull($container->get(DatabaseSchemaInterface::class));
        $this->assertNotNull($container->get(PassportServerInterface::class));
        $this->assertNotNull($container->get(TokenRepositoryInterface::class));
        /** @var PassportServerIntegrationInterface $integration */
        $this->assertNotNull($integration = $container->get(PassportServerIntegrationInterface::class));
        $this->assertNotNull($userId = $integration->validateUserId(
            PassportServerTest::TEST_USER_NAME,
            PassportServerTest::TEST_USER_PASSWORD,
            PassportServerTest::TEST_EXTRAS
        ));

        $integration->verifyAllowedUserScope($userId, []);
    }

    /**
     * Test container configurator.
     *
     * @throws Exception
     */
    public function testMySqlContainerConfigurator()
    {
        $container                                   = new TestContainer();
        $container[SettingsProviderInterface::class] = $this->createSettingsProvider();
        $container[Connection::class]                = Mockery::mock(Connection::class);
        $container[LoggerInterface::class]           = new NullLogger();

        MySqlPassportContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(TokenRepositoryInterface::class));
        /** @var PassportServerIntegrationInterface $integration */
        $this->assertNotNull($integration = $container->get(PassportServerIntegrationInterface::class));
        $userId = $integration->validateUserId(
            PassportServerTest::TEST_USER_NAME, PassportServerTest::TEST_USER_PASSWORD
        );
        $this->assertTrue(is_int($userId));
        $integration->verifyAllowedUserScope($userId, []);
    }

    /**
     * Test container configurator.
     *
     * @throws Exception
     */
    public function testMySqlContainerConfigurator1()
    {
        $container                                   = new TestContainer();
        $container[SettingsProviderInterface::class] = $this->createSettingsProvider();
        $container[Connection::class]                = Mockery::mock(Connection::class);
        $container[LoggerInterface::class]           = new NullLogger();

        MySqlPassportContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(TokenRepositoryInterface::class));
        /** @var PassportServerIntegrationInterface $integration */
        $this->assertNotNull($integration = $container->get(PassportServerIntegrationInterface::class));
        $userId = $integration->validateUserId(
            PassportServerTest::TEST_USER_NAME,
            PassportServerTest::TEST_USER_PASSWORD,
            PassportServerTest::TEST_EXTRAS
        );
        $this->assertTrue(is_int($userId));
        $integration->verifyAllowedUserScope($userId, []);
    }

    /**
     * Test container configurator.
     *
     * @throws Exception
     */
    public function testPostgreSqlContainerConfigurator()
    {
        $container                                   = new TestContainer();
        $container[SettingsProviderInterface::class] = $this->createSettingsProvider();
        $container[Connection::class]                = Mockery::mock(Connection::class);
        $container[LoggerInterface::class]           = new NullLogger();

        PostgreSqlPassportContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(TokenRepositoryInterface::class));
        /** @var PassportServerIntegrationInterface $integration */
        $this->assertNotNull($integration = $container->get(PassportServerIntegrationInterface::class));
        $userId = $integration->validateUserId(
            PassportServerTest::TEST_USER_NAME, PassportServerTest::TEST_USER_PASSWORD
        );
        $this->assertTrue(is_int($userId));
        $integration->verifyAllowedUserScope($userId, []);
    }

    /**
     * Test container configurator.
     *
     * @throws Exception
     */
    public function testPostgreSqlContainerConfigurator1()
    {
        $container                                   = new TestContainer();
        $container[SettingsProviderInterface::class] = $this->createSettingsProvider();
        $container[Connection::class]                = Mockery::mock(Connection::class);
        $container[LoggerInterface::class]           = new NullLogger();

        PostgreSqlPassportContainerConfigurator::configureContainer($container);

        $this->assertNotNull($container->get(TokenRepositoryInterface::class));
        /** @var PassportServerIntegrationInterface $integration */
        $this->assertNotNull($integration = $container->get(PassportServerIntegrationInterface::class));
        $userId = $integration->validateUserId(
            PassportServerTest::TEST_USER_NAME,
            PassportServerTest::TEST_USER_PASSWORD,
            PassportServerTest::TEST_EXTRAS
        );
        $this->assertTrue(is_int($userId));
        $integration->verifyAllowedUserScope($userId, []);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    /**
     * @return SettingsProviderInterface
     */
    public static function createSettingsProvider(): SettingsProviderInterface
    {
        return new class implements SettingsProviderInterface {
            private $values = [
                C::class => [
                    C::KEY_IS_LOG_ENABLED                       => true,
                    C::KEY_USER_TABLE_NAME                      => 'users',
                    C::KEY_USER_PRIMARY_KEY_NAME                => 'id_user',
                    C::KEY_DEFAULT_CLIENT_ID                    => T::TEST_DEFAULT_CLIENT_ID,
                    C::KEY_APPROVAL_URI_STRING                  => T::TEST_APPROVAL_URI,
                    C::KEY_ERROR_URI_STRING                     => T::TEST_ERROR_URI,
                    C::KEY_CODE_EXPIRATION_TIME_IN_SECONDS      => 3600,
                    C::KEY_TOKEN_EXPIRATION_TIME_IN_SECONDS     => 3600,
                    C::KEY_RENEW_REFRESH_VALUE_ON_TOKEN_REFRESH => true,
                    C::KEY_USER_CREDENTIALS_VALIDATOR           => [T::class, 'userValidator'],
                    C::KEY_USER_SCOPE_VALIDATOR                 => [T::class, 'scopeValidator'],
                    C::KEY_TOKEN_CUSTOM_PROPERTIES_PROVIDER     => [T::class, 'tokenCustomPropertiesProvider'],
                ],
            ];

            /**
             * @inheritdoc
             */
            public function has(string $className): bool
            {
                return array_key_exists($className, $this->values);
            }

            /**
             * @inheritdoc
             */
            public function get(string $className): array
            {
                return $this->values[$className];
            }
        };
    }

    /**
     * @param ContainerInterface $container
     * @param string             $userName
     * @param string             $password
     *
     * @return int|null
     */
    public static function userValidator(ContainerInterface $container, string $userName, string $password)
    {
        assert($container !== null);

        $credOk =
            PassportServerTest::TEST_USER_NAME === $userName &&
            PassportServerTest::TEST_DEFAULT_CLIENT_PASS === $password;

        return $credOk ? PassportServerTest::TEST_USER_ID : null;
    }

    /**
     * @param ContainerInterface $container
     * @param                    $userId
     * @param array              $scope
     *
     * @return array|null
     */
    public static function scopeValidator(ContainerInterface $container, $userId, array $scope)
    {
        assert($container !== null);
        assert($userId !== null);

        return $scope;
    }

    /**
     * @param ContainerInterface $container
     * @param TokenInterface     $token
     *
     * @return array
     */
    public static function tokenCustomPropertiesProvider(ContainerInterface $container, TokenInterface $token): array
    {
        assert($container !== null);

        return [
            'user_id' => $token->getUserIdentifier(),
        ];
    }
}
