<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_RequirementsPopup extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('requirementsPopup');
        //------------------------------

        $this->setTemplate('M2ePro/requirements_popup.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'closeRequirementsPopup();',
            'style' => 'float:right;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('requirements_popup_close',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_development_inspection_requirements');
        $this->setChild('requirements', $block);
        //-------------------------------
    }

    // ########################################
}