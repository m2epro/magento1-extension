<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Presentation extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    // ########################################

    protected function _beforeToHtml()
    {
        $url = $this->getUrl('*/adminhtml_wizard_'.$this->getNick());

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => 'Proceed',
                'onclick' => 'setLocation(\''.$url.'\');',
            ) );

        $this->setChild('continue_button',$buttonBlock);

        $this->setTemplate('M2ePro/wizard/'.$this->getNick().'/presentation.phtml');

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}