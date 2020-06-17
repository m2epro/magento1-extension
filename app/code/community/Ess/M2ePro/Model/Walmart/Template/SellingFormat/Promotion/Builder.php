<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion_Builder extends
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
        if (!empty($this->_rawData['from_date']['value'])) {
            $startDate = Mage::helper('M2ePro')->getDate(
                $this->_rawData['from_date']['value'], false, 'Y-m-d H:i'
            );
        } else {
            $startDate = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d H:i'
            );
        }

        if (!empty($this->_rawData['to_date']['value'])) {
            $endDate = Mage::helper('M2ePro')->getDate(
                $this->_rawData['to_date']['value'], false, 'Y-m-d H:i'
            );
        } else {
            $endDate = Mage::helper('M2ePro')->getCurrentGmtDate(
                false, 'Y-m-d H:i'
            );
        }

        return array(
            'template_selling_format_id'   => $this->getTemplateSellingFormatId(),
            'price_mode'                   => $this->_rawData['price']['mode'],
            'price_attribute'              => $this->_rawData['price']['attribute'],
            'price_coefficient'            => $this->_rawData['price']['coefficient'],
            'start_date_mode'              => $this->_rawData['from_date']['mode'],
            'start_date_attribute'         => $this->_rawData['from_date']['attribute'],
            'start_date_value'             => $startDate,
            'end_date_mode'                => $this->_rawData['to_date']['mode'],
            'end_date_attribute'           => $this->_rawData['to_date']['attribute'],
            'end_date_value'               => $endDate,
            'comparison_price_mode'        => $this->_rawData['comparison_price']['mode'],
            'comparison_price_attribute'   => $this->_rawData['comparison_price']['attribute'],
            'comparison_price_coefficient' => $this->_rawData['comparison_price']['coefficient'],
            'type'                         => $this->_rawData['type'],
        );
    }

    public function getDefaultData()
    {
        return array();
    }

    //########################################
}
