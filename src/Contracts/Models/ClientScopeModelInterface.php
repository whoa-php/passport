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
interface ClientScopeModelInterface
{
    /** @var string Table name */
    public const TABLE_NAME = 'oauth_clients_scopes';

    /** @var string Primary key */
    public const FIELD_ID = 'id_client_scope';

    /** @var string Foreign key */
    public const FIELD_ID_CLIENT = ClientModelInterface::FIELD_ID;

    /** @var string Foreign key */
    public const FIELD_ID_SCOPE = ScopeModelInterface::FIELD_ID;
}
