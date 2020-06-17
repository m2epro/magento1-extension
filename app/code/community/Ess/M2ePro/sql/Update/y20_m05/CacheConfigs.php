<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_CacheConfigs extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if (!$this->_installer->getConnection()->isTableExists($this->_installer->getFullTableName('cache_config'))) {
            return;
        }

        $queryStmt = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('cache_config'))
            ->query();

        while ($row = $queryStmt->fetch()) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('registry'),
                array(
                    'key'         => $this->getNewRegistryKey($row),
                    'value'       => $row['value'],
                    'update_date' => $row['update_date'],
                    'create_date' => $row['create_date']
                )
            );
        }

        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('cache_config')
        );
    }

    protected function getNewRegistryKey($row)
    {
        $cacheKeysForRename = array(
            '/view/ebay/listing/advanced/autoaction_popup/##shown/'  => '/ebay/listing/autoaction_popup/is_shown/',
            '/ebay/synchronization/orders/receive/timeout##fails/'   => '/ebay/orders/receive/timeout_fails/',
            '/ebay/synchronization/orders/receive/timeout##rise/'    => '/ebay/orders/receive/timeout_rise/',
            '/amazon/synchronization/orders/receive/timeout##fails/' => '/amazon/orders/receive/timeout_fails/',
            '/amazon/synchronization/orders/receive/timeout##rise/'  => '/amazon/orders/receive/timeout_rise/',
            '/ebay/motors/##was_instruction_shown/'                  => '/ebay/motors/instruction/is_shown/'
        );

        $newKey = $row['group'] . '##' . $row['key'] . '/';
        if (array_key_exists($newKey, $cacheKeysForRename)) {
            $newKey = $cacheKeysForRename[$newKey];
        }

        return str_replace('##', '', $newKey);
    }

    //########################################
}
