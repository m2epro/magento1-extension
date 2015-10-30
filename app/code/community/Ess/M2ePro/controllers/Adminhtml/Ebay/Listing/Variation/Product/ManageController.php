<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_Variation_Product_ManageController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Listings'));

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
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Listing/EditListingTitle.js')
            ->addJs('M2ePro/Ebay/Listing/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/ViewGridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/VariationProductManageVariationsGridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Ebay/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Settings/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Translation/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/PaymentHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/TranslateHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Transferring/InfoHandler.js')
            ->addJs('M2ePro/Ebay/MotorsHandler.js')
        ;

        $this->_initPopUp();

        return $this;
    }

    protected function _setActiveMenu($menuPath)
    {
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/listings');
    }

    // ---------------------------------------

    protected function addNotificationMessages() {}

    protected function beforeAddContentEvent() {}

    // ---------------------------------------

    public function indexAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $view = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_variation_product_manage_view');
        $view->setListingProductId($productId);

        return $this->getResponse()->setBody($view->toHtml());
    }

    //########################################

    public function viewVariationsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_variation_product_manage_view_grid');
        $grid->setListingProductId($productId);

        $help = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_variation_product_manage_view_help');

        $this->_initAction();
        $this->_addContent($help);

        $this->_addContent($grid)->renderLayout();
    }

    public function viewVariationsGridAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_variation_product_manage_view_grid');
        $grid->setListingProductId($productId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################

    public function setIdentifiersAction()
    {
        $variationsId = $this->getRequest()->getParam('variation_id');
        $productDetails   = $this->getRequest()->getParam('product_details');

        if (empty($variationsId) || empty($productDetails)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $variation */
        $variation = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product_Variation', $variationsId);

        $data = array();
        foreach ($productDetails as $key => $value) {
            if (!empty($value)) {
                $data[$key] = $value;
            }
        }

        $additionalData = $variation->getAdditionalData();
        $additionalData['product_details'] = $data;
        $variation->setData('additional_data', json_encode($additionalData))->save();

        $this->getResponse()->setBody(json_encode(array('success' => true)));
    }
}
