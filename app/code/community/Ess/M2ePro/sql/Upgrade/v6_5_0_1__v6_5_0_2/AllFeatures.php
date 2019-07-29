<?php

class Ess_M2ePro_Sql_Upgrade_v6_5_0_1__v6_5_0_2_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->installer->getTableModifier('amazon_processing_action')
            ->dropColumn('is_completed');

        $this->installer->getTableModifier('amazon_processing_action_item')
            ->addColumn('is_skipped', 'TINYINT(2) NOT NULL', 0, 'is_completed', true);

        $this->installer->getTableModifier('ebay_processing_action')
            ->dropColumn('is_completed', true, false)
            ->addColumn('request_timeout', 'INT(11) UNSIGNED', NULL, 'type', false, false)
            ->commit();

        $this->installer->getTableModifier('ebay_processing_action_item')
            ->dropColumn('output_data', false, false)
            ->dropColumn('output_messages', false, false)
            ->dropColumn('attempts_count', false, false)
            ->dropColumn('is_completed', true, false)
            ->addColumn('is_skipped', 'TINYINT(2) NOT NULL', 0, 'input_data', true, false)
            ->commit();

        $this->installer->getMainConfigModifier()->getEntity('/amazon/repricing/', 'base_url')
            ->updateValue('http://repricer.m2epro.com/connector/m2epro/');
    }

    //########################################
}