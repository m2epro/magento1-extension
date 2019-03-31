<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add_Item_ItemsCountAlertPopup
    extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMotorAddTabItemItemsCountAlertPopup');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/motor/add/tab/Item/items_count_alert_popup.phtml');
    }

    //########################################
}