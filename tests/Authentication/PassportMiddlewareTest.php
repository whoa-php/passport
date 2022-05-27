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

namespace Whoa\Tests\Passport\Authentication;

use Exception;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Whoa\Contracts\Passport\PassportAccountInterface;
use Whoa\Contracts\Passport\PassportAccountManagerInterface;
use Whoa\Contracts\Settings\SettingsProviderInterface;
use Whoa\Passport\Authentication\PassportMiddleware;
use Whoa\Passport\Exceptions\AuthenticationException;
use Whoa\Tests\Passport\Data\TestContainer;
use Whoa\Tests\Passport\Package\PassportContainerConfiguratorTest;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @package Whoa\Tests\Passport
 */
class PassportMiddlewareTest extends TestCase
{
    /**
     * Test handle.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHandleWithValidToken()
    {
        $token = 'abc123';
        $request = (new ServerRequest())->withHeader('Authorization', "Bearer $token");
        $nextCalled = false;
        $next = function () use (&$nextCalled) {
            $nextCalled = true;

            return new Response();
        };
        $container = new TestContainer();
        /** @var Mock $managerMock */
        $container[PassportAccountManagerInterface::class] = $managerMock =
            Mockery::mock(PassportAccountManagerInterface::class);
        $accountMock = Mockery::mock(PassportAccountInterface::class);
        $managerMock->shouldReceive('setAccountWithTokenValue')->once()->with($token)->andReturn($accountMock);

        PassportMiddleware::handle($request, $next, $container);

        $this->assertTrue($nextCalled);
    }

    /**
     * Test handle.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHandleWithMalformedToken()
    {
        $request = (new ServerRequest())->withHeader('Authorization', 'Bearer ');
        $nextCalled = false;
        $container = new TestContainer();
        $container[LoggerInterface::class] = new NullLogger();
        $container[SettingsProviderInterface::class] = PassportContainerConfiguratorTest::createSettingsProvider();
        $next = function () use (&$nextCalled) {
            $nextCalled = true;

            return new Response();
        };

        $response = PassportMiddleware::handle($request, $next, $container);

        $this->assertTrue($nextCalled);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test handle.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testHandleWithInvalidToken()
    {
        $request = (new ServerRequest())->withHeader('Authorization', 'Bearer XXX');
        $nextCalled = false;
        $container = new TestContainer();
        $container[LoggerInterface::class] = new NullLogger();
        $container[SettingsProviderInterface::class] = PassportContainerConfiguratorTest::createSettingsProvider();
        $next = function () use (&$nextCalled) {
            $nextCalled = true;

            return new Response();
        };
        /** @var Mock $managerMock */
        $container[PassportAccountManagerInterface::class] = $managerMock =
            Mockery::mock(PassportAccountManagerInterface::class);
        $managerMock->shouldReceive('setAccountWithTokenValue')
            ->once()->withAnyArgs()->andThrow(AuthenticationException::class);

        $response = PassportMiddleware::handle($request, $next, $container);

        $this->assertFalse($nextCalled);
        $this->assertEquals(401, $response->getStatusCode());
    }
}
