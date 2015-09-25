<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Listing_AutoAction_Mode
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode
{
    // ####################################

    public function getHelpPageUrl()
    {
        return Mage::helper('M2ePro/Module_Support')
            ->getDocumentationUrl(NULL, 'pages/viewpage.action?pageId=18189166');
    }

    // ####################################
}
