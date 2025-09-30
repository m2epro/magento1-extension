<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Account as Account;

class Ess_M2ePro_Model_Amazon_Account_Repricing_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{

    protected function prepareData()
    {
        $data = array();

        $keys = array_keys($this->getDefaultData());

        foreach ($keys as $key) {
            if (isset($this->_rawData[$key])) {
                $data[$key] = $this->_rawData[$key];
            }
        }

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            'account_id' => '',
            'email' => '',
            'token' => '',

            'regular_price_mode' => '',
            'regular_price_attribute' => '',
            'regular_price_coefficient' => '',
            'regular_price_variation_mode' => '',

            'min_price_mode' => '',
            'min_price_attribute' => '',
            'min_price_coefficient' => '',
            'min_price_value' => '',
            'min_price_percent' => '',
            'min_price_variation_mode' => '',
            'min_price_value_attribute' => '',
            'min_price_percent_attribute' => '',

            'max_price_mode' => '',
            'max_price_attribute' => '',
            'max_price_coefficient' => '',
            'max_price_value' => '',
            'max_price_percent' => '',
            'max_price_variation_mode' => '',
            'max_price_value_attribute' => '',
            'max_price_percent_attribute' => '',

            'disable_mode' => '',
            'disable_mode_attribute' => '',
        );
    }
}
