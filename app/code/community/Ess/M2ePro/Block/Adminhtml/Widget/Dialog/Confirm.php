<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_Dialog_Confirm
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('widgetConfirm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/widget/dialog/confirm.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $data = array(
            'class'   => 'ok_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'Dialog.okCallback();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('ok_button', $buttonBlock);
        // ---------------------------------------
    }

    //########################################
}