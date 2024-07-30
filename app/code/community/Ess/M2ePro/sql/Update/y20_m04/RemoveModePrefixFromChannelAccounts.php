<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m04_RemoveModePrefixFromChannelAccounts extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $dataHelper = Mage::helper('M2ePro/Data');

        foreach (Mage::helper('M2ePro/Component')->getComponents() as $component) {
            $accountChannelTable = $this->_installer->getFullTableName("{$component}_account");

            $query = $this->_installer->getConnection()
                ->select()
                ->from($accountChannelTable)
                ->query();

            while ($row = $query->fetch()) {
                $magentoOrdersSettings = $dataHelper->jsonDecode($row['magento_orders_settings']);
                if (!isset($magentoOrdersSettings['number']['prefix']['mode'])) {
                    continue;
                }

                if ($magentoOrdersSettings['number']['prefix']['mode'] == 0) {
                    foreach ($magentoOrdersSettings['number']['prefix'] as &$setting) {
                        $setting = '';
                    }
                    unset($setting);
                }
                unset($magentoOrdersSettings['number']['prefix']['mode']);

                $this->_installer->getConnection()->update(
                    $accountChannelTable,
                    array('magento_orders_settings' => $dataHelper->jsonEncode($magentoOrdersSettings)),
                    array('account_id = ?' => (int)$row['account_id'])
                );
            }
        }
    }

    //########################################
}
