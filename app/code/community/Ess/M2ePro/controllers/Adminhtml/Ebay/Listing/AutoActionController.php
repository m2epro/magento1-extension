<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing as Listing;
use Ess_M2ePro_Model_Ebay_Listing as eBayListing;
use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;

class Ess_M2ePro_Adminhtml_Ebay_Listing_AutoActionController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );
        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);

        $autoMode = $this->getRequest()->getParam('auto_mode');
        empty($autoMode) && $autoMode = $listing->getAutoMode();

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

    //########################################

    public function getCategoryChooserHtmlAction()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        $magentoCategoryId = $this->getRequest()->getParam('magento_category_id');
        $this->loadLayout();

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $converter->setAccountId($listing->getAccountId());
        $converter->setMarketplaceId($listing->getMarketplaceId());

        $categoryTemplate = $this->getCategoryTemplate(
            $listing,
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN,
            $this->getRequest()->getParam('auto_mode'),
            $this->getRequest()->getParam('group_id'),
            $magentoCategoryId
        );
        if ($categoryTemplate !== null) {
            $converter->setCategoryDataFromTemplate(
                $categoryTemplate->getData(),
                Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN
            );
        }

        $categorySecondaryTemplate = $this->getCategoryTemplate(
            $listing,
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY,
            $this->getRequest()->getParam('auto_mode'),
            $this->getRequest()->getParam('group_id'),
            $magentoCategoryId
        );
        if ($categorySecondaryTemplate !== null) {
            $converter->setCategoryDataFromTemplate(
                $categorySecondaryTemplate->getData(),
                Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_SECONDARY
            );
        }

        $storeTemplate = $this->getStoreCategoryTemplate(
            $listing,
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN,
            $this->getRequest()->getParam('auto_mode'),
            $this->getRequest()->getParam('group_id'),
            $magentoCategoryId
        );
        if ($storeTemplate !== null) {
            $converter->setCategoryDataFromTemplate(
                $storeTemplate->getData(),
                Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN
            );
        }

        $storeSecondaryTemplate = $this->getStoreCategoryTemplate(
            $listing,
            Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY,
            $this->getRequest()->getParam('auto_mode'),
            $this->getRequest()->getParam('group_id'),
            $magentoCategoryId
        );
        if ($storeSecondaryTemplate !== null) {
            $converter->setCategoryDataFromTemplate(
                $storeSecondaryTemplate->getData(),
                Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_SECONDARY
            );
        }

        /** @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_category_chooser');
        $chooserBlock->setAccountId($listing->getAccountId());
        $chooserBlock->setMarketplaceId($listing->getMarketplaceId());
        $chooserBlock->setCategoriesData($converter->getCategoryDataForChooser());

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @param int $categoryType
     * @param $autoMode
     * @param int $groupId
     * @param int $magentoCategoryId
     * @return Ess_M2ePro_Model_Ebay_Template_Category|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getCategoryTemplate($listing, $categoryType, $autoMode, $groupId, $magentoCategoryId)
    {
        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN) {
                    return $listing->getChildObject()->getAutoGlobalAddingCategoryTemplate();
                }
                return $listing->getChildObject()->getAutoGlobalAddingCategorySecondaryTemplate();

            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN) {
                    return $listing->getChildObject()->getAutoWebsiteAddingCategoryTemplate();
                }
                return $listing->getChildObject()->getAutoWebsiteAddingCategorySecondaryTemplate();

            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                if ($magentoCategoryId) {
                    /** @var Ess_M2ePro_Model_Listing_Auto_Category $autoCategory */
                    $autoCategory = Mage::getModel('M2ePro/Listing_Auto_Category')->getCollection()
                        ->addFieldToFilter('group_id', $groupId)
                        ->addFieldToFilter('category_id', $magentoCategoryId)
                        ->getFirstItem();

                    if ($autoCategory->getId()) {
                        $template = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')->loadInstance($groupId);
                        if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_EBAY_MAIN) {
                            return $template->getCategoryTemplate();
                        }

                        return $template->getCategorySecondaryTemplate();
                    }
                }
                return null;
        }

        return null;
    }

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @param int $categoryType
     * @param $autoMode
     * @param int $groupId
     * @param int $magentoCategoryId
     * @return Ess_M2ePro_Model_Ebay_Template_StoreCategory|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getStoreCategoryTemplate($listing, $categoryType, $autoMode, $groupId, $magentoCategoryId)
    {
        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN) {
                    return $listing->getChildObject()->getAutoGlobalAddingStoreCategoryTemplate();
                }
                return $listing->getChildObject()->getAutoGlobalAddingStoreCategorySecondaryTemplate();

            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN) {
                    return $listing->getChildObject()->getAutoWebsiteAddingStoreCategoryTemplate();
                }
                return $listing->getChildObject()->getAutoWebsiteAddingStoreCategorySecondaryTemplate();

            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                if ($magentoCategoryId) {
                    /** @var Ess_M2ePro_Model_Listing_Auto_Category $autoCategory */
                    $autoCategory = Mage::getModel('M2ePro/Listing_Auto_Category')->getCollection()
                        ->addFieldToFilter('group_id', $groupId)
                        ->addFieldToFilter('category_id', $magentoCategoryId)
                        ->getFirstItem();

                    if ($autoCategory->getId()) {
                        $template = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group')->loadInstance($groupId);
                        if ($categoryType == Ess_M2ePro_Helper_Component_Ebay_Category::TYPE_STORE_MAIN) {
                            return $template->getStoreCategoryTemplate();
                        }

                        return $template->getStoreCategorySecondaryTemplate();
                    }
                }
                return null;
        }

        return null;
    }

    //########################################

    public function getAutoCategoryFormHtmlAction()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );
        Mage::helper('M2ePro/Data_Global')->setValue('ebay_listing', $listing);

        $this->loadLayout();

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_autoAction_mode_category_form'
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function saveAction()
    {
        $data = Mage::helper('M2ePro')->jsonDecode(
            $this->getRequest()->getPost('auto_action_data')
        );

        if ($data === null) {
            return;
        }

        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        /** @var Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter $converter */
        $converter = Mage::getModel('M2ePro/Ebay_Template_Category_Chooser_Converter');
        $converter->setAccountId($listing->getAccountId());
        $converter->setMarketplaceId($listing->getMarketplaceId());
        foreach ($data['template_category_data'] as $type => $templateData) {
            $converter->setCategoryDataFromChooser($templateData, $type);
        }

        $ebayTpl = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_Category'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_EBAY_MAIN)
        );
        $ebaySecondaryTpl = Mage::getModel('M2ePro/Ebay_Template_Category_Builder') ->build(
            Mage::getModel('M2ePro/Ebay_Template_Category'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_EBAY_SECONDARY)
        );
        $storeTpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_StoreCategory'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_STORE_MAIN)
        );
        $storeSecondaryTpl = Mage::getModel('M2ePro/Ebay_Template_StoreCategory_Builder')->build(
            Mage::getModel('M2ePro/Ebay_Template_StoreCategory'),
            $converter->getCategoryDataForTemplate(eBayCategory::TYPE_STORE_SECONDARY)
        );

        $listingData = array(
            'auto_mode'                                      => Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode'                        => Listing::ADDING_MODE_NONE,
            'auto_global_adding_add_not_visible'             => Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_adding_template_category_id'                 => null,
            'auto_global_adding_template_category_secondary_id'       => null,
            'auto_global_adding_template_store_category_id'           => null,
            'auto_global_adding_template_store_category_secondary_id' => null,

            'auto_website_adding_mode'            => Listing::ADDING_MODE_NONE,
            'auto_website_adding_add_not_visible' => Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_website_adding_template_category_id'                 => null,
            'auto_website_adding_template_category_secondary_id'       => null,
            'auto_website_adding_template_store_category_id'           => null,
            'auto_website_adding_template_store_category_secondary_id' => null,

            'auto_website_deleting_mode' => Listing::DELETING_MODE_NONE
        );

        // mode global
        // ---------------------------------------
        if ($data['auto_mode'] == Listing::AUTO_MODE_GLOBAL) {
            $listingData['auto_mode']               = Listing::AUTO_MODE_GLOBAL;
            $listingData['auto_global_adding_mode'] = $data['auto_global_adding_mode'];

            if ($data['auto_global_adding_mode'] == eBayListing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $listingData['auto_global_adding_template_category_id']                 = $ebayTpl->getId();
                $listingData['auto_global_adding_template_category_secondary_id']       = $ebaySecondaryTpl->getId();
                $listingData['auto_global_adding_template_store_category_id']           = $storeTpl->getId();
                $listingData['auto_global_adding_template_store_category_secondary_id'] = $storeSecondaryTpl->getId();
            }

            if ($data['auto_global_adding_mode'] != Listing::ADDING_MODE_NONE) {
                $listingData['auto_global_adding_add_not_visible'] = $data['auto_global_adding_add_not_visible'];
            }
        }

        // mode website
        // ---------------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE) {
            $listingData['auto_mode']                  = Listing::AUTO_MODE_WEBSITE;
            $listingData['auto_website_adding_mode']   = $data['auto_website_adding_mode'];
            $listingData['auto_website_deleting_mode'] = $data['auto_website_deleting_mode'];

            if ($data['auto_website_adding_mode'] == eBayListing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $listingData['auto_website_adding_template_category_id']                 = $ebayTpl->getId();
                $listingData['auto_website_adding_template_category_secondary_id']       = $ebaySecondaryTpl->getId();
                $listingData['auto_website_adding_template_store_category_id']           = $storeTpl->getId();
                $listingData['auto_website_adding_template_store_category_secondary_id'] = $storeSecondaryTpl->getId();
            }

            if ($data['auto_website_adding_mode'] != Listing::ADDING_MODE_NONE) {
                $listingData['auto_website_adding_add_not_visible'] = $data['auto_website_adding_add_not_visible'];
            }
        }

        // mode category
        // ---------------------------------------
        if ($data['auto_mode'] == Listing::AUTO_MODE_CATEGORY) {
            $listingData['auto_mode'] = Listing::AUTO_MODE_CATEGORY;

            $groupData = array(
                'id'                     => null,
                'category'               => null,
                'title'                  => null,
                'auto_mode'              => Listing::AUTO_MODE_NONE,
                'adding_mode'            => Listing::ADDING_MODE_NONE,
                'adding_add_not_visible' => Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                'deleting_mode'          => Listing::DELETING_MODE_NONE,
            );
            $groupData = array_merge($groupData, $data);

            $ebayGroupData = array(
                'adding_template_category_id'                 => null,
                'adding_template_category_secondary_id'       => null,
                'adding_template_store_category_id'           => null,
                'adding_template_store_category_secondary_id' => null
            );

            if ($data['adding_mode'] == eBayListing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY) {
                $ebayGroupData['adding_template_category_id'] = $ebayTpl->getId();
                $ebayGroupData['adding_template_category_secondary_id'] = $ebaySecondaryTpl->getId();
                $ebayGroupData['adding_template_store_category_id'] = $storeTpl->getId();
                $ebayGroupData['adding_template_store_category_secondary_id'] = $storeSecondaryTpl->getId();
            }

            /** @var Ess_M2ePro_Model_Listing_Auto_Category_Group $group */
            $group = Mage::helper('M2ePro/Component')->getComponentModel(
                $this->getCustomViewNick(), 'Listing_Auto_Category_Group'
            );
            /** @var Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group $ebayGroup */
            $ebayGroup = Mage::getModel('M2ePro/Ebay_Listing_Auto_Category_Group');

            if ((int)$data['id'] > 0) {
                $group->loadInstance((int)$data['id']);
            } else {
                unset($data['id']);
            }

            $group->addData($groupData);
            $group->setData('listing_id', $listing->getId());
            $group->save();

            $ebayGroup->setId($group->getId());
            $ebayGroup->addData($ebayGroupData);
            $ebayGroup->save();

            $group->clearCategories();

            foreach ($data['categories'] as $categoryId) {
                $category = Mage::getModel('M2ePro/Listing_Auto_Category');
                $category->setData('group_id', $group->getId());
                $category->setData('category_id', $categoryId);
                $category->save();
            }
        }

        $listing->addData($listingData);
        $listing->save();
    }

    public function resetAction()
    {
        /** @var Ess_M2ePro_Model_Listing $listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        $data = array(
            'auto_mode'                          => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode'            => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_global_adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_adding_template_category_id'                 => null,
            'auto_global_adding_template_category_secondary_id'       => null,
            'auto_global_adding_template_store_category_id'           => null,
            'auto_global_adding_template_store_category_secondary_id' => null,

            'auto_website_adding_mode'            => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_website_adding_template_category_id'                 => null,
            'auto_website_adding_template_category_secondary_id'       => null,
            'auto_website_adding_template_store_category_id'           => null,
            'auto_website_adding_template_store_category_secondary_id' => null,

            'auto_website_deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE
        );

        $listing->addData($data);
        $listing->save();

        foreach ($listing->getAutoCategoriesGroups(true) as $autoCategoryGroup) {
            /**@var Ess_M2ePro_Model_Listing_Auto_Category_Group $autoCategoryGroup */
            $autoCategoryGroup->deleteInstance();
        }
    }

    //########################################

    public function deleteCategoryAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');
        $categoryId = $this->getRequest()->getParam('category_id');

        /** @var Ess_M2ePro_Model_Listing_Auto_Category $category */
        $category = Mage::getModel('M2ePro/Listing_Auto_Category')->getCollection()
            ->addFieldToFilter('group_id', (int)$groupId)
            ->addFieldToFilter('category_id', (int)$categoryId)
            ->getFirstItem();

        if (!$category->getId()) {
            return;
        }

        $category->deleteInstance();

        if (Mage::getResourceModel('M2ePro/Listing_Auto_Category_Group')->isEmpty($groupId)) {
            Mage::getModel('M2ePro/Listing_Auto_Category_Group')->loadInstance($groupId)->deleteInstance();
        }
    }

    public function deleteCategoryGroupAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        Mage::getModel('M2ePro/Listing_Auto_Category_Group')
            ->loadInstance($groupId)
            ->deleteInstance();
    }

    public function isCategoryGroupTitleUniqueAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $groupId = $this->getRequest()->getParam('group_id');
        $title = $this->getRequest()->getParam('title');

        if ($title == '') {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('unique' => false)));
        }

        $collection = Mage::getModel('M2ePro/Listing_Auto_Category_Group')->getCollection()
            ->addFieldToFilter('listing_id', $listingId)
            ->addFieldToFilter('title', $title);

        if ($groupId) {
            $collection->addFieldToFilter('id', array('neq' => $groupId));
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('unique' => !(bool)$collection->getSize())
            )
        );
    }

    public function getCategoryGroupGridAction()
    {
        $this->loadLayout();

        $grid = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_autoAction_mode_category_group_grid'
        );
        $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################
}
