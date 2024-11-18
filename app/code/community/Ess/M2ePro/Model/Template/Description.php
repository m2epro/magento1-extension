<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Description as EbayTemplateDescription;
use Ess_M2ePro_Model_Walmart_Template_Description as WalmartTemplateDescription;

/**
 * @method EbayTemplateDescription|WalmartTemplateDescription getChildObject()
 */
class Ess_M2ePro_Model_Template_Description extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_Description');
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
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_description');
        return parent::delete();
    }

    //########################################
}
