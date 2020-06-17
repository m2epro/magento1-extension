<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_PickupStore_Variation_Product_ShowController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Pickup Stores'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Attribute.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')
            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Ebay/Listing/Action.js')
            ->addJs('M2ePro/Listing/Moving.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGrid.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManage.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Step/Products/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Step/Stores/Grid.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageVariationsGrid.js');

        $this->_initPopUp();
        $this->setPageHelpLink();

        return $this;
    }

    protected function _setActiveMenu($menuPath)
    {
        return $this;
    }

    protected function addNotificationMessages()
    {
        return null;
    }

    protected function beforeAddContentEvent()
    {
        return null;
    }

    //########################################

    public function variationAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $pickupStoreId = $this->getRequest()->getParam('pickup_store_id', '');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $view = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_variation_product_view');
        $view->setListingProductId($productId);
        $view->setPickupStoreId($pickupStoreId);

        return $this->getResponse()->setBody($view->toHtml());
    }

    public function variationsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $pickupStoreId = $this->getRequest()->getParam('pickup_store_id', '');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_variation_product_view_grid');
        $grid->setListingProductId($productId);
        $grid->setPickupStoreId($pickupStoreId);

        $help = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_variation_product_view_help');

        $this->_initAction();
        $this->_addContent($help);

        $this->_addContent($grid)->renderLayout();
    }

    public function variationsGridAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');
        $pickupStoreId = $this->getRequest()->getParam('pickup_store_id', '');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_pickupStore_variation_product_view_grid');
        $grid->setListingProductId($productId);
        $grid->setPickupStoreId($pickupStoreId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################
}
