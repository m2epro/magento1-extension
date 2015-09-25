<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Buy_Welcome_Content extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardWelcomeContent');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/buy/welcome/content.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $step = $this->helper('M2ePro/Module_Wizard')->getWizard($this->getNick())->getFirstStep();
        $status = Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE;
        $callback = 'function() { setLocation(\''.$this->getUrl('*/adminhtml_wizard_'.$this->getNick()).'\'); }';
        $callback = 'function() { WizardHandlerObj.setStep(\''.$step.'\', '.$callback.'); }';

        $confirmMessage = Mage::helper('M2ePro')->escapeJs(
            Mage::helper('M2ePro')->__(
'It is strongly recommended to watch 6 min. video tutorial before starting configuration.
Would you like to watch the video?'
            )
        );

        $onClick = <<<JS
if (!isTutorialFinished && confirm('{$confirmMessage}')) {
    return $('tutorial_image_container').simulate('click');
}
WizardHandlerObj.setStatus('{$status}', {$callback});
JS;

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Start Configuration'),
                'onclick' => $onClick,
                'class' => 'start_installation_button'
            ) );

        $this->setChild('start_wizard_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}