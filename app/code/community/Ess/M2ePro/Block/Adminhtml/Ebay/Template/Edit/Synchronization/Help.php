<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Edit_Synchronization_Help extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateEditSynchronizationHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/edit/synchronization/help.phtml');
    }

    // ####################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    // ####################################
}