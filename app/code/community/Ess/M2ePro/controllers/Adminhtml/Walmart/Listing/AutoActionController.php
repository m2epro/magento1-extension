<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_Listing_AutoActionController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    /** @var Ess_M2ePro_Model_Listing $_listing */
    protected $_listing;

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        $listing = $this->getListing();
        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);

        $autoMode  = $this->getRequest()->getParam('auto_mode');
        empty($autoMode) && $autoMode = $listing->getAutoMode();

        $this->loadLayout();

        switch ($autoMode) {
            case Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_autoAction_mode_global');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_autoAction_mode_website');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_autoAction_mode_category');
                break;
            case Ess_M2ePro_Model_Listing::AUTO_MODE_NONE:
            default:
                $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_autoAction_mode');
                break;
        }

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function getAutoCategoryFormHtmlAction()
    {
        $listing = $this->getListing();
        Mage::helper('M2ePro/Data_Global')->setValue('listing', $listing);

        $this->loadLayout();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_autoAction_mode_category_form');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function saveAction()
    {
        if (!$post = $this->getRequest()->getPost()) {
            return;
        }

        if (!isset($post['auto_action_data'])) {
            return;
        }

        $listing = $this->getListing();
        $data = Mage::helper('M2ePro')->jsonDecode($post['auto_action_data']);

        $listingData = array(
            'auto_mode'                                => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode'                  => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_global_adding_add_not_visible'       => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_product_type_id'              => null,
            'auto_website_adding_mode'                 => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_add_not_visible'      => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_website_deleting_mode'               => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE,
            'auto_website_product_type_id'             => null
        );

        $groupData = array(
            'id'                     => null,
            'category'               => null,
            'title'                  => null,
            'auto_mode'              => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'adding_mode'            => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'deleting_mode'          => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE,
            'categories'             => array()
        );

        // mode global
        // ---------------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_GLOBAL;
            $listingData['auto_global_adding_mode'] = $data['auto_global_adding_mode'];
            $listingData['auto_global_product_type_id'] = $data['product_type_id'];

            if ($listingData['auto_global_adding_mode'] != Ess_M2ePro_Model_Listing::ADDING_MODE_NONE) {
                $listingData['auto_global_adding_add_not_visible'] = $data['auto_global_adding_add_not_visible'];
            }
        }

        // mode website
        // ---------------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_WEBSITE;
            $listingData['auto_website_adding_mode'] = $data['auto_website_adding_mode'];
            $listingData['auto_website_deleting_mode'] = $data['auto_website_deleting_mode'];
            $listingData['auto_website_product_type_id'] = $data['product_type_id'];

            if ($listingData['auto_website_adding_mode'] != Ess_M2ePro_Model_Listing::ADDING_MODE_NONE) {
                $listingData['auto_website_adding_add_not_visible'] = $data['auto_website_adding_add_not_visible'];
            }
        }

        // mode category
        // ---------------------------------------
        if ($data['auto_mode'] == Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY) {
            $listingData['auto_mode'] = Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY;

            $group = Mage::helper('M2ePro/Component')->getComponentModel(
                $listing->getComponentMode(),
                'Listing_Auto_Category_Group'
            );

            if ((int)$data['id'] > 0) {
                $group->loadInstance((int)$data['id']);
            } else {
                unset($data['id']);
            }

            $group->addData(array_merge($groupData, $data));
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

        $listing->addData($listingData)->save();
    }

    //########################################

    public function resetAction()
    {
        $listing = $this->getListing();

        $data = array(
            'auto_mode'                                => Ess_M2ePro_Model_Listing::AUTO_MODE_NONE,
            'auto_global_adding_mode'                  => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_global_adding_add_not_visible'       => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_global_product_type_id'              => null,
            'auto_website_adding_mode'                 => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'auto_website_adding_add_not_visible'      => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'auto_website_deleting_mode'               => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE,
            'auto_website_product_type_id'             => null
        );

        $listing->addData($data)->save();

        foreach ($listing->getAutoCategoriesGroups(true) as $autoCategoryGroup) {
            $autoCategoryGroup->deleteInstance();
        }
    }

    //########################################

    public function deleteCategoryAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');
        $categoryId = $this->getRequest()->getParam('category_id');

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

    //########################################

    public function deleteCategoryGroupAction()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        Mage::getModel('M2ePro/Listing_Auto_Category_Group')
            ->loadInstance($groupId)
            ->deleteInstance();
    }

    //########################################

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

    //########################################

    public function getCategoryGroupGridAction()
    {
        $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_walmart_listing_autoAction_mode_category_group_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Listing', $this->getRequest()->getParam('listing_id')
            );
        }

        return $this->_listing;
    }

    //########################################
}
