<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Notification extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    // ########################################

    protected function _beforeToHtml()
    {
        $this->setTemplate('M2ePro/wizard/'.$this->getNick().'/notification.phtml');

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}
