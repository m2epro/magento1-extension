<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_AutoActionController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    //#############################################

    public function indexAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $autoMode  = $this->getRequest()->getParam('auto_mode');
        $listing   = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);

        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);
        //------------------------------

        if (empty($autoMode)) {
            $autoMode = $listing->getChildObject()->getAutoMode();
        }

        $this->loadLayout();

        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_global');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_website');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_category');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_NONE:
            default:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode');
                break;
        }

        $this->getResponse()->setBody($block->toHtml());
    }

    // ########################################

    public function getCategoryChooserHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $groupId = $this->getRequest()->getParam('group_id');
        $autoMode  = $this->getRequest()->getParam('auto_mode');
        $listing   = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $template = $this->getCategoryTemplate($autoMode, $groupId, $listing);
        $otherTemplate = $this->getOtherCategoryTemplate($autoMode, $groupId, $listing);

        $this->loadLayout();

        /* @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('data_container');
        $chooserBlock->setAccountId($listing->getAccountId());
        $chooserBlock->setMarketplaceId($listing->getMarketplaceId());

        if (!is_null($template)) {
            $data = $template->getData();
            $otherTemplate && $data = array_merge($data, $otherTemplate->getData());

            $chooserBlock->setInternalData($data);
        }

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    public function getCategorySpecificHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $groupId = $this->getRequest()->getParam('group_id');
        $autoMode = $this->getRequest()->getParam('auto_mode');
        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $this->loadLayout();

        /* @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());
        $specific->setCategoryMode($categoryMode);
        $specific->setCategoryValue($categoryValue);

        $categoryWasChanged = false;

        $template = $this->getCategoryTemplate($autoMode, $groupId, $listing);

        if (!$template) {
            $categoryWasChanged = true;
        } else {
            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY &&
                $template->getData('category_main_id') != $categoryValue) {
                $categoryWasChanged = true;
            }

            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY &&
                $template->getData('category_main_id') != $categoryValue) {
                $categoryWasChanged = true;
            }
        }

        if ($categoryWasChanged) {
            $templateData = array(
                'category_main_id'        => 0,
                'category_main_mode'      => $categoryMode,
                'category_main_attribute' => '',
                'marketplace_id'          => $listing->getMarketplaceId()
            );

            if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $templateData['category_main_id'] = $categoryValue;
            } else if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
                $templateData['category_main_attribute'] = $categoryValue;
            }

            $existingTemplates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()
                ->getItemsByPrimaryCategories(array($templateData));

            $template = reset($existingTemplates);
        }

        if ($template) {
            $specific->setInternalData($template->getData());
            $specific->setSelectedSpecifics($template->getSpecifics());
        }

        $this->getResponse()->setBody($specific->toHtml());
    }

    private function getCategoryTemplate($autoMode, $groupId, $listing)
    {
        $template = NULL;

        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                $template = $listing->getChildObject()->getAutoGlobalAddingCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                $template = $listing->getChildObject()->getAutoWebsiteAddingCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                if ($magentoCategoryId = $this->getRequest()->getParam('magento_category_id')) {
                    $autoCategory = Mage::getModel('M2ePro/Listing_Auto_Category')
                        ->getCollection()
                            ->addFieldToFilter('group_id', $groupId)
                            ->addFieldToFilter('category_id', $magentoCategoryId)
                            ->getFirstItem();

                    if ($autoCategory->getId()) {
                        $template = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')
                            ->loadInstance($groupId)->getCategoryTemplate();
                    }
                }
                break;
        }

        return $template;
    }

    private function getOtherCategoryTemplate($autoMode, $groupId, $listing)
    {
        $template = NULL;

        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                $template = $listing->getChildObject()->getAutoGlobalAddingOtherCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                $template = $listing->getChildObject()->getAutoWebsiteAddingOtherCategoryTemplate();
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                if ($magentoCategoryId = $this->getRequest()->getParam('magento_category_id')) {
                    $autoCategory = Mage::getModel('M2ePro/Listing_Auto_Category')
                        ->getCollection()
                            ->addFieldToFilter('group_id', $groupId)
                            ->addFieldToFilter('category_id', $magentoCategoryId)
                            ->getFirstItem();

                    if ($autoCategory->getId()) {
                        $template = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')
                            ->loadInstance($groupId)->getOtherCategoryTemplate();
                    }
                }
                break;
        }

        return $template;
    }

    // ########################################

    public function getAutoCategoryFormHtmlAction()
    {
        //------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_listing', $listing);
        //------------------------------

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_category_form');

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
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $data = json_decode($post['auto_action_data'], true);

        if (isset($data['template_category_data'])) {
            Mage::helper('M2ePro/Component_Ebay_Category')->fillCategoriesPaths(
                $data['template_category_data'], $listing
            );
        }

        $listingData = array(
            'auto_mode' => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_global_adding_template_category_id' => NULL,
            'auto_global_adding_template_other_category_id' => NULL,
            'auto_website_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_template_category_id' => NULL,
            'auto_website_adding_template_other_category_id' => NULL,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE
        );

        $addingModeAddAndAssignCategory = Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;

        // mode global
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL;
            $listingData['auto_global_adding_mode'] = $data['auto_global_adding_mode'];

            if ($data['auto_global_adding_mode'] == $addingModeAddAndAssignCategory) {
                $builderData = $data['template_category_data'];
                $builderData['marketplace_id'] = $listing->getMarketplaceId();
                $builderData['account_id'] = $listing->getAccountId();
                $builderData['specifics'] = $data['template_category_specifics_data']['specifics'];

                $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
                $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build(
                    $builderData
                );

                $listingData['auto_global_adding_template_category_id'] = $categoryTemplate->getId();
                $listingData['auto_global_adding_template_other_category_id'] = $otherCategoryTemplate->getId();
            }
        }
        //------------------------------

        // mode website
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE;
            $listingData['auto_website_adding_mode'] = $data['auto_website_adding_mode'];
            $listingData['auto_website_deleting_mode'] = $data['auto_website_deleting_mode'];

            if ($data['auto_website_adding_mode'] == $addingModeAddAndAssignCategory) {
                $builderData = $data['template_category_data'];
                $builderData['marketplace_id'] = $listing->getMarketplaceId();
                $builderData['account_id'] = $listing->getAccountId();
                $builderData['specifics'] = $data['template_category_specifics_data']['specifics'];

                $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
                $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build(
                    $builderData
                );

                $listingData['auto_website_adding_template_category_id'] = $categoryTemplate->getId();
                $listingData['auto_website_adding_template_other_category_id'] = $otherCategoryTemplate->getId();
            }
        }
        //------------------------------

        // mode category
        //------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY;

            $group = Mage::helper('M2ePro/Component')
                ->getComponentModel($this->getCustomViewNick(), 'Listing_Auto_Category_Group');

            if ((int)$data['id'] > 0) {
                $group->loadInstance((int)$data['id']);
            } else {
                unset($data['id']);
            }

            $group->addData($data);
            $group->setData('listing_id', $listingId);

            if ($data['adding_mode'] == Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $builderData = $data['template_category_data'];
                $builderData['marketplace_id'] = $listing->getMarketplaceId();
                $builderData['account_id'] = $listing->getAccountId();
                $builderData['specifics'] = $data['template_category_specifics_data']['specifics'];

                $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
                $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build(
                    $builderData
                );

                $group->setData('adding_template_category_id', $categoryTemplate->getId());
                $group->setData('adding_template_other_category_id', $otherCategoryTemplate->getId());
            } else {
                $group->setData('adding_template_category_id', NULL);
                $group->setData('adding_template_other_category_id', NULL);
            }

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
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        //------------------------------

        $data = array(
            'auto_mode' => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_global_adding_template_category_id' => NULL,
            'auto_global_adding_template_other_category_id' => NULL,
            'auto_website_adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_template_category_id' => NULL,
            'auto_website_adding_template_other_category_id' => NULL,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE
        );

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
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_category_group_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################
}