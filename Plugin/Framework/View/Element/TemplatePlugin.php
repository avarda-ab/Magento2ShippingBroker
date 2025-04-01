<?php

/**
 * @author Avarda Team
 * @copyright Copyright © Avarda. All rights reserved.
 */

declare(strict_types=1);

namespace Avarda\ShippingBroker\Plugin\Framework\View\Element;

use Magento\Framework\View\Element\Template;

class TemplatePlugin
{
    /**
     * Add dependency on the unifaun script (should be changed in next version)
     *
     * @param Template $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        if ($subject->getNameInLayout() == 'require.js') {
            $unifaunJS = $subject->getBlockHtml('add-unifaun-js');
            return $unifaunJS . $result;
        }

        return $result;
    }
}
