<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data;

interface TaxIdentifierInterface
{
    /**
     * String constants for property names
     */
    public const TYPE = "identifier_type";
    public const VALUE = "value";
    public const ISSUING_AUTHORITY = "issuing_authority";
    public const ENTITY_TYPE = "taxable_entity_type";

    /**
     * Getter for Type.
     *
     * @return string|null
     */
    public function getType(): ?string;

    /**
     * Setter for Type.
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function setType(?string $type): TaxIdentifierInterface;

    /**
     * Getter for Value.
     *
     * @return string|null
     */
    public function getValue(): ?string;

    /**
     * Setter for Value.
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function setValue(?string $value): TaxIdentifierInterface;

    /**
     * Getter for IssueAuthority.
     *
     * @return string|null
     */
    public function getIssueAuthority(): ?string;

    /**
     * Setter for IssueAuthority.
     *
     * @param string|null $issueAuthority
     *
     * @return $this
     */
    public function setIssueAuthority(?string $issueAuthority): TaxIdentifierInterface;

    /**
     * Getter for EntityType.
     *
     * @return string|null
     */
    public function getEntityType(): ?string;

    /**
     * Setter for EntityType.
     *
     * @param string|null $entityType
     *
     * @return $this
     */
    public function setEntityType(?string $entityType): TaxIdentifierInterface;
}
