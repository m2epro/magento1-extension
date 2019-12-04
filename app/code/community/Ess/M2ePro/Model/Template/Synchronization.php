<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Template_Synchronization as AmazonTemplateSynchronization;
use Ess_M2ePro_Model_Ebay_Template_Synchronization as EbayTemplateSynchronization;
use Ess_M2ePro_Model_Walmart_Template_Synchronization as WalmartTemplateSynchronization;

/**
 * @method AmazonTemplateSynchronization|EbayTemplateSynchronization|WalmartTemplateSynchronization getChildObject()
 */
class Ess_M2ePro_Model_Template_Synchronization extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const QTY_MODE_NONE    = 0;
    const QTY_MODE_LESS    = 1;
    const QTY_MODE_BETWEEN = 2;
    const QTY_MODE_MORE    = 3;

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
