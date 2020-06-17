<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_Description_Specific as Specific;

class Ess_M2ePro_Model_Amazon_Template_Description_Specific_Builder extends
    Ess_M2ePro_Model_ActiveRecord_AbstractBuilder
{
    private $_templateDescriptionId;

    //########################################

    public function setTemplateDescriptionId($templateDescriptionId)
    {
        $this->_templateDescriptionId = $templateDescriptionId;
    }

    public function getTemplateDescriptionId()
    {
        if (empty($this->_templateDescriptionId)) {
            throw new Ess_M2ePro_Model_Exception_Logic('descriptionTemplateId not set');
        }

        return $this->_templateDescriptionId;
    }

    //########################################

    protected function prepareData()
    {
        return array(
            'template_description_id' => $this->getTemplateDescriptionId(),
            'xpath'             => $this->_rawData['xpath'],
            'mode'              => $this->_rawData['mode'],
            'is_required'       => isset($this->_rawData['is_required']) ? $this->_rawData['is_required'] : 0,
            'recommended_value' => $this->_rawData['mode'] == Specific::DICTIONARY_MODE_RECOMMENDED_VALUE
                ? $this->_rawData['recommended_value'] : '',
            'custom_value'      => $this->_rawData['mode'] == Specific::DICTIONARY_MODE_CUSTOM_VALUE
                ? $this->_rawData['custom_value'] : '',
            'custom_attribute'  => $this->_rawData['mode'] == Specific::DICTIONARY_MODE_CUSTOM_ATTRIBUTE
                ? $this->_rawData['custom_attribute'] : '',
            'type'              => isset($this->_rawData['type']) ? $this->_rawData['type'] : '',
            'attributes'        => isset($this->_rawData['attributes']) ?
                Mage::helper('M2ePro')->jsonEncode($this->_rawData['attributes']) : '[]'
        );
    }

    public function getDefaultData()
    {
        return array();
    }

    //########################################
}
