<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_ShippingOverride_Builder extends
    Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    private $_templateSellingFormatId;

    //########################################

    public function setTemplateSellingFormatId($templateSellingFormatId)
    {
        $this->_templateSellingFormatId = $templateSellingFormatId;
    }

    public function getTemplateSellingFormatId()
    {
        if (empty($this->_templateSellingFormatId)) {
            throw new Ess_M2ePro_Model_Exception_Logic('templateSellingFormatId not set');
        }

        return $this->_templateSellingFormatId;
    }

    //########################################

    protected function prepareData()
    {
        return array(
            'template_selling_format_id' => $this->getTemplateSellingFormatId(),

            'method'              => $this->_rawData['method'],
            'is_shipping_allowed' => $this->_rawData['is_shipping_allowed'],
            'region'              => $this->_rawData['region'],
            'cost_mode'           => !empty($this->_rawData['cost_mode']) ? $this->_rawData['cost_mode'] : 0,
            'cost_value'          => !empty($this->_rawData['cost_value']) ? $this->_rawData['cost_value'] : 0,
            'cost_attribute'      => !empty($this->_rawData['cost_attribute']) ? $this->_rawData['cost_attribute'] : ''
        );
    }

    public function getDefaultData()
    {
        return array();
    }

    //########################################
}
