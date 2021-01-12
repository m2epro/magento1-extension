<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Template_Synchronization as Synchronization;

class Ess_M2ePro_Model_Amazon_Template_Synchronization_Builder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
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

        $data['title'] = strip_tags($data['title']);

        $data['list_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Amazon_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX,
            $this->_rawData
        );

        $data['relist_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Amazon_Template_Synchronization::RELIST_ADVANCED_RULES_PREFIX,
            $this->_rawData
        );

        $data['revise_update_qty'] = 1;

        $data['stop_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Amazon_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX,
            $this->_rawData
        );

        return $data;
    }

    protected function getRuleData($rulePrefix, $post)
    {
        if (empty($post['rule'][$rulePrefix])) {
            return null;
        }

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array('prefix' => $rulePrefix)
        );

        return $ruleModel->getSerializedFromPost($post);
    }

    public function getDefaultData()
    {
        return array(
            'title'               => '',

            // list
            'list_mode'           => 1,
            'list_status_enabled' => 1,
            'list_is_in_stock'    => 1,

            'list_qty_calculated'       => Synchronization::QTY_MODE_YES,
            'list_qty_calculated_value' => '1',

            'list_advanced_rules_mode' => 0,

            // relist
            'relist_mode'              => 1,
            'relist_filter_user_lock'  => 1,
            'relist_status_enabled'    => 1,
            'relist_is_in_stock'       => 1,

            'relist_qty_calculated'       => Synchronization::QTY_MODE_YES,
            'relist_qty_calculated_value' => '1',

            'relist_advanced_rules_mode'               => 0,

            // revise
            'revise_update_qty'                        => 1,
            'revise_update_qty_max_applied_value_mode' => 1,
            'revise_update_qty_max_applied_value'      => 5,
            'revise_update_price'                      => 1,
            'revise_update_details'                    => 0,
            'revise_update_images'                     => 0,

            // stop
            'stop_mode'                                => 1,

            'stop_status_disabled' => 1,
            'stop_out_off_stock'   => 1,

            'stop_qty_calculated'       => Synchronization::QTY_MODE_YES,
            'stop_qty_calculated_value' => '0',

            'stop_advanced_rules_mode' => 0,
        );
    }

    //########################################
}
