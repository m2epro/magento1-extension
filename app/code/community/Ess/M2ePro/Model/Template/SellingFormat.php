<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_SellingFormat as AmazonTemplateSellingFormat;
use Ess_M2ePro_Model_Ebay_Template_SellingFormat as EbayTemplateSellingFormat;
use Ess_M2ePro_Model_Walmart_Template_SellingFormat as WalmartTemplateSellingFormat;

/**
 * @method AmazonTemplateSellingFormat|EbayTemplateSellingFormat|WalmartTemplateSellingFormat getChildObject()
 */
class Ess_M2ePro_Model_Template_SellingFormat extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const QTY_MODE_PRODUCT       = 1;
    const QTY_MODE_SINGLE        = 2;
    const QTY_MODE_NUMBER        = 3;
    const QTY_MODE_ATTRIBUTE     = 4;
    const QTY_MODE_PRODUCT_FIXED = 5;

    const PRICE_MODE_NONE      = 0;
    const PRICE_MODE_PRODUCT   = 1;
    const PRICE_MODE_SPECIAL   = 2;
    const PRICE_MODE_ATTRIBUTE = 3;
    const PRICE_MODE_TIER      = 4;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_SellingFormat');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

    public function getCreateDate()
    {
        return $this->getData('create_date');
    }

    public function getUpdateDate()
    {
        return $this->getData('update_date');
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::delete();
    }

    //########################################
}
