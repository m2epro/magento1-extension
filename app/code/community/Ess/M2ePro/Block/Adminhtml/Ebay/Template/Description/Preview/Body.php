<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Description_Preview_Body extends Mage_Adminhtml_Block_Widget
{
    //#############################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateDescriptionPreviewBody');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/description/preview/body.phtml');
    }

    //#############################################
}