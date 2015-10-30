<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_MainAbstract extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    //########################################

    protected function _beforeToHtml()
    {
        // Initialization block
        // ---------------------------------------
        $this->setId($this->getWizardBlockId());

        // Set header text
        // ---------------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__($this->getHeaderTextHtml());

        // Buttons
        // ---------------------------------------
        $this->removeButtons();

        if ($this->isNeedSkipButton()) {
            $url = $this->getUrl('*/*/skip');
            $this->_addButton('skip', array(
                'label'     => Mage::helper('M2ePro')->__('Skip Wizard'),
                'onclick'   => 'WizardHandlerObj.skip(\''.$url.'\')',
                'class'     => 'skip'
            ));
        }

        $this->setTemplate('widget/form/container.phtml');

        return parent::_beforeToHtml();
    }

    //########################################

    protected function getWizardBlockId()
    {
        $className = explode('_', get_class($this));
        $className = array_pop($className);
        return 'wizard' . $this->getNick() . ucfirst($className);
    }

    protected function getHeaderTextHtml()
    {
        return '';
    }

    //########################################

    protected function removeButtons()
    {
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    // ---------------------------------------

    protected function isNeedSkipButton()
    {
        return Mage::helper('M2ePro/Module_Wizard')->getType($this->getNick()) !=
               Ess_M2ePro_Helper_Module_Wizard::TYPE_BLOCKER;
    }

    //########################################
}