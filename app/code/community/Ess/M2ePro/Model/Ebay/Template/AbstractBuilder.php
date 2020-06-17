<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Template_AbstractBuilder extends Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    //########################################

    protected function validate()
    {
        if (!isset($this->_rawData['is_custom_template'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Policy mode is empty.');
        }
    }

    protected function prepareData()
    {
        $data = array();

        // ---------------------------------------
        if (isset($this->_rawData['id']) && (int)$this->_rawData['id'] > 0) {
            $data['id'] = (int)$this->_rawData['id'];
        }

        $data['is_custom_template'] = (int)(bool)$this->_rawData['is_custom_template'];
        $data['title'] = $this->_rawData['title'];
        // ---------------------------------------

        // ---------------------------------------
        unset($this->_rawData['id']);
        unset($this->_rawData['is_custom_template']);
        unset($this->_rawData['title']);
        // ---------------------------------------

        return $data;
    }

    //########################################
}
