<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Model;

use Calcurates\ModuleMagento\Api\Data\TaxIdentifierInterface;
use Calcurates\ModuleMagento\Model\ResourceModel\TaxIdentifier as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class TaxIdentifier extends AbstractModel implements TaxIdentifierInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'calcurates_tax_identifiers_model';

    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Getter for Type.
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Setter for Type.
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function setType(?string $type): TaxIdentifierInterface
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Getter for Value.
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->getData(self::VALUE);
    }

    /**
     * Setter for Value.
     *
     * @param string|null $value
     *
     * @return $this
     */
    public function setValue(?string $value): TaxIdentifierInterface
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * Getter for IssueAuthority.
     *
     * @return string|null
     */
    public function getIssueAuthority(): ?string
    {
        return $this->getData(self::ISSUING_AUTHORITY);
    }

    /**
     * Setter for IssueAuthority.
     *
     * @param string|null $issueAuthority
     *
     * @return $this
     */
    public function setIssueAuthority(?string $issueAuthority): TaxIdentifierInterface
    {
        return $this->setData(self::ISSUING_AUTHORITY, $issueAuthority);
    }

    /**
     * Getter for EntityType.
     *
     * @return string|null
     */
    public function getEntityType(): ?string
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * Setter for EntityType.
     *
     * @param string|null $entityType
     *
     * @return $this
     */
    public function setEntityType(?string $entityType): TaxIdentifierInterface
    {
        return $this->setData(self::ENTITY_TYPE, $entityType);
    }
}
