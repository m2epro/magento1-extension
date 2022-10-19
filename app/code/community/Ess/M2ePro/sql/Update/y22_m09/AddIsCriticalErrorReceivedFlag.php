<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m09_AddIsCriticalErrorReceivedFlag extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_feedback')
            ->addColumn(
                'is_critical_error_received',
                'TINYINT(2) UNSIGNED NOT NULL',
                0,
                'last_response_attempt_date',
                false,
                false
            )
            ->commit();
    }
}
