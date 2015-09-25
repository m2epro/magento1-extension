<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_MigrationNewAmazon_Installation_Information
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationInformation');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/migrationNewAmazon/installation/information.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $callback = 'function() {
            $(\'wizard_complete\').show()
        }';
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                    'label'   => Mage::helper('M2ePro')->__('Confirm'),
                    'onclick' => 'WizardHandlerObj.skipStep(\'information\', '.$callback.');',
                    'class' => 'process_information_button'
                ) );
        $this->setChild('process_information_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}