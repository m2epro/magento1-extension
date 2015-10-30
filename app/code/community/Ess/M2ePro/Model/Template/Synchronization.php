<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Template_Synchronization extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const REVISE_CHANGE_LISTING_NONE = 0;
    const REVISE_CHANGE_LISTING_YES  = 1;

    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES  = 1;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Template_Synchronization');
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isReviseListing()
    {
        return (int)$this->getData('revise_change_listing') != self::REVISE_CHANGE_LISTING_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseSellingFormatTemplate()
    {
        return (int)$this->getData('revise_change_selling_format_template') !=
            self::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE;
    }

    //########################################

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

    //########################################
}