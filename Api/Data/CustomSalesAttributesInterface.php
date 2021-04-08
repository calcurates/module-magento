<?php
declare(strict_types=1);
/**
 * @author Calcurates Team
 * @copyright Copyright © 2020 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

namespace Calcurates\ModuleMagento\Api\Data;

interface CustomSalesAttributesInterface
{
    const CARRIER_SOURCE_CODE_TO_SERVICE = 'calcurates_carrier_srvs_srs_codes';
    const SOURCE_CODE = 'calcurates_source_code';
    /* @TODO: remove from track table! */
    const SERVICE_ID = 'calcurates_service_id';
    /* @TODO: drop all usages and remove from table */
    const LABEL_DATA = 'calcurates_label_data';
    const CARRIER_PACKAGES = 'calcurates_carrier_packages';
    const DELIVERY_DATES = 'calcurates_delivery_dates_data';
}
