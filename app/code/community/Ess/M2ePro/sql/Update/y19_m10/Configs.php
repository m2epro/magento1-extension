<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_Configs extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $primaryConfig = $this->_installer->getPrimaryConfigModifier();

        $primaryConfig->delete('/modules/');

        $primaryConfig->getEntity('/server/', 'default_baseurl_index')
            ->updateGroup('/server/location/')
            ->updateKey('default_index');

        $query = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('primary_config'))
            ->where("`group` = '/server/' AND (`key` LIKE 'baseurl_%' OR `key` LIKE 'hostname_%')");

        foreach ($this->_installer->getConnection()->fetchAll($query) as $row) {
            $key   = (strpos($row['key'], 'baseurl') !== false) ? 'baseurl' : 'hostname';
            $index = str_replace($key . '_', '', $row['key']);
            $group = "/server/location/{$index}/";

            $primaryConfig->getEntity('/server/', $row['key'])->updateGroup($group);
            $primaryConfig->getEntity($group, $row['key'])->updateKey($key);
        }

        $select = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getFullTableName('primary_config'))
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('group')
            ->where('`group` like ?', '/M2ePro/%');

        $groupsForRenaming = $this->_installer->getConnection()->fetchCol($select);
        foreach (array_unique($groupsForRenaming) as $group) {
            $primaryConfig->updateGroup(
                preg_replace('/^\/M2ePro/', '', $group),
                array('`group` = ?' => $group)
            );
        }

        // ---------------------------------------

        $mainConfig = $this->_installer->getMainConfigModifier();

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('config'),
            array('group' => '/'),
            '`group` IS NULL'
        );

        $mainConfig->delete('/support/', 'knowledge_base_url');
        $mainConfig->insert('/support/', 'forum_url', 'https://community.m2epro.com/');

        $mainConfig->getEntity('/support/', 'main_website_url')->updateKey('website_url');
        $mainConfig->getEntity('/support/', 'main_support_url')->updateKey('support_url');
        $mainConfig->getEntity('/support/', 'magento_connect_url')
            ->updateKey('magento_marketplace_url')
            ->updateValue('https://marketplace.magento.com/m2e-m2epro-ebay-magento.html');
    }

    //########################################
}
