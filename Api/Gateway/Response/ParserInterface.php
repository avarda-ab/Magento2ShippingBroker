<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\GatewayShipping\Api\Gateway\Response;

interface ParserInterface
{
    /**
     * Parse and return data from response
     *
     * @param array $response
     * @return array|bool
     */
    public function parse(array $response): array|bool;
}
