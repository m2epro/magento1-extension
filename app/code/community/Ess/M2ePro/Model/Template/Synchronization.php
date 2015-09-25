<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Template_Synchronization extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const REVISE_CHANGE_LISTING_NONE = 0;
    const REVISE_CHANGE_LISTING_YES  = 1;

    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES  = 1;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_Synchronization');
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    //-----------------------------------------

    public function isReviseListing()
    {
        return (int)$this->getData('revise_change_listing') != self::REVISE_CHANGE_LISTING_NONE;
    }

    public function isReviseSellingFormatTemplate()
    {
        return (int)$this->getData('revise_change_selling_format_template') !=
            self::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE;
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::delete();
    }

    // ########################################
}