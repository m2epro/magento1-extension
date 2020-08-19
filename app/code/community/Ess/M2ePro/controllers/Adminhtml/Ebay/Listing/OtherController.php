<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay as ComponentEbay;
use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;

class Ess_M2ePro_Adminhtml_Ebay_Listing_OtherController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')

             ->addJs('M2ePro/Grid.js')
             ->addJs('M2ePro/Listing/Other/Grid.js')
             ->addJs('M2ePro/Ebay/Listing/Other/Grid.js')

             ->addJs('M2ePro/Action.js')
             ->addJs('M2ePro/Listing/Moving.js')
             ->addJs('M2ePro/Listing/Other/Mapping.js')
             ->addJs('M2ePro/Listing/Other/AutoMapping.js')

            ->addJs('M2ePro/Listing/Other/Removing.js')
            ->addJs('M2ePro/Listing/Other/Unmapping.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/HQAJAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function viewAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_view'))
             ->renderLayout();
    }

    //########################################

    public function viewGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_view_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function removingAction()
    {
        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;
        $productIds = $this->getRequest()->getParam('product_ids');

        if (!$productIds) {
            return $this->getResponse()->setBody('0');
        }

        $productArray = explode(',', $productIds);

        if (empty($productArray)) {
            return $this->getResponse()->setBody('0');
        }

        foreach ($productArray as $productId) {
            /** @var $listingOther Ess_M2ePro_Model_Listing_Other */
            $listingOther = Mage::helper('M2ePro/Component')->getComponentObject(
                $component, 'Listing_Other', $productId
            );

            if ($listingOther->getProductId() !== null) {
                $listingOther->unmapProduct();
            }

            $listingOther->deleteInstance();
        }

        return $this->getResponse()->setBody('1');
    }

    //########################################

    public function moveToListingAction()
    {
        $sessionHelper = Mage::helper('M2ePro/Data_Session');
        $sessionKey = ComponentEbay::NICK . '_' . Ess_M2ePro_Helper_View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $selectedProducts = $sessionHelper->getValue($sessionKey);

        /** @var Ess_M2ePro_Model_Listing $listingInstance */
        $listingInstance = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Listing', (int)$this->getRequest()->getParam('listingId'));

        $errorsCount = 0;
        $tempProducts = array();
        foreach ($selectedProducts as $otherListingProduct) {

            /** @var Ess_M2ePro_Model_Listing_Other $listingOther */
            $listingOther = Mage::helper('M2ePro/Component_Ebay')->getObject(
                'Listing_Other', $otherListingProduct
            );

            $listingProduct = $listingInstance->getChildObject()->addProductFromOther(
                $listingOther,
                Ess_M2ePro_Helper_Data::INITIATOR_USER
            );

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                $errorsCount++;
                continue;
            }

            $tempProducts[] = $listingProduct->getId();
        }

        $addingProducts = array_unique(
            array_merge(
                $tempProducts,
                $listingInstance->getChildObject()->getAddedListingProductsIds()
            )
        );

        $listingInstance->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($addingProducts));
        $listingInstance->setSetting('additional_data', 'source', SourceModeBlock::SOURCE_OTHER);
        $listingInstance->save();

        $sessionHelper->removeValue($sessionKey);

        if ($errorsCount) {
            if (count($selectedProducts) == $errorsCount) {
                $this->getSession()->addError(
                    Mage::helper('M2ePro')->__(
                        'Products were not moved because they already exist in the selected Listing.'
                    )
                );

                return $this->getResponse()->setBody(
                    Mage::helper('M2ePro')->jsonEncode(array('result' => false))
                );
            }

            $this->getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Some products were not moved because they already exist in the selected Listing.'
                )
            );
        } else {
            $this->getSession()->addSuccess(Mage::helper('M2ePro')->__('Product(s) was successfully Moved.'));
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(array('result' => true))
        );
    }

    //########################################

    public function resetAction()
    {
        Mage::getResourceModel('M2ePro/Ebay_Listing_Other')->resetEntities();

        $this->getSession()->addSuccess(
            Mage::helper('M2ePro')->__('eBay 3rd Party Listings were reset.')
        );

        $this->_redirect(
            '*/adminhtml_ebay_listing/index', array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Ebay_ManageListings::TAB_ID_LISTING_OTHER
            )
        );
    }

    //########################################
}
