<?php
/**
 * @author Calcurates Team
 * @copyright Copyright © 2021 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Model\Data;

use Calcurates\ModuleMagento\Api\Data\MetaRateInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class MetaRate extends AbstractSimpleObject implements MetaRateInterface
{

    /**
     * {@inheritdoc}
     */
    public function getRates(): array
    {
        return $this->_get(self::RATES);
    }

    /**
     * {@inheritdoc}
     */
    public function setRates(array $rates): void
    {
        $this->setData(self::RATES, $rates);
    }

    /**
     * {@inheritdoc}
     */
    public function getProducts(): ?array
    {
        return $this->_get(self::PRODUCTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProducts(array $products): void
    {
        $this->setData(self::PRODUCTS, $products);
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginId(): ?int
    {
        return $this->_get(self::ORIGIN);
    }

    /**
     * {@inheritdoc}
     */
    public function setOriginId(int $id): void
    {
        $this->setData(self::ORIGIN, $id);
    }
}
