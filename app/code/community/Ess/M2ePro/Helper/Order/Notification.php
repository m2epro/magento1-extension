<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Order_Notification extends Mage_Core_Helper_Abstract
{
    const NOTIFICATIONS_PATH = '/logs/notification/order/';

    const NOTIFICATIONS_DISABLED        = 0;
    const NOTIFICATIONS_EXTENSION_PAGES = 1;
    const NOTIFICATIONS_MAGENTO_PAGES   = 2;

    public function getNotificationMode()
    {
        return (int)$this->getConfig()
            ->getGroupValue(self::NOTIFICATIONS_PATH, 'mode');
    }

    public function setNotificationMode($value)
    {
        $this->getConfig()
            ->setGroupValue(self::NOTIFICATIONS_PATH, 'mode', (int)$value);
    }

    public function getOrderNotCreatedLastDate()
    {
        return  $this->getConfig()
            ->getGroupValue(self::NOTIFICATIONS_PATH, 'order_not_created_last_date');
    }

    public function setOrderNotCreatedLastDate($value)
    {
        $this->getConfig()
            ->setGroupValue(self::NOTIFICATIONS_PATH, 'order_not_created_last_date', $value);
    }

    public function getOrderChangedVatLastDate()
    {
        return  $this->getConfig()
            ->getGroupValue(self::NOTIFICATIONS_PATH, 'order_changed_vat_last_date');
    }

    public function setOrderChangedVatLastDate($value)
    {
        $this->getConfig()
            ->setGroupValue(self::NOTIFICATIONS_PATH, 'order_changed_vat_last_date', $value);
    }

    /**
     * @return Ess_M2ePro_Model_Config_Manager
     */
    private function getConfig()
    {
        /** @var Ess_M2ePro_Helper_Module $moduleHelper */
        $moduleHelper = Mage::helper('M2ePro/Module');

        return $moduleHelper->getConfig();
    }

    public function isNotificationDisabled()
    {
        return $this->getNotificationMode() === self::NOTIFICATIONS_DISABLED;
    }

    public function isNotificationExtensionPages()
    {
        return $this->getNotificationMode() === self::NOTIFICATIONS_EXTENSION_PAGES;
    }

    public function isNotificationMagentoPages()
    {
        return $this->getNotificationMode() === self::NOTIFICATIONS_MAGENTO_PAGES;
    }
}
