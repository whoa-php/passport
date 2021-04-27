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

namespace Whoa\Passport\Entities;

use DateTimeImmutable;
use DateTimeInterface;
use Whoa\Contracts\Data\TimestampFields;
use Whoa\Contracts\Data\UuidFields;
use Whoa\Doctrine\Traits\UuidTypeTrait;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function property_exists;

/**
 * @package Whoa\Passport
 */
abstract class DatabaseItem implements UuidFields, TimestampFields
{
    use UuidTypeTrait;

    /**
     * @return string
     */
    abstract protected function getDbDateFormat(): string;

    /**
     * @var UuidInterface|null
     */
    private $uuidField;

    /**
     * @var DateTimeInterface|null
     */
    private $createdAtField;

    /**
     * @var DateTimeInterface|null
     */
    private $updatedAtField;

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        if ($this->uuidField === null &&
            $this->hasDynamicProperty(static::FIELD_UUID) === true &&
            ($uuid = $this->{static::FIELD_UUID}) !== null
        ) {
            $this->setUuidImpl($uuid);
        } else {
            $this->setUuidImpl();
        }

        return $this->uuidField;
    }

    /**
     * @param UuidInterface|string|null $uuid
     *
     * @return $this
     */
    public function setUuidImpl($uuid = null): DatabaseItem
    {
        if ($uuid instanceof UuidInterface) {
            $this->uuidField = $uuid;
        } elseif (is_string($uuid) === true && Uuid::isValid($uuid) === true) {
            $this->uuidField = $this->parseUuid($uuid);
        } else {
            $this->uuidField = Uuid::uuid4();
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        if ($this->createdAtField === null &&
            $this->hasDynamicProperty(static::FIELD_CREATED_AT) === true &&
            ($createdAt = $this->{static::FIELD_CREATED_AT}) !== null
        ) {
            $this->setCreatedAtImpl($this->parseDateTime($createdAt));
        }

        return $this->createdAtField;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        if ($this->updatedAtField === null &&
            $this->hasDynamicProperty(static::FIELD_UPDATED_AT) === true &&
            ($updatedAt = $this->{static::FIELD_UPDATED_AT}) !== null
        ) {
            $this->setUpdatedAtImpl($this->parseDateTime($updatedAt));
        }

        return $this->updatedAtField;
    }

    /**
     * @param DateTimeInterface $createdAt
     *
     * @return DatabaseItem
     */
    protected function setCreatedAtImpl(DateTimeInterface $createdAt): DatabaseItem
    {
        $this->createdAtField = $createdAt;

        return $this;
    }

    /**
     * @param DateTimeInterface $updatedAt
     *
     * @return DatabaseItem
     */
    protected function setUpdatedAtImpl(DateTimeInterface $updatedAt): DatabaseItem
    {
        $this->updatedAtField = $updatedAt;

        return $this;
    }

    /**
     * @param string $createdAt
     *
     * @return DateTimeInterface
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function parseDateTime(string $createdAt): DateTimeInterface
    {
        return DateTimeImmutable::createFromFormat($this->getDbDateFormat(), $createdAt);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    protected function hasDynamicProperty(string $name): bool
    {
        return property_exists($this, $name);
    }
}
