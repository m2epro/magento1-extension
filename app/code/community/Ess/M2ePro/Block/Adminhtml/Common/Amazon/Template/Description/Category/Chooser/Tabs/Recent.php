<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description_Category_Chooser_Tabs_Recent
    extends Mage_Adminhtml_Block_Widget
{
    protected $_selectedCategory = array();

    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateDescriptionCategoryChooserRecent');
        //------------------------------

        // Set template
        //------------------------------
        $this->setTemplate('M2ePro/common/amazon/template/description/category/chooser/tabs/recent.phtml');
        // -----------------------------
    }

    // ########################################

    public function getCategories()
    {
        return Mage::helper('M2ePro/Component_Amazon_Category')->getRecent(
            $this->getRequest()->getPost('marketplace_id'),
            array('product_data_nick' => $this->getRequest()->getPost('product_data_nick'),
                  'browsenode_id'     => $this->getRequest()->getPost('browsenode_id'),
                  'path'              => $this->getRequest()->getPost('category_path'))
        );
    }

    // ########################################
}