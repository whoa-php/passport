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

use Exception;
use Whoa\Passport\Contracts\PassportServerInterface;
use Whoa\Passport\Package\PassportController;
use Whoa\Tests\Passport\Data\TestContainer;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * @package Whoa\Tests\Templates
 */
class PassportControllerTest extends TestCase
{
    /**
     * Test authorize.
     *
     * @throws Exception
     */
    public function testAuthorize()
    {
        $request  = new ServerRequest();
        $response = new Response();

        $container                                 = new TestContainer();
        $container[PassportServerInterface::class] = $serverMock = Mockery::mock(PassportServerInterface::class);
        /** @var Mock $serverMock */
        $serverMock->shouldReceive('getCreateAuthorization')->once()->with($request)->andReturn($response);

        $this->assertSame($response, PassportController::authorize([], $container, $request));
    }

    /**
     * Test authorize.
     *
     * @throws Exception
     */
    public function testToken()
    {
        $request  = new ServerRequest();
        $response = new Response();

        $container                                 = new TestContainer();
        $container[PassportServerInterface::class] = $serverMock = Mockery::mock(PassportServerInterface::class);
        /** @var Mock $serverMock */
        $serverMock->shouldReceive('postCreateToken')->once()->with($request)->andReturn($response);

        $this->assertSame($response, PassportController::token([], $container, $request));
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }
}
