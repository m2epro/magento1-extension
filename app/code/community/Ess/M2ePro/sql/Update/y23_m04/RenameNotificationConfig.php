<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m04_RenameNotificationConfig extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $moduleConfigModifier = $this->_installer->getMainConfigModifier();

        $now = date('Y-m-d');
        $oldValue = $moduleConfigModifier
            ->getEntity('/logs/notification/order/', 'last_date')
            ->getValue();

        if (empty($oldValue)) {
            $oldValue = $now;
        }

        $moduleConfigModifier
            ->delete('/logs/notification/order/', 'last_date');

        $moduleConfigModifier
            ->insert('/logs/notification/order/', 'order_not_created_last_date', $oldValue);
        $moduleConfigModifier
            ->insert('/logs/notification/order/', 'order_changed_vat_last_date', $now);
    }
}
