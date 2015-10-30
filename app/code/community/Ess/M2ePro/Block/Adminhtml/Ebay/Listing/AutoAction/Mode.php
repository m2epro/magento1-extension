<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingAutoActionMode');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode.phtml');
    }

    //########################################

    public function getHelpPageUrl()
    {
        return Mage::helper('M2ePro/Module_Support')
            ->getDocumentationUrl(NULL, 'pages/viewpage.action?pageId=17367107');
    }

    //########################################
}
