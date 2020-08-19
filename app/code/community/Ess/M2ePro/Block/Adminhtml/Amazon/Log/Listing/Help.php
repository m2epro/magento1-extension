<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Log_Listing_Help extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonLogListingHelp');
        $this->setTemplate('M2ePro/amazon/log/listing/help.phtml');
    }

    //########################################
}
