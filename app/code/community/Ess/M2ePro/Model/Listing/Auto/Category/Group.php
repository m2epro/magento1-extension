<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Auto_Category_Group');
    }

    //########################################

    /**
     * @return int
     */
    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    //########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    //########################################

    /**
     * @return int
     */
    public function getAddingMode()
    {
        return (int)$this->getData('adding_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAddingModeNone()
    {
        return $this->getAddingMode() == Ess_M2ePro_Model_Listing::ADDING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isAddingModeAdd()
    {
        return $this->getAddingMode() == Ess_M2ePro_Model_Listing::ADDING_MODE_ADD;
    }

    /**
     * @return bool
     */
    public function isAddingAddNotVisibleYes()
    {
        return $this->getData('adding_add_not_visible') == Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES;
    }

    //########################################

    /**
     * @return int
     */
    public function getDeletingMode()
    {
        return (int)$this->getData('deleting_mode');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isDeletingModeNone()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isDeletingModeStop()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP;
    }

    /**
     * @return bool
     */
    public function isDeletingModeStopRemove()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE;
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array|Ess_M2ePro_Model_Abstract[]
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCategories($asObjects = false, array $filters = array())
    {
        return $this->getRelatedSimpleItems('Listing_Auto_Category','group_id', $asObjects, $filters);
    }

    public function clearCategories()
    {
        $categories = $this->getCategories(true);
        foreach ($categories as $category) {
            $category->deleteInstance();
        }
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $items = $this->getRelatedSimpleItems('Listing_Auto_Category', 'group_id', true);
        foreach ($items as $item) {
            $item->deleteInstance();
        }

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################
}