<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Edit_Selling_Help extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateEditSellingHelp');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/edit/selling/help.phtml');
    }

    // ####################################

    public function isEditMode()
    {
        return !!$this->getRequest()->getParam('id');
    }

    // ####################################
}