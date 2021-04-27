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

namespace Whoa\Passport\Models;

use Doctrine\DBAL\Types\Types;
use Whoa\Contracts\Application\ModelInterface;
use Whoa\Contracts\Data\RelationshipTypes;
use Whoa\Contracts\Data\TimestampFields;
use Whoa\Contracts\Data\UuidFields;
use Whoa\Doctrine\Types\DateTimeType;
use Whoa\Doctrine\Types\UuidType;

/**
 * @package Whoa\Passport
 */
class Client implements ModelInterface, UuidFields, TimestampFields
{
    /** @var string Table name */
    const TABLE_NAME = 'oauth_clients';

    /** @var string Primary key */
    const FIELD_ID = 'id_client';

    /** @var string Field name */
    const FIELD_NAME = 'name';

    /** @var string Field name */
    const FIELD_DESCRIPTION = 'description';

    /** @var string Field name */
    const FIELD_CREDENTIALS = 'credentials';

    /** @var string Field name */
    const FIELD_IS_CONFIDENTIAL = 'is_confidential';

    /** @var string Field name */
    const FIELD_IS_SCOPE_EXCESS_ALLOWED = 'is_scope_excess_allowed';

    /** @var string Field name */
    const FIELD_IS_USE_DEFAULT_SCOPE = 'is_use_default_scope';

    /** @var string Field name */
    const FIELD_IS_CODE_GRANT_ENABLED = 'is_code_grant_enabled';

    /** @var string Field name */
    const FIELD_IS_IMPLICIT_GRANT_ENABLED = 'is_implicit_grant_enabled';

    /** @var string Field name */
    const FIELD_IS_PASSWORD_GRANT_ENABLED = 'is_password_grant_enabled';

    /** @var string Field name */
    const FIELD_IS_CLIENT_GRANT_ENABLED = 'is_client_grant_enabled';

    /** @var string Field name */
    const FIELD_IS_REFRESH_GRANT_ENABLED = 'is_refresh_grant_enabled';

    /** @var string Relationship name */
    const REL_REDIRECT_URIS = 'redirect_uris';

    /** @var string Relationship name */
    const REL_SCOPES = 'scopes';

    /** @var string Relationship name */
    const REL_TOKENS = 'tokens';

    /**
     * @inheritDoc
     */
    public static function getTableName(): string
    {
        return static::TABLE_NAME;
    }

    /**
     * @inheritDoc
     */
    public static function getPrimaryKeyName(): string
    {
        return static::FIELD_ID;
    }

    /**
     * @inheritDoc
     */
    public static function getAttributeTypes(): array
    {
        return [
            self::FIELD_ID                        => Types::STRING,
            self::FIELD_UUID                      => UuidType::NAME,
            self::FIELD_NAME                      => Types::STRING,
            self::FIELD_DESCRIPTION               => Types::TEXT,
            self::FIELD_CREDENTIALS               => Types::STRING,
            self::FIELD_IS_CONFIDENTIAL           => Types::BOOLEAN,
            self::FIELD_IS_SCOPE_EXCESS_ALLOWED   => Types::BOOLEAN,
            self::FIELD_IS_USE_DEFAULT_SCOPE      => Types::BOOLEAN,
            self::FIELD_IS_CODE_GRANT_ENABLED     => Types::BOOLEAN,
            self::FIELD_IS_IMPLICIT_GRANT_ENABLED => Types::BOOLEAN,
            self::FIELD_IS_PASSWORD_GRANT_ENABLED => Types::BOOLEAN,
            self::FIELD_IS_CLIENT_GRANT_ENABLED   => Types::BOOLEAN,
            self::FIELD_IS_REFRESH_GRANT_ENABLED  => Types::BOOLEAN,
            self::FIELD_CREATED_AT                => DateTimeType::NAME,
            self::FIELD_UPDATED_AT                => DateTimeType::NAME,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_ID          => 255,
            self::FIELD_NAME        => 255,
            self::FIELD_CREDENTIALS => 255,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::HAS_MANY        => [
                self::REL_REDIRECT_URIS => [
                    RedirectUri::class,
                    RedirectUri::FIELD_ID_CLIENT,
                    RedirectUri::REL_CLIENT,
                ],
                self::REL_TOKENS        => [
                    Token::class,
                    Token::FIELD_ID_CLIENT,
                    Token::REL_CLIENT,
                ],
            ],
            RelationshipTypes::BELONGS_TO_MANY => [
                self::REL_SCOPES => [
                    Scope::class,
                    ClientScope::TABLE_NAME,
                    ClientScope::FIELD_ID_CLIENT,
                    ClientScope::FIELD_ID_SCOPE,
                    Scope::REL_CLIENTS,
                ]
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getRawAttributes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public static function getVirtualAttributes(): array
    {
        return [];
    }
}
