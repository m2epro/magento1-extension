<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_AbstractWizard
{
    //########################################

    abstract protected function getStep();

    //########################################

    protected function _beforeToHtml()
    {
        $this->setId('wizard' . $this->getNick() . $this->getStep());
        $this->_headerText = Mage::helper('M2ePro')->__($this->getHeaderTextHtml());

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->addButton(
            'continue',
            array(
                'label' => Mage::helper('M2ePro')->__('Continue'),
                'class' => 'primary forward',
                'id'    => 'continue'
            ),
            1,
            0
        );

        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        Mage::helper('M2ePro/View')->getJsUrlsRenderer()->addControllerActions(
            "adminhtml_wizard_{$this->getNick()}"
        );

        return parent::_prepareLayout();
    }

    protected function _toHtml()
    {
        /** @var Ess_M2ePro_Block_Adminhtml_Widget_Breadcrumb $stepsBlock */
        $stepsBlock = Mage::helper('M2ePro/Module_Wizard')->createBlock('breadcrumb', $this->getNick());
        $stepsBlock->setSelectedStep($this->getStep());

        $helpBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_helpBlock',
            'wizard.help.block',
            array(
                'no_collapse' => true,
                'no_hide'     => true
            )
        );

        $contentBlock = Mage::helper('M2ePro/Module_Wizard')->createBlock(
            "installation_{$this->getStep()}_content",
            $this->getNick()
        );

        return parent::_toHtml()
            . $stepsBlock->toHtml()
            . $helpBlock->toHtml()
            . $contentBlock->toHtml();
    }

    //########################################

    protected function getHeaderTextHtml()
    {
        return '';
    }

    //########################################
}
