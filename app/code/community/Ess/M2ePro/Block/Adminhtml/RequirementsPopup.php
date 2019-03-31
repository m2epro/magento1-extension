<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_RequirementsPopup extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('requirementsPopup');
        // ---------------------------------------

        $this->setTemplate('M2ePro/requirements_popup.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Confirm and Close'),
            'onclick' => 'closeRequirementsPopup();',
            'style' => 'float:right;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('requirements_popup_close',$buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_development_inspection_requirements');
        $this->setChild('requirements', $block);
        // ---------------------------------------
    }

    //########################################
}