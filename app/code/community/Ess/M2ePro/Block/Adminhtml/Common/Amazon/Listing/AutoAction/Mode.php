<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_AutoAction_Mode
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode
{
    //########################################

    public function getHelpPageUrl()
    {
        return Mage::helper('M2ePro/Module_Support')
            ->getDocumentationUrl(NULL, 'pages/viewpage.action?pageId=18188381');
    }

    //########################################
}
