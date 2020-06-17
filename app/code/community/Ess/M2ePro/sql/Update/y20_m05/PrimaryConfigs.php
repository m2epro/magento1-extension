<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m05_PrimaryConfigs extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if (!$this->_installer->getConnection()->isTableExists($this->_installer->getFullTableName('primary_config'))) {
            return;
        }

        $select = $this->_installer->getConnection()
            ->select()
            ->from(
                $this->_installer->getFullTableName('primary_config'),
                array('group', 'key', 'value', 'update_date', 'create_date')
            );

        $this->_installer->getConnection()->query(
            $this->_installer->getConnection()
                ->insertFromSelect(
                    $select,
                    $this->_installer->getFullTableName('config'),
                    array('group','key','value','update_date','create_date')
                )
        );

        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('primary_config')
        );

        $this->migrateConfig();
    }

    protected function migrateConfig()
    {
        $this->updateConfig(
            $this->from('/server/', 'installation_key'),
            $this->to('/', 'installation_key')
        );

        $this->updateConfig(
            $this->from('/license/', 'domain'),
            $this->to('/license/domain/', 'valid')
        );
        $this->updateConfig(
            $this->from('/license/valid/', 'domain'),
            $this->to('/license/domain/', 'is_valid')
        );
        $this->updateConfig(
            $this->from('/license/', 'ip'),
            $this->to('/license/ip/', 'valid')
        );
        $this->updateConfig(
            $this->from('/license/valid/', 'ip'),
            $this->to('/license/ip/', 'is_valid')
        );

        $realDomain = $this->_installer->getCacheConfigModifier()
            ->getEntity('/location_info/', 'domain')
            ->getValue();

        $realIp = $this->_installer->getCacheConfigModifier()
            ->getEntity('/location_info/', 'ip')
            ->getValue();

        $this->_installer->getMainConfigModifier()->insert('/license/domain/', 'real', $realDomain);
        $this->_installer->getMainConfigModifier()->insert('/license/ip/', 'real', $realIp);

        //----------------------------------------

        $this->updateConfig(
            $this->from('/debug/exceptions/', 'send_to_server'),
            $this->to('/server/exceptions/', 'send')
        );
        $this->updateConfig(
            $this->from('/debug/exceptions/', 'filters_mode'),
            $this->to('/server/exceptions/', 'filters')
        );
        $this->updateConfig(
            $this->from('/debug/fatal_error/', 'send_to_server'),
            $this->to('/server/fatal_error/', 'send')
        );
        $this->updateConfig(
            $this->from('/debug/logging/', 'send_to_server'),
            $this->to('/server/logging/', 'send')
        );

        //----------------------------------------

        $this->_installer->getMainConfigModifier()->insert('/server/location/', 'current_index', '1');
    }

    //########################################

    protected function updateConfig(array $from, array $to)
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('config'),
            array(
                'group' => $to['group'],
                'key'   => $to['key']
            ),
            array(
                '`key` = ?'   => $from['key'],
                '`group` = ?' => $from['group']
            )
        );
    }

    protected function from($group, $key)
    {
        return array('group' => $group, 'key' => $key);
    }

    protected function to($group, $key)
    {
        return array('group' => $group, 'key' => $key);
    }

    //########################################
}
