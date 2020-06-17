<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_AutoAction_Mode_Website
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_WebsiteAbstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonListingAutoActionModeWebsite');
        $this->setTemplate('M2ePro/amazon/listing/auto_action/mode/website.phtml');
    }

    //########################################
}
