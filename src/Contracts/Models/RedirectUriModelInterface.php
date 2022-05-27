<?php

/**
 * Copyright 2021-2022 info@whoaphp.com
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

namespace Whoa\Passport\Contracts\Models;

/**
 * @package Whoa\Passport
 */
interface RedirectUriModelInterface
{
    /** @var string Table name */
    public const TABLE_NAME = 'oauth_redirect_uris';

    /** @var string Primary key */
    public const FIELD_ID = 'id_redirect_uri';

    /** @var string Foreign key */
    public const FIELD_ID_CLIENT = ClientModelInterface::FIELD_ID;

    /** @var string Field name */
    public const FIELD_VALUE = 'value';

    /** @var string Relationship name */
    public const REL_CLIENT = 'client';
}
