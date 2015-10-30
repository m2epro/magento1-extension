<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Fee_Details extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewFeeDetails');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/view/fee/details.phtml');
    }

    public function getFees()
    {
        if (empty($this->_data['fees']) || !is_array($this->_data['fees'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Fees are not set.');
        }

        $preparedData = array();

        foreach ($this->_data['fees'] as $feeName => $feeData) {
            if ($feeData['fee'] <= 0 && $feeName != 'listing_fee') {
                continue;
            }

            $camelCasedFeeName = str_replace('_', ' ', $feeName);
            $camelCasedFeeName = ucwords($camelCasedFeeName);

            $preparedData[$feeName] = array(
                'label' => $camelCasedFeeName,
                'value' => Mage::getSingleton('M2ePro/Currency')->formatPrice($feeData['currency'], $feeData['fee'])
            );
        }

        return $preparedData;
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'class'   => 'close_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}