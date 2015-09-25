<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationToV6_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    // ########################################

    abstract protected function getStep();

    // ########################################

    protected function _beforeToHtml()
    {
        // Initialization block
        //------------------------------
        $this->setId('wizard' . $this->getNick() . $this->getStep());
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationToV6/container.phtml');

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################

    protected function _toHtml()
    {
        $contentBlock = Mage::helper('M2ePro/Module_Wizard')->createBlock(
            'installation_' . $this->getStep() . '_content',
            $this->getNick()
        );

        return parent::_toHtml() . $contentBlock->toHtml();
    }

    // ########################################
}