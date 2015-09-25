<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_MainAbstract
{
    // ########################################

    protected function getHeaderTextHtml()
    {
        return 'Configuration Wizard!';
    }

    // ########################################

    protected function _afterToHtml($html)
    {
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData(array(
                                'id'      => 'wizard_complete',
                                'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                                'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                                'class'   => 'end_button',
                                'style'   => 'display: none'
                            ));

        $html .= $buttonBlock->toHtml();
        return parent::_afterToHtml($html);
    }

    // ########################################
}