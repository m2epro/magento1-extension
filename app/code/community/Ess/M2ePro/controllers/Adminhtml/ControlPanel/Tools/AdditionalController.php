<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Tools_AdditionalController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    /**
     * @title "Clear Opcode"
     * @description "Clear Opcode (APC and Zend Optcache Extension)"
     */
    public function clearOpcodeAction()
    {
        $messages = array();

        if (!Mage::helper('M2ePro/Client_Cache')->isApcAvailable() &&
            !Mage::helper('M2ePro/Client_Cache')->isZendOpcacheAvailable()) {
            $this->_getSession()->addError('Opcode extensions are not installed.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
            return;
        }

        if (Mage::helper('M2ePro/Client_Cache')->isApcAvailable()) {
            $messages[] = 'APC opcode';
            apc_clear_cache('system');
        }

        if (Mage::helper('M2ePro/Client_Cache')->isZendOpcacheAvailable()) {
            $messages[] = 'Zend Optcache';
            // @codingStandardsIgnoreLine
            opcache_reset();
        }

        $this->_getSession()->addSuccess(implode(' and ', $messages) . ' caches are cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
    }

    //########################################
}
