<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Edit_Synchronization_Help extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateEditSynchronizationHelp');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/edit/synchronization/help.phtml');
    }

    //########################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    //########################################
}