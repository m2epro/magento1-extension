<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m01_AmazonRemoveUnnecessaryData extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $this->_installer->getMainConfigModifier()
            ->delete('/amazon/', 'application_name');

        $this->_installer->getTableModifier('amazon_marketplace')
            ->dropColumn('developer_key');
    }
}
