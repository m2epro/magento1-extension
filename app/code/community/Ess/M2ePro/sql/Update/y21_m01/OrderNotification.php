<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m01_OrderNotification extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->insert(
            Ess_M2ePro_Helper_Order_Notification::NOTIFICATIONS_PATH,
            'mode',
            Ess_M2ePro_Helper_Order_Notification::NOTIFICATIONS_MAGENTO_PAGES
        );

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $this->_installer->getMainConfigModifier()->insert(
            Ess_M2ePro_Helper_Order_Notification::NOTIFICATIONS_PATH,
            'last_date',
            $now->format('Y-m-d')
        );
    }

    //########################################
}