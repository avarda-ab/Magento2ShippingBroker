<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Api\Gateway\Response;

interface ParserInterface
{
    public function parse(array $response): array|bool;
}
