<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Template_Synchronization as Synchronization;

class Ess_M2EPro_Model_Ebay_Template_Synchronization_Builder
    extends Ess_M2ePro_Model_Ebay_Template_AbstractBuilder
{
    //########################################

    protected function prepareData()
    {
        $this->validate();

        $data = parent::prepareData();

        $this->_rawData = Mage::helper('M2ePro')->arrayReplaceRecursive($this->getDefaultData(), $this->_rawData);

        $data = array_merge(
            $data,
            $this->prepareListData(),
            $this->prepareReviseData(),
            $this->prepareRelistData(),
            $this->prepareStopData()
        );

        return $data;
    }

    // ---------------------------------------

    protected function prepareListData()
    {
        $data = array();

        if (isset($this->_rawData['list_mode'])) {
            $data['list_mode'] = (int)$this->_rawData['list_mode'];
        }

        if (isset($this->_rawData['list_status_enabled'])) {
            $data['list_status_enabled'] = (int)$this->_rawData['list_status_enabled'];
        }

        if (isset($this->_rawData['list_is_in_stock'])) {
            $data['list_is_in_stock'] = (int)$this->_rawData['list_is_in_stock'];
        }

        if (isset($this->_rawData['list_qty_calculated'])) {
            $data['list_qty_calculated'] = (int)$this->_rawData['list_qty_calculated'];
        }

        if (isset($this->_rawData['list_qty_calculated_value'])) {
            $data['list_qty_calculated_value'] = (int)$this->_rawData['list_qty_calculated_value'];
        }

        if (isset($this->_rawData['list_advanced_rules_mode'])) {
            $data['list_advanced_rules_mode'] = (int)$this->_rawData['list_advanced_rules_mode'];
        }

        $data['list_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Ebay_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX
        );

        return $data;
    }

    protected function prepareReviseData()
    {
        $data = array(
            'revise_update_qty' => 1,
        );

        $key = 'revise_update_qty_max_applied_value_mode';
        if (isset($this->_rawData[$key])) {
            $data[$key] = (int)$this->_rawData[$key];
        }

        if (isset($this->_rawData['revise_update_qty_max_applied_value'])) {
            $data['revise_update_qty_max_applied_value'] = (int)$this->_rawData['revise_update_qty_max_applied_value'];
        }

        if (isset($this->_rawData['revise_update_price'])) {
            $data['revise_update_price'] = (int)$this->_rawData['revise_update_price'];
        }

        if (isset($this->_rawData['revise_update_title'])) {
            $data['revise_update_title'] = (int)$this->_rawData['revise_update_title'];
        }

        if (isset($this->_rawData['revise_update_sub_title'])) {
            $data['revise_update_sub_title'] = (int)$this->_rawData['revise_update_sub_title'];
        }

        if (isset($this->_rawData['revise_update_description'])) {
            $data['revise_update_description'] = (int)$this->_rawData['revise_update_description'];
        }

        if (isset($this->_rawData['revise_update_images'])) {
            $data['revise_update_images'] = (int)$this->_rawData['revise_update_images'];
        }

        if (isset($this->_rawData['revise_update_categories'])) {
            $data['revise_update_categories'] = (int)$this->_rawData['revise_update_categories'];
        }

        if (isset($this->_rawData['revise_update_parts'])) {
            $data['revise_update_parts'] = (int)$this->_rawData['revise_update_parts'];
        }

        if (isset($this->_rawData['revise_update_shipping'])) {
            $data['revise_update_shipping'] = (int)$this->_rawData['revise_update_shipping'];
        }

        if (isset($this->_rawData['revise_update_payment'])) {
            $data['revise_update_payment'] = (int)$this->_rawData['revise_update_payment'];
        }

        if (isset($this->_rawData['revise_update_return'])) {
            $data['revise_update_return'] = (int)$this->_rawData['revise_update_return'];
        }

        if (isset($this->_rawData['revise_update_other'])) {
            $data['revise_update_other'] = (int)$this->_rawData['revise_update_other'];
        }

        return $data;
    }

    protected function prepareRelistData()
    {
        $data = array();

        if (isset($this->_rawData['relist_mode'])) {
            $data['relist_mode'] = (int)$this->_rawData['relist_mode'];
        }

        if (isset($this->_rawData['relist_filter_user_lock'])) {
            $data['relist_filter_user_lock'] = (int)$this->_rawData['relist_filter_user_lock'];
        }

        if (isset($this->_rawData['relist_status_enabled'])) {
            $data['relist_status_enabled'] = (int)$this->_rawData['relist_status_enabled'];
        }

        if (isset($this->_rawData['relist_is_in_stock'])) {
            $data['relist_is_in_stock'] = (int)$this->_rawData['relist_is_in_stock'];
        }

        if (isset($this->_rawData['relist_qty_calculated'])) {
            $data['relist_qty_calculated'] = (int)$this->_rawData['relist_qty_calculated'];
        }

        if (isset($this->_rawData['relist_qty_calculated_value'])) {
            $data['relist_qty_calculated_value'] = (int)$this->_rawData['relist_qty_calculated_value'];
        }

        if (isset($this->_rawData['relist_advanced_rules_mode'])) {
            $data['relist_advanced_rules_mode'] = (int)$this->_rawData['relist_advanced_rules_mode'];
        }

        $data['relist_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_ADVANCED_RULES_PREFIX
        );

        return $data;
    }

    protected function prepareStopData()
    {
        $data = array();

        if (isset($this->_rawData['stop_mode'])) {
            $data['stop_mode'] = (int)$this->_rawData['stop_mode'];
        }

        if (isset($this->_rawData['stop_status_disabled'])) {
            $data['stop_status_disabled'] = (int)$this->_rawData['stop_status_disabled'];
        }

        if (isset($this->_rawData['stop_out_off_stock'])) {
            $data['stop_out_off_stock'] = (int)$this->_rawData['stop_out_off_stock'];
        }

        if (isset($this->_rawData['stop_qty_calculated'])) {
            $data['stop_qty_calculated'] = (int)$this->_rawData['stop_qty_calculated'];
        }

        if (isset($this->_rawData['stop_qty_calculated_value'])) {
            $data['stop_qty_calculated_value'] = (int)$this->_rawData['stop_qty_calculated_value'];
        }

        if (isset($this->_rawData['stop_advanced_rules_mode'])) {
            $data['stop_advanced_rules_mode'] = (int)$this->_rawData['stop_advanced_rules_mode'];
        }

        $data['stop_advanced_rules_filters'] = $this->getRuleData(
            Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX
        );

        return $data;
    }

    //########################################

    protected function getRuleData($rulePrefix)
    {
        $post = Mage::app()->getRequest()->getPost();
        if (empty($post['rule'][$rulePrefix])) {
            return null;
        }

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array('prefix' => $rulePrefix)
        );

        return $ruleModel->getSerializedFromPost($post);
    }

    //########################################

    public function getDefaultData()
    {
        return array(
            // list
            'list_mode'           => 1,
            'list_status_enabled' => 1,
            'list_is_in_stock'    => 1,

            'list_qty_calculated'           => Synchronization::QTY_MODE_YES,
            'list_qty_calculated_value'     => '1',

            'list_advanced_rules_mode'    => 0,
            'list_advanced_rules_filters' => null,

            // relist
            'relist_mode'             => 1,
            'relist_filter_user_lock' => 1,
            'relist_status_enabled'   => 1,
            'relist_is_in_stock'      => 1,

            'relist_qty_calculated'           => Synchronization::QTY_MODE_YES,
            'relist_qty_calculated_value'     => '1',

            'relist_advanced_rules_mode'    => 0,
            'relist_advanced_rules_filters' => null,

            // revise
            'revise_update_qty'                              => 1,
            'revise_update_qty_max_applied_value_mode'       => 1,
            'revise_update_qty_max_applied_value'            => 5,
            'revise_update_price'                            => 1,
            'revise_update_title'                            => 0,
            'revise_update_sub_title'                        => 0,
            'revise_update_description'                      => 0,
            'revise_update_images'                           => 0,
            'revise_update_categories'                       => 0,
            'revise_update_parts'                            => 0,
            'revise_update_shipping'                         => 0,
            'revise_update_payment'                          => 0,
            'revise_update_return'                           => 0,
            'revise_update_other'                            => 0,

            // stop
            'stop_mode' => 1,

            'stop_status_disabled' => 1,
            'stop_out_off_stock'   => 1,

            'stop_qty_calculated'           => Synchronization::QTY_MODE_YES,
            'stop_qty_calculated_value'     => '0',

            'stop_advanced_rules_mode'    => 0,
            'stop_advanced_rules_filters' => null
        );
    }

    //########################################
}
