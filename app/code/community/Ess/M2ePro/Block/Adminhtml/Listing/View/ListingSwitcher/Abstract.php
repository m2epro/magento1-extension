<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_View_ListingSwitcher_Abstract
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        $this->setAddListingUrl('');

        $this->setTemplate('M2ePro/listing/view/listing_switcher.phtml');
    }

    // ####################################
}