<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_FilterPopup
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorAddTabItemFilterPopup');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/motor/add/tab/Item/filter_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'save-btn',
            'label'   => Mage::helper('M2ePro')->__('Save')
        );
        $this->setChild(
            'save_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        return $this;
    }

    //########################################
}