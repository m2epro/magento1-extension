<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Chooser_Tabs_Browse
    extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateDescriptionCategoryChooserBrowse');
        //------------------------------

        // Set template
        //------------------------------
        $this->setTemplate('M2ePro/common/amazon/template/description/category/chooser/tabs/browse.phtml');
        // -----------------------------
    }

    // ########################################
}