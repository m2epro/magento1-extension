<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_AutoAction_Mode_Global
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Global
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/auto_action/mode/global.phtml');
    }

    //########################################
}
