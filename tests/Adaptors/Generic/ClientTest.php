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

namespace Whoa\Tests\Passport\Adaptors\Generic;

use Exception;
use Whoa\Passport\Adaptors\Generic\Client;
use PHPUnit\Framework\TestCase;

/**
 * @package Whoa\Tests\Passport
 */
class ClientTest extends TestCase
{
    /**
     * Test date format method implemented.
     *
     * @throws Exception
     */
    public function testDbDateFormat()
    {
        $client = new Client();
        $this->assertNull($client->getUpdatedAt());
        $client->{Client::FIELD_UPDATED_AT} = '2001-02-03 04:05:06';
        $this->assertNotNull($client->getUpdatedAt());
    }
}
