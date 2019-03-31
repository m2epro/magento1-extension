<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Websites_Update extends Ess_M2ePro_Model_Abstract
{
    const ACTION_ADD = 1;
    const ACTION_REMOVE = 2;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Magento_Product_Websites_Update');
    }

    //########################################

    public function getProductId()
    {
        return $this->getData('product_id');
    }

    //----------------------------------------

    public function getAction()
    {
        return $this->getData('action');
    }

    public function isActionAdd()
    {
        return (int)$this->getData('action') == self::ACTION_ADD;
    }

    public function isActionRemove()
    {
        return (int)$this->getData('action') == self::ACTION_REMOVE;
    }

    //----------------------------------------

    public function getWebsiteId()
    {
        return $this->getData('website_id');
    }

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    //########################################
}