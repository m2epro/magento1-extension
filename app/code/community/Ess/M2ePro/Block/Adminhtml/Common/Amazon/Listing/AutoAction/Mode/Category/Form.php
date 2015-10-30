<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_AutoAction_Mode_Category_Form
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/amazon/listing/auto_action/mode/category/form.phtml');
    }

    //########################################

    public function getDefault()
    {
        return array(
            'id' => NULL,
            'title' => NULL,
            'category_id' => NULL,
            'adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE,
            'adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'adding_description_template_id' => NULL
        );
    }

    //########################################
}
