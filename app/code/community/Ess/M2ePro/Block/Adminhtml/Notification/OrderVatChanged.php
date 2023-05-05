<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Notification_OrderVatChanged
    extends Ess_M2ePro_Block_Adminhtml_Notification_AbstractNotificationMessage
{
    protected function renderHtml()
    {
        return <<<MESAGE
Since {$this->sinceDate}, Amazon has applied reverse charge (0% VAT) to {$this->failOrderCount} orders,
check your {$this->makeLinksHtmlToComponentLogs()}. {$this->makeSkipLink()}.
MESAGE;
    }
}
