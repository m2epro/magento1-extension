<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Log_Synchronization extends
    Ess_M2ePro_Block_Adminhtml_Log_Synchronization_AbstractContainer
{
    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_View_Ebay::NICK;
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            '',
            array(
                'content' => Mage::helper('M2ePro')->__(
                    'This Log contains information about Synchronization task performing.<br><br>

                    <strong>Note:</strong> Only errors and warnings are logged.<br><br>
            
                    The detailed information can be found <a href="%url%" target="_blank">here</a>.',
                    Mage::helper("M2ePro/Module_Support")->getDocumentationUrl(null, null, "logs-and-events")
                ),
                'title' => Mage::helper('M2ePro')->__('Synchronization')
            )
        );

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}
