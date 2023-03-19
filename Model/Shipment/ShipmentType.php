<?php
declare(strict_types=1);

namespace DHL\Dhl24pl\Model\Shipment;

use MyCLabs\Enum\Enum;

/**
 * Class ShipmentType
 * @package DHL\Dhl24pl\Model\Shipment
 */
class ShipmentType extends Enum
{
    const SHIPMENT_TYPE_PRIMARY = 'primary';
    const SHIPMENT_TYPE_RETURN = 'return';
}
