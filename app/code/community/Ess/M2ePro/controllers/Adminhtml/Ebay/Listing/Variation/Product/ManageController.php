<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Listing_Variation_Product_ManageController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    const MANAGE_VARIATION_MODE = 1;
    const MANAGE_VARIATION_THAT_CAN_NOT_BE_DELETED_MODE = 2;

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
            ->addJs('M2ePro/Ebay/Listing/ActionHandler.js')
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
            ->addJs('M2ePro/Ebay/MotorsHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _setActiveMenu($menuPath)
    {
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
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

        $this->_initAction();

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
        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Grid/FrameHandler.js')
             ->addCss('M2ePro/css/Grid/Iframe.css');

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
        $productDetails = $this->getRequest()->getParam('product_details');
        if (empty($productDetails)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $data = array();
        foreach ($productDetails as $key => $value) {
            if (!empty($value)) {
                $data[$key] = $value;
            }
        }

        if ($this->getRequest()->getParam('manage_mode') == self::MANAGE_VARIATION_THAT_CAN_NOT_BE_DELETED_MODE) {

            $listingProductId = $this->getRequest()->getParam('listing_product_id');
            $variationIndex = $this->getRequest()->getParam('variation_id');
            $variationIndex = str_replace(array($listingProductId, '##'), '', $variationIndex);

            if ($variationIndex == '' || empty($listingProductId)) {
                return $this->getResponse()->setBody('You should provide correct parameters.');
            }

            /** @var Ess_M2ePro_Model_Listing_Product $lp */
            $lp = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product', $listingProductId);

            $canNotBeDeleted = $lp->getSetting('additional_data', 'variations_that_can_not_be_deleted', array());
            if (!isset($canNotBeDeleted[$variationIndex])) {
                return $this->getResponse()->setBody(sprintf('Variation index "%s" is not exists', $variationIndex));
            }
            $canNotBeDeleted[$variationIndex]['details'] = $data;

            $lp->setSetting('additional_data', 'variations_that_can_not_be_deleted', $canNotBeDeleted);
            $lp->save();

        } else {

            $variationId = $this->getRequest()->getParam('variation_id');

            if (empty($variationId)) {
                return $this->getResponse()->setBody('You should provide correct parameters.');
            }

            /** @var Ess_M2ePro_Model_Listing_Product_Variation $variation */
            $variation = Mage::helper('M2ePro/Component_Ebay')->getObject('Listing_Product_Variation', $variationId);

            $additionalData = $variation->getAdditionalData();
            $additionalData['product_details'] = $data;

            $variation->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($additionalData));
            $variation->save();
        }

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('success' => true)));
    }

    //########################################

    public function viewDeletedVariationsGridAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_variation_product_manage_view_deletedVariations_grid'
        );
        $grid->setListingProductId($productId);

        $this->_initAction();
        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Grid/FrameHandler.js')
             ->addCss('M2ePro/css/Grid/Iframe.css');

        $this->_addContent($grid)->renderLayout();
    }

    public function viewDeletedVariationsGridAjaxAction()
    {
        $productId = $this->getRequest()->getParam('product_id');

        if (empty($productId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $grid = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_variation_product_manage_view_deletedVariations_grid'
        );
        $grid->setListingProductId($productId);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################
}