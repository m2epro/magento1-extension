<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_StoreCategory_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = array();

        $keys = array(
            'account_id',
            'category_mode',
            'category_id',
            'category_attribute',
            'category_path'
        );

        foreach ($keys as $key) {
            isset($this->_rawData[$key]) && $data[$key] = $this->_rawData[$key];
        }

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            'category_id'        => 0,
            'category_path'      => '',
            'category_mode'      => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
            'category_attribute' => '',
        );
    }

    //########################################
}
