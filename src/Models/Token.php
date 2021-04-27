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
class Token implements ModelInterface, UuidFields, TimestampFields
{
    /** @var string Table name */
    const TABLE_NAME = 'oauth_tokens';

    /** @var string Primary key */
    const FIELD_ID = 'id_token';

    /** @var string Foreign key */
    const FIELD_ID_CLIENT = Client::FIELD_ID;

    /** @var string Foreign key */
    const FIELD_ID_USER = 'id_user';

    /** @var string Field name */
    const FIELD_IS_SCOPE_MODIFIED = 'is_scope_modified';

    /** @var string Field name */
    const FIELD_IS_ENABLED = 'is_enabled';

    /** @var string Field name */
    const FIELD_REDIRECT_URI = 'redirect_uri';

    /** @var string Field name */
    const FIELD_CODE = 'code';

    /** @var string Field name */
    const FIELD_VALUE = 'value';

    /** @var string Field name */
    const FIELD_TYPE = 'type';

    /** @var string Field name */
    const FIELD_REFRESH = 'refresh';

    /** @var string Field name */
    const FIELD_CODE_CREATED_AT = 'code_created_at';

    /** @var string Field name */
    const FIELD_VALUE_CREATED_AT = 'value_created_at';

    /** @var string Field name */
    const FIELD_REFRESH_CREATED_AT = 'refresh_created_at';

    /** @var string Relationship name */
    const REL_CLIENT = 'client';

    /** @var string Relationship name */
    const REL_SCOPES = 'scopes';

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
            self::FIELD_ID                 => Types::INTEGER,
            self::FIELD_ID_CLIENT          => Client::getAttributeTypes()[Client::FIELD_ID],
            self::FIELD_ID_USER            => Types::INTEGER,
            self::FIELD_UUID               => UuidType::NAME,
            self::FIELD_IS_SCOPE_MODIFIED  => Types::BOOLEAN,
            self::FIELD_IS_ENABLED         => Types::BOOLEAN,
            self::FIELD_REDIRECT_URI       => Types::STRING,
            self::FIELD_CODE               => Types::STRING,
            self::FIELD_VALUE              => Types::STRING,
            self::FIELD_TYPE               => Types::STRING,
            self::FIELD_REFRESH            => Types::STRING,
            self::FIELD_CODE_CREATED_AT    => DateTimeType::NAME,
            self::FIELD_VALUE_CREATED_AT   => DateTimeType::NAME,
            self::FIELD_REFRESH_CREATED_AT => DateTimeType::NAME,
            self::FIELD_CREATED_AT         => DateTimeType::NAME,
            self::FIELD_UPDATED_AT         => DateTimeType::NAME,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_ID_CLIENT    => Client::getAttributeTypes()[Client::FIELD_ID],
            self::FIELD_REDIRECT_URI => 255,
            self::FIELD_CODE         => 255,
            self::FIELD_VALUE        => 255,
            self::FIELD_TYPE         => 255,
            self::FIELD_REFRESH      => 255,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO      => [
                self::REL_CLIENT => [
                    Client::class,
                    self::FIELD_ID_CLIENT,
                    Client::REL_TOKENS,
                ],
            ],
            RelationshipTypes::BELONGS_TO_MANY => [
                self::REL_SCOPES => [
                    Scope::class,
                    TokenScope::TABLE_NAME,
                    TokenScope::FIELD_ID_TOKEN,
                    TokenScope::FIELD_ID_SCOPE,
                    Scope::REL_TOKENS,
                ],
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
