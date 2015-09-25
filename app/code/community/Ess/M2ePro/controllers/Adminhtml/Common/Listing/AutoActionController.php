<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Listing_AutoActionController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    /** @var Ess_M2ePro_Model_Listing $listing */
    private $listing = null;

    //#############################################

    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    //#############################################

    public function indexAction()
    {
        //------------------------------
        $autoMode  = $this->getRequest()->getParam('auto_mode');
        $listing   = $this->getListing();

        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);
        //------------------------------

        if (empty($autoMode)) {
            $autoMode = $listing->getChildObject()->getAutoMode();
        }

        $this->loadLayout();

        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
                    $this->getComponent() . '_listing_autoAction_mode_global');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
                    $this->getComponent() . '_listing_autoAction_mode_website');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
                    $this->getComponent() . '_listing_autoAction_mode_category');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_NONE:
            default:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
                    $this->getComponent() . '_listing_autoAction_mode');
                break;
        }

        $this->getResponse()->setBody($block->toHtml());
    }

    // ########################################

    public function getAutoCategoryFormHtmlAction()
    {
        //------------------------------
        $listing = $this->getListing();
        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);
        //------------------------------

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
            $this->getComponent() . '_listing_autoAction_mode_category_form');

        $this->getResponse()->setBody($block->toHtml());
    }

    // ########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return;
        }

        if (!isset($post['auto_action_data'])) {
            return;
        }

        //------------------------------
        $listing = $this->getListing();
        //------------------------------

        $data = json_decode($post['auto_action_data'], true);

        $listingData = array(
            'auto_mode' => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE
        );

        // mode global
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL;
            $listingData['auto_global_adding_mode'] = $data['auto_global_adding_mode'];

            if ($listing->isComponentModeAmazon()) {
                $listingData['auto_global_adding_description_template_id'] = $data['adding_description_template_id'];
            }
        }
        //------------------------------

        // mode website
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE;
            $listingData['auto_website_adding_mode'] = $data['auto_website_adding_mode'];
            $listingData['auto_website_deleting_mode'] = $data['auto_website_deleting_mode'];

            if ($listing->isComponentModeAmazon()) {
                $listingData['auto_website_adding_description_template_id'] = $data['adding_description_template_id'];
            }
        }
        //------------------------------

        // mode category
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY;

            $group = Mage::helper('M2ePro/Component')
                ->getComponentModel($listing->getComponentMode(), 'Listing_Auto_Category_Group');

            if ((int)$data['id'] > 0) {
                $group->loadInstance((int)$data['id']);
            } else {
                unset($data['id']);
            }

            $group->addData($data);
            $group->setData('listing_id', $listing->getId());
            $group->save();
            $group->clearCategories();

            foreach ($data['categories'] as $categoryId) {
                $category = Mage::getModel('M2ePro/Listing_Auto_Category');
                $category->setData('group_id', $group->getId());
                $category->setData('category_id', $categoryId);
                $category->save();
            }
        }
        //------------------------------

        $listing->addData($listingData)->save();
    }

    // ########################################

    public function resetAction()
    {
        //------------------------------
        $listing = $this->getListing();
        //------------------------------

        $data = array(
            'auto_mode' => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE
        );

        if ($listing->isComponentModeAmazon()) {
            $data['auto_global_adding_description_template_id'] = NULL;
            $data['auto_website_adding_description_template_id'] = NULL;
        }

        $listing->addData($data)->save();

        foreach ($listing->getAutoCategoriesGroups(true) as $autoCategoryGroup) {
            $autoCategoryGroup->deleteInstance();
        }
    }

    //#############################################

    public function deleteCategoryAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');
        $categoryId = $this->getRequest()->getParam('category_id');

        $category = Mage::getModel('M2ePro/Listing_Auto_Category')
            ->getCollection()
                ->addFieldToFilter('group_id', (int)$groupId)
                ->addFieldToFilter('category_id', (int)$categoryId)
                ->getFirstItem();

        if (!$category->getId()) {
            return;
        }

        $category->deleteInstance();

        if(Mage::getResourceModel('M2ePro/Listing_Auto_Category_Group')->isEmpty($groupId)) {
            Mage::getModel('M2ePro/Listing_Auto_Category_Group')->loadInstance($groupId)->deleteInstance();
        }
    }

    //#############################################

    public function deleteCategoryGroupAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        Mage::getModel('M2ePro/Listing_Auto_Category_Group')
            ->loadInstance($groupId)
            ->deleteInstance();
    }

    //#############################################

    public function isCategoryGroupTitleUniqueAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $groupId = $this->getRequest()->getParam('group_id');
        $title = $this->getRequest()->getParam('title');

        if ($title == '') {
            return $this->getResponse()->setBody(json_encode(array('unique' => false)));
        }

        $collection = Mage::getModel('M2ePro/Listing_Auto_Category_Group')
            ->getCollection()
                ->addFieldToFilter('listing_id', $listingId)
                ->addFieldToFilter('title', $title);

        if ($groupId) {
            $collection->addFieldToFilter('id', array('neq' => $groupId));
        }

        return $this->getResponse()->setBody(json_encode(array('unique' => !(bool)$collection->getSize())));
    }

    //#############################################

    public function getCategoryGroupGridAction()
    {
        $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_common_' .
            $this->getComponent() . '_listing_autoAction_mode_category_group_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################

    private function getListing()
    {
        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component')
                ->getCachedUnknownObject('Listing', $this->getRequest()->getParam('listing_id'));
        }

        return $this->listing;
    }

    private function getComponent()
    {
        return $this->getRequest()->getParam('component');
    }

    //#############################################
}