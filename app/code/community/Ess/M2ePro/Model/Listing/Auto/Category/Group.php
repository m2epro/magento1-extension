<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Auto_Category_Group');
    }

    // #######################################

    public function getListingId()
    {
        return (int)$this->getData('listing_id');
    }

    // ########################################

    public function getTitle()
    {
        return $this->getData('title');
    }

    // #######################################

    public function getAddingMode()
    {
        return (int)$this->getData('adding_mode');
    }

    //----------------------------------------

    public function isAddingModeNone()
    {
        return $this->getAddingMode() == Ess_M2ePro_Model_Listing::ADDING_MODE_NONE;
    }

    public function isAddingModeAdd()
    {
        return $this->getAddingMode() == Ess_M2ePro_Model_Listing::ADDING_MODE_ADD;
    }

    // #######################################

    public function getDeletingMode()
    {
        return (int)$this->getData('deleting_mode');
    }

    //----------------------------------------

    public function isDeletingModeNone()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_NONE;
    }

    public function isDeletingModeStop()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP;
    }

    public function isDeletingModeStopRemove()
    {
        return $this->getDeletingMode() == Ess_M2ePro_Model_Listing::DELETING_MODE_STOP_REMOVE;
    }

    // #######################################

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

    // #######################################

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

    // #######################################
}