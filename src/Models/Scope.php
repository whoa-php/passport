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
class Scope implements ModelInterface, UuidFields, TimestampFields
{
    /** @var string Table name */
    const TABLE_NAME = 'oauth_scopes';

    /** @var string Primary key */
    const FIELD_ID = 'id_scope';

    /** @var string Field name */
    const FIELD_DESCRIPTION = 'description';

    /** @var string Relationship name */
    const REL_CLIENTS = 'clients';

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
            self::FIELD_ID          => Types::STRING,
            self::FIELD_UUID        => UuidType::NAME,
            self::FIELD_DESCRIPTION => Types::TEXT,
            self::FIELD_CREATED_AT  => DateTimeType::NAME,
            self::FIELD_UPDATED_AT  => DateTimeType::NAME,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_ID => 255,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getRelationships(): array
    {
        return [
            RelationshipTypes::BELONGS_TO_MANY => [
                self::REL_CLIENTS => [
                    Client::class,
                    ClientScope::TABLE_NAME,
                    ClientScope::FIELD_ID_SCOPE,
                    ClientScope::FIELD_ID_CLIENT,
                    Client::REL_SCOPES,
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
