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
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/AutoComplete.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/AttributeHandler.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Step/Products/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/PickupStore/Step/Stores/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageVariationsGridHandler.js');

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
