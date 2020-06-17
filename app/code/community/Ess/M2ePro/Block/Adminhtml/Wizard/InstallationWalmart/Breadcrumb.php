<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationWalmart_Breadcrumb
    extends Ess_M2ePro_Block_Adminhtml_Widget_Breadcrumb
{
    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->setSteps(
            array(
                array(
                    'id'          => 'registration',
                    'title'       => Mage::helper('M2ePro')->__('Step 1'),
                    'description' => Mage::helper('M2ePro')->__('Module Registration'),
                ),
                array(
                    'id'          => 'account',
                    'title'       => Mage::helper('M2ePro')->__('Step 2'),
                    'description' => Mage::helper('M2ePro')->__('Account Onboarding'),
                ),
                array(
                    'id'          => 'settings',
                    'title'       => Mage::helper('M2ePro')->__('Step 3'),
                    'description' => Mage::helper('M2ePro')->__('General Settings'),
                ),
                array(
                    'id'          => 'listingTutorial',
                    'title'       => Mage::helper('M2ePro')->__('Step 4'),
                    'description' => Mage::helper('M2ePro')->__('First Listing Creation'),
                ),
            )
        );
    }

    //########################################
}
