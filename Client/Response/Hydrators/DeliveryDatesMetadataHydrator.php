<?php

/**
 * @author Calcurates Team
 * @copyright Copyright © 2019-2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

declare(strict_types=1);

namespace Calcurates\ModuleMagento\Client\Response\Hydrators;

use Calcurates\ModuleMagento\Api\Data\Metadata\DeliveryDatesMetadataInterface;

class DeliveryDatesMetadataHydrator implements HydratorInterface
{
    public const DELIVERY_DATES_METADATA_KEY = 'deliveryDates';

    /**
     * @param array $response
     * @return array|null
     */
    public function extract(array $response): ?array
    {
        if (!isset($response['metadata'])
            && !isset($response['metadata'][self::DELIVERY_DATES_METADATA_KEY])
        ) {
            return null;
        }
        return $response['metadata'][self::DELIVERY_DATES_METADATA_KEY];
    }

    /**
     * @param object $entity
     * @param array $data
     * @return object
     */
    public function hydrate($entity, array $data)
    {
        /** @var DeliveryDatesMetadataInterface $entity */
        $entity->setTimeSlotDateRequired(
            (bool) ($data[DeliveryDatesMetadataInterface::TIME_SLOT_DATE_REQUIRED] ?? null)
        )->setTimeSlotTimeRequired(
            (bool) ($data[DeliveryDatesMetadataInterface::TIME_SLOT_TIME_REQUIRED] ?? null)
        );

        return $entity;
    }
}
