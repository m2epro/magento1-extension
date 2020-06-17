<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser_Tabs_Attribute
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayCategoryChooserCategoryAttribute');
        $this->setTemplate('M2ePro/ebay/template/category/chooser/tabs/attribute.phtml');
    }

    //########################################
}
