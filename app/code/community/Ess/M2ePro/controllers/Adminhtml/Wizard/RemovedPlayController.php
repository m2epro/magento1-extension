<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Wizard_RemovedPlayController
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //########################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Amazon::NICK;
    }

    protected function getMenuRootNodeNick()
    {
        return Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK;
    }

    protected function getMenuRootNodeLabel()
    {
        return Mage::helper('M2ePro/View_Amazon')->getMenuRootNodeLabel();
    }

    //########################################

    protected function getNick()
    {
        return 'removedPlay';
    }

    //########################################

    public function installationAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED);

        return $this->_redirect('*/adminhtml_amazon_listing/index/');
    }

    public function congratulationAction()
    {
        return $this->_redirect('*/adminhtml_amazon_listing/index/');
    }

    //########################################
}
