<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Notification_OrderNotCreated
    extends Ess_M2ePro_Block_Adminhtml_Notification_AbstractNotificationMessage
{
    protected function renderHtml()
    {
        return <<<TEMPLATE
Since {$this->sinceDate}, some Magento orders have not been created: {$this->failOrderCount},
check your {$this->makeLinksHtmlToComponentLogs()}.{$this->makeSkipLink()}.
TEMPLATE;
    }
}
