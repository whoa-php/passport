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
use Whoa\Contracts\Data\TimestampFields;
use Whoa\Doctrine\Types\DateTimeType;

/**
 * @package Whoa\Passport
 */
class TokenScope implements ModelInterface, TimestampFields
{
    /** @var string Table name */
    const TABLE_NAME = 'oauth_tokens_scopes';

    /** @var string Primary key */
    const FIELD_ID = 'id_token_scope';

    /** @var string Foreign key */
    const FIELD_ID_TOKEN = Token::FIELD_ID;

    /** @var string Foreign key */
    const FIELD_ID_SCOPE = Scope::FIELD_ID;

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
            self::FIELD_ID         => Types::INTEGER,
            self::FIELD_ID_TOKEN   => Token::getAttributeTypes()[Token::FIELD_ID],
            self::FIELD_ID_SCOPE   => Scope::getAttributeTypes()[Scope::FIELD_ID],
            self::FIELD_CREATED_AT => DateTimeType::NAME,
            self::FIELD_UPDATED_AT => DateTimeType::NAME,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getAttributeLengths(): array
    {
        return [
            self::FIELD_ID_SCOPE => Scope::getAttributeLengths()[Scope::FIELD_ID],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getRelationships(): array
    {
        return [];
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
