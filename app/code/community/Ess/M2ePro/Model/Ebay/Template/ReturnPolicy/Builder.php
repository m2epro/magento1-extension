<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_ReturnPolicy as ReturnPolicy;

class Ess_M2EPro_Model_Ebay_Template_ReturnPolicy_Builder
    extends Ess_M2ePro_Model_Ebay_Template_AbstractBuilder
{
    //########################################

    protected function validate()
    {
        if (empty($this->_rawData['marketplace_id'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace ID is empty.');
        }

        parent::validate();
    }

    protected function prepareData()
    {
        $this->validate();

        $data = parent::prepareData();

        $data['marketplace_id'] = (int)$this->_rawData['marketplace_id'];

        $domesticKeys = array(
            'accepted', 'option', 'within', 'shipping_cost'
        );
        foreach ($domesticKeys as $keyName) {
            isset($this->_rawData[$keyName]) && $data[$keyName] = $this->_rawData[$keyName];
        }

        $internationalKeys = array(
            'international_accepted', 'international_option', 'international_within', 'international_shipping_cost'
        );
        foreach ($internationalKeys as $keyName) {
            isset($this->_rawData[$keyName]) && $data[$keyName] = $this->_rawData[$keyName];
        }

        isset($this->_rawData['description']) && $data['description'] = $this->_rawData['description'];

        return $data;
    }

    //########################################

    public function getDefaultData()
    {
        return array(
            'accepted'      => ReturnPolicy::RETURNS_ACCEPTED,
            'option'        => '',
            'within'        => '',
            'shipping_cost' => '',

            'international_accepted'      => ReturnPolicy::RETURNS_NOT_ACCEPTED,
            'international_option'        => '',
            'international_within'        => '',
            'international_shipping_cost' => '',

            'description' => ''
        );
    }

    //########################################
}
