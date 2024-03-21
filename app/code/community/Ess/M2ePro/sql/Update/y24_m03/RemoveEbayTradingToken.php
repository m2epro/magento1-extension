<?php

class Ess_M2ePro_Sql_Update_y24_m03_RemoveEbayTradingToken
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('ebay_account');
        $modifier->addColumn(
            'is_token_exist',
            'TINYINT(2) NOT NULL',
            0,
            'user_id'
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_account'),
            array(
                'is_token_exist' => new \Zend_Db_Expr('sell_api_token_session IS NOT NULL')
            )
        );

        $this->dropColumns();
    }

    private function dropColumns()
    {
        $modifier = $this->_installer->getTableModifier('ebay_account');
        $modifier->dropColumn('token_session')
                 ->dropColumn('token_expired_date')
                 ->dropColumn('sell_api_token_session')
                 ->commit();
    }
}