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
interface ClientModelInterface
{
    /** @var string Table name */
    public const TABLE_NAME = 'oauth_clients';

    /** @var string Primary key */
    public const FIELD_ID = 'id_client';

    /** @var string Field name */
    public const FIELD_IDENTIFIER = 'identifier';

    /** @var string Field name */
    public const FIELD_NAME = 'name';

    /** @var string Field name */
    public const FIELD_DESCRIPTION = 'description';

    /** @var string Field name */
    public const FIELD_CREDENTIALS = 'credentials';

    /** @var string Field name */
    public const FIELD_IS_CONFIDENTIAL = 'is_confidential';

    /** @var string Field name */
    public const FIELD_IS_SCOPE_EXCESS_ALLOWED = 'is_scope_excess_allowed';

    /** @var string Field name */
    public const FIELD_IS_USE_DEFAULT_SCOPE = 'is_use_default_scope';

    /** @var string Field name */
    public const FIELD_IS_CODE_GRANT_ENABLED = 'is_code_grant_enabled';

    /** @var string Field name */
    public const FIELD_IS_IMPLICIT_GRANT_ENABLED = 'is_implicit_grant_enabled';

    /** @var string Field name */
    public const FIELD_IS_PASSWORD_GRANT_ENABLED = 'is_password_grant_enabled';

    /** @var string Field name */
    public const FIELD_IS_CLIENT_GRANT_ENABLED = 'is_client_grant_enabled';

    /** @var string Field name */
    public const FIELD_IS_REFRESH_GRANT_ENABLED = 'is_refresh_grant_enabled';

    /** @var string Relationship name */
    public const REL_REDIRECT_URIS = 'redirect_uris';

    /** @var string Relationship name */
    public const REL_SCOPES = 'scopes';

    /** @var string Relationship name */
    public const REL_TOKENS = 'tokens';
}
