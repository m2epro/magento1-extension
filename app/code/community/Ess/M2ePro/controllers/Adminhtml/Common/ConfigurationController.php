<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_ConfigurationController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('Global'));

        $this->setComponentPageHelpLink('Global+Settings');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration');
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock(
                    'M2ePro/adminhtml_common_configuration', '',
                    array('active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Configuration_Tabs::TAB_ID_GLOBAL)
                )
            )->renderLayout();
    }

    //########################################
}