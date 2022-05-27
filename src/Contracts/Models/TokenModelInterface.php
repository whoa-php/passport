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
interface TokenModelInterface
{
    /** @var string Table name */
    public const TABLE_NAME = 'oauth_tokens';

    /** @var string Primary key */
    public const FIELD_ID = 'id_token';

    /** @var string Foreign key */
    public const FIELD_ID_CLIENT = ClientModelInterface::FIELD_ID;

    /** @var string Foreign key */
    public const FIELD_ID_USER = 'id_user';

    /** @var string Field name */
    public const FIELD_IS_SCOPE_MODIFIED = 'is_scope_modified';

    /** @var string Field name */
    public const FIELD_IS_ENABLED = 'is_enabled';

    /** @var string Field name */
    public const FIELD_REDIRECT_URI = 'redirect_uri';

    /** @var string Field name */
    public const FIELD_CODE = 'code';

    /** @var string Field name */
    public const FIELD_VALUE = 'value';

    /** @var string Field name */
    public const FIELD_TYPE = 'type';

    /** @var string Field name */
    public const FIELD_REFRESH = 'refresh';

    /** @var string Field name */
    public const FIELD_CODE_CREATED_AT = 'code_created_at';

    /** @var string Field name */
    public const FIELD_VALUE_CREATED_AT = 'value_created_at';

    /** @var string Field name */
    public const FIELD_REFRESH_CREATED_AT = 'refresh_created_at';

    /** @var string Relationship name */
    public const REL_USER = 'user';

    /** @var string Relationship name */
    public const REL_CLIENT = 'client';

    /** @var string Relationship name */
    public const REL_SCOPES = 'scopes';
}
