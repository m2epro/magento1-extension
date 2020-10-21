<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Advanced_Form extends Ess_M2ePro_Block_Adminhtml_Configuration_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('configurationAdvancedForm');
        $this->setTemplate('M2ePro/configuration/advanced.phtml');
    }

    //########################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getLayout()->getBlock('head')->addJs('M2ePro/Configuration/Advanced.js');
        $this->initPopUp();
    }

    //########################################

    protected function _beforeToHtml()
    {
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Proceed'),
            'onclick' => 'AdvancedObj.informationPopup()',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('proceed_button', $buttonBlock);

        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'setLocation(\''.$this->getUrl('M2ePro/adminhtml_migrationToMagento2/disableModule').'\')',
            'class'   => 'proceed_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);

        return parent::_beforeToHtml();
    }

    //########################################
}