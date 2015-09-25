<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingAutoActionMode');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode.phtml');
    }

    // ####################################

    public function getHelpPageUrl()
    {
        return Mage::helper('M2ePro/Module_Support')
            ->getDocumentationUrl(NULL, 'pages/viewpage.action?pageId=17367107');
    }

    // ####################################
}
