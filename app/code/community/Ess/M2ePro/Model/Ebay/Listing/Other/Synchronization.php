<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization
{
    const RELIST_MODE_NONE = 0;
    const RELIST_MODE_YES = 1;

    const RELIST_FILTER_USER_LOCK_NONE = 0;
    const RELIST_FILTER_USER_LOCK_YES = 1;

    const RELIST_SEND_DATA_NONE = 0;
    const RELIST_SEND_DATA_YES = 1;

    const RELIST_STATUS_ENABLED_NONE = 0;
    const RELIST_STATUS_ENABLED_YES = 1;

    const RELIST_IS_IN_STOCK_NONE = 0;
    const RELIST_IS_IN_STOCK_YES = 1;

    const RELIST_QTY_NONE    = 0;
    const RELIST_QTY_LESS    = 1;
    const RELIST_QTY_BETWEEN = 2;
    const RELIST_QTY_MORE    = 3;

    const REVISE_UPDATE_QTY_NONE = 0;
    const REVISE_UPDATE_QTY_YES  = 1;

    const REVISE_UPDATE_PRICE_NONE = 0;
    const REVISE_UPDATE_PRICE_YES  = 1;

    const REVISE_UPDATE_TITLE_NONE = 0;
    const REVISE_UPDATE_TITLE_YES  = 1;

    const REVISE_UPDATE_DESCRIPTION_NONE = 0;
    const REVISE_UPDATE_DESCRIPTION_YES  = 1;

    const REVISE_UPDATE_SUB_TITLE_NONE = 0;
    const REVISE_UPDATE_SUB_TITLE_YES  = 1;

    const STOP_STATUS_DISABLED_NONE = 0;
    const STOP_STATUS_DISABLED_YES  = 1;

    const STOP_OUT_OFF_STOCK_NONE = 0;
    const STOP_OUT_OFF_STOCK_YES  = 1;

    const STOP_QTY_NONE    = 0;
    const STOP_QTY_LESS    = 1;
    const STOP_QTY_BETWEEN = 2;
    const STOP_QTY_MORE    = 3;

    const MODE_NONE = 0;
    const MODE_YES  = 1;

    // ########################################

    public function getConfigValue($tab, $key)
    {
        $value = Mage::helper('M2ePro/Module')
            ->getSynchronizationConfig()
            ->getGroupValue('/ebay/other_listing/'.$tab.'/', $key);

        return $value;
    }

    // ########################################

    public function isMode()
    {
        return $this->getConfigValue('synchronization', 'mode') != self::MODE_NONE;
    }

    //---------------------------------------

    public function isReviseWhenChangeQty()
    {
        return $this->getConfigValue('revise', 'revise_update_qty') != self::REVISE_UPDATE_QTY_NONE;
    }

    public function isReviseWhenChangePrice()
    {
        return $this->getConfigValue('revise', 'revise_update_price') != self::REVISE_UPDATE_PRICE_NONE;
    }

    public function isReviseWhenChangeTitle()
    {
        return $this->getConfigValue('revise', 'revise_update_title') != self::REVISE_UPDATE_TITLE_NONE;
    }

    public function isReviseWhenChangeSubTitle()
    {
        return $this->getConfigValue('revise', 'revise_update_sub_title') != self::REVISE_UPDATE_SUB_TITLE_NONE;
    }

    public function isReviseWhenChangeDescription()
    {
        return $this->getConfigValue('revise', 'revise_update_description') != self::REVISE_UPDATE_DESCRIPTION_NONE;
    }

    // ########################################

    public function isRelistMode()
    {
        return $this->getConfigValue('relist', 'relist_mode') != self::RELIST_MODE_NONE;
    }

    public function isRelistFilterUserLock()
    {
        return $this->getConfigValue('relist', 'relist_filter_user_lock') != self::RELIST_FILTER_USER_LOCK_NONE;
    }

    public function isRelistSendData()
    {
        return $this->getConfigValue('relist', 'relist_send_data') != self::RELIST_SEND_DATA_NONE;
    }

    public function isRelistStatusEnabled()
    {
        return $this->getConfigValue('relist', 'relist_status_enabled') != self::RELIST_STATUS_ENABLED_NONE;
    }

    public function isRelistIsInStock()
    {
        return $this->getConfigValue('relist', 'relist_is_in_stock') != self::RELIST_IS_IN_STOCK_NONE;
    }

    public function isRelistWhenQtyHasValue()
    {
        return $this->getConfigValue('relist', 'relist_qty') != self::RELIST_QTY_NONE;
    }

    //---------------------------------------

    public function getRelistWhenQtyHasValueType()
    {
        return $this->getConfigValue('relist', 'relist_qty');
    }

    public function getRelistWhenQtyHasValueMin()
    {
        return $this->getConfigValue('relist', 'relist_qty_value');
    }

    public function getRelistWhenQtyHasValueMax()
    {
        return $this->getConfigValue('relist', 'relist_qty_value_max');
    }

    // ########################################

    public function isStopStatusDisabled()
    {
        return $this->getConfigValue('stop', 'stop_status_disabled') != self::STOP_STATUS_DISABLED_NONE;
    }

    public function isStopOutOfStock()
    {
        return $this->getConfigValue('stop', 'stop_out_off_stock') != self::STOP_OUT_OFF_STOCK_NONE;
    }

    public function isStopWhenQtyHasValue()
    {
        return $this->getConfigValue('stop', 'stop_qty') != self::STOP_QTY_NONE;
    }

    //---------------------------------------

    public function getStopWhenQtyHasValueType()
    {
        return $this->getConfigValue('stop', 'stop_qty');
    }

    public function getStopWhenQtyHasValueMin()
    {
        return $this->getConfigValue('stop', 'stop_qty_value');
    }

    public function getStopWhenQtyHasValueMax()
    {
        return $this->getConfigValue('stop', 'stop_qty_value_max');
    }

    //######################################
}