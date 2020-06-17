<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_Category_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $data = array();

        $defaultData = $this->getDefaultData();
        unset($defaultData['id']);
        $keys = array_keys($defaultData);

        foreach ($keys as $key) {
            isset($this->_rawData[$key]) && $data[$key] = $this->_rawData[$key];
        }

        $data['title'] = strip_tags($data['title']);

        return $data;
    }

    public function getDefaultData()
    {
        return array(
            'id'                => '',
            'title'             => '',
            'marketplace_id'    => '',
            'category_path'     => '',
            'browsenode_id'     => '',
            'product_data_nick' => '',
            'specifics'         => array()
        );
    }

    //########################################
}
