<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_AutoAction_Mode_Category_Form
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_FormAbstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/listing/auto_action/mode/category/form.phtml');
    }

    //########################################

    public function getDefault()
    {
        return array_merge(
            parent::getDefault(),
            array(
                'adding_description_template_id' => null
            )
        );
    }

    //########################################
}
