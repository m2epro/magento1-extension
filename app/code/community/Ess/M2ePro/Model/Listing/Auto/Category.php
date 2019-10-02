<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Auto_Category extends Ess_M2ePro_Model_Component_Abstract
{
    /** @var Ess_M2ePro_Model_Listing_Auto_Category_Group $_group */
    protected $_group = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Auto_Category');
    }

    //########################################

    /**
     * @return int
     */
    public function getGroupId()
    {
        return (int)$this->getData('group_id');
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->getData('category_id');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Auto_Category_Group
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getGroup()
    {
        if ($this->getGroupId() <= 0) {
            throw new Ess_M2ePro_Model_Exception_Logic('Group ID was not set.');
        }

        if ($this->_group !== null) {
            return $this->_group;
        }

        return $this->_group = Mage::helper('M2ePro/Component')->getUnknownObject(
            'Listing_Auto_Category_Group', $this->getGroupId()
        );
    }

    //########################################
}
