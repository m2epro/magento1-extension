<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Category_Categories_Chooser_Tabs_Recent
    extends Mage_Adminhtml_Block_Widget
{
    protected $_selectedCategory = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartTemplateCategoryCategoriesChooserRecent');
        // ---------------------------------------

        // Set template
        // ---------------------------------------
        $this->setTemplate('M2ePro/walmart/template/category/categories/chooser/tabs/recent.phtml');
        // ---------------------------------------
    }

    //########################################

    public function shouldBeShown()
    {
        $categoriesData = $this->getCategories();
        return !empty($categoriesData);
    }

    public function getCategories()
    {
        return Mage::helper('M2ePro/Component_Walmart_Category')->getRecent(
            $this->getRequest()->getPost('marketplace_id'),
            array('product_data_nick' => $this->getRequest()->getPost('product_data_nick'),
                  'browsenode_id'     => $this->getRequest()->getPost('browsenode_id'),
                  'path'              => $this->getRequest()->getPost('category_path'))
        );
    }

    //########################################
}
