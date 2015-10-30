<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $listing;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAutoActionModeCategoryForm');
        // ---------------------------------------

        $this->setTemplate('M2ePro/listing/auto_action/mode/category/form.phtml');
    }

    //########################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    public function hasFormData()
    {
        return $this->getListing()->getData('auto_mode') == Ess_M2ePro_Model_Listing::AUTO_MODE_CATEGORY;
    }

    public function getFormData()
    {
        $groupId = $this->getRequest()->getParam('group_id');

        if (empty($groupId)) {
            return array();
        }

        $group = Mage::helper('M2ePro/Component')
            ->getUnknownObject('Listing_Auto_Category_Group', $groupId);

        $data = $group->getData();

        return $data;
    }

    public function getDefault()
    {
        return array(
            'id' => NULL,
            'title' => NULL,
            'category_id' => NULL,
            'adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE,
        );
    }

    //########################################

    public function getCategoriesFromOtherGroups()
    {
        $categories = Mage::getResourceModel('M2ePro/Listing_Auto_Category_Group')
            ->getCategoriesFromOtherGroups(
                $this->getRequest()->getParam('listing_id'),
                $this->getRequest()->getParam('group_id')
            );

        foreach ($categories as &$group) {
            $group['title'] = Mage::helper('M2ePro')->escapeHtml($group['title']);
        }

        return $categories;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (is_null($this->listing)) {
            $listingId = $this->getRequest()->getParam('listing_id');
            $this->listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // ---------------------------------------
        $selectedCategories = array();
        if ($this->getRequest()->getParam('group_id')) {
            $selectedCategories = Mage::getModel('M2ePro/Listing_Auto_Category')
                ->getCollection()
                    ->addFieldToFilter('group_id', $this->getRequest()->getParam('group_id'))
                    ->addFieldToFilter('category_id', array('neq' => 0))
                    ->getColumnValues('category_id');
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Listing_Category_Tree $block */
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_listing_category_tree');
        $block->setCallback('ListingAutoActionHandlerObj.magentoCategorySelectCallback');
        $block->setSelectedCategories($selectedCategories);
        $this->setChild('category_tree', $block);
        // ---------------------------------------

        // ---------------------------------------
        $this->setChild('confirm', $this->getLayout()->createBlock('M2ePro/adminhtml_widget_dialog_confirm'));
        // ---------------------------------------
    }

    //########################################
}