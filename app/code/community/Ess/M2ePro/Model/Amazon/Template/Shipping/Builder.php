<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Template_Shipping_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

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
            'title'         => '',

            'template_name_mode' => '',
            'template_name_value' => '',
            'template_name_attribute' => '',
        );
    }

    //########################################
}
