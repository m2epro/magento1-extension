<?php

class Ess_M2ePro_Sql_Update_y25_m07_DecreaseAmazonProcessingTime extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $configs = array(
            array(
                'group' => '/amazon/listing/product/action/revise_qty/',
                'key' => 'min_allowed_wait_interval',
                'value' => '300',
            ),
            array(
                'group' => '/amazon/listing/product/action/revise_price/',
                'key' => 'min_allowed_wait_interval',
                'value' => '300',
            ),
            array(
                'group' => '/amazon/listing/product/action/revise_details/',
                'key' => 'min_allowed_wait_interval',
                'value' => '300',
            ),
        );

        foreach ($configs as $configData) {
            $this->updateConfigValue($configData['group'], $configData['key'], $configData['value']);
        }
    }

    private function updateConfigValue($group, $key, $value)
    {
        $this->_installer
            ->getMainConfigModifier()
            ->getEntity($group, $key)
            ->updateValue($value);
    }
}
