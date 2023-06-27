<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_AutoAction_Mode
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_ModeAbstract
{
    //########################################

    public function getHelpPageUrl()
    {
        return Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(null, null, "auto-add-remove-rules");
    }

    //########################################
}
