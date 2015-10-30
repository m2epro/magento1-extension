<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Common_ListingController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css')
            ->addCss('M2ePro/css/Plugin/AutoComplete.css')

            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/GridHandler.js')

            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/AutoComplete.js')
            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/Plugin/ProgressBar.js')

            ->addJs('M2ePro/Listing/EditListingTitle.js')
            ->addJs('M2ePro/Listing/MovingHandler.js')
            ->addJs('M2ePro/Listing/Other/GridHandler.js')

            ->addJs('M2ePro/Common/Listing.js')
            ->addJs('M2ePro/Common/Listing/Other/GridHandler.js')
            ->addJs('M2ePro/Common/Buy/Listing/Other/GridHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/AfnQtyHandler.js')
            ->addJs('M2ePro/Common/Amazon/Listing/Other/GridHandler.js')

            ->addJs('M2ePro/Listing/Other/AutoMappingHandler.js')
            ->addJs('M2ePro/Listing/Other/MappingHandler.js')
            ->addJs('M2ePro/Listing/Other/RemovingHandler.js')
            ->addJs('M2ePro/Listing/Other/UnmappingHandler.js');

        $this->_initPopUp();

        $this->setComponentPageHelpLink('Listings+Overview');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings');
    }

    //########################################

    public function indexAction()
    {
        /*!(bool)Mage::getModel('M2ePro/Template_SellingFormat')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one selling format policy first.')
        );

        !(bool)Mage::getModel('M2ePro/Template_Synchronization')->getCollection()->getSize() &&
        $this->_getSession()->addNotice(
            Mage::helper('M2ePro')->__('You must create at least one synchronization policy first.')
        );*/

        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_manageListings'))
             ->renderLayout();
    }

    //########################################

    public function getListingTabAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        $this->getResponse()->setBody(
            $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing')->toHtml()
        );
    }

    public function getListingOtherTabAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        $this->getResponse()->setBody(
            $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other')->toHtml()
        );
    }

    public function getSearchTabAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing/index');
        }

        $this->getResponse()->setBody(
            $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search')->toHtml()
        );
    }

    //########################################

    public function saveTitleAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');

        if (is_null($listingId)) {
            return;
        }

        $model = Mage::getModel('M2ePro/Listing')->loadInstance((int)$listingId);
        $model->setTitle($title)->save();

        Mage::getModel('M2ePro/Listing_Log')->updateListingTitle($listingId, $title);
    }

    //########################################

    public function searchAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search'))
             ->renderLayout();
    }

    public function searchGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_search_grid');
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function goToSellingFormatTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id)->getChildObject();

        if (!$model->getId()) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            $this->_redirect('*/*/index');
            return;
        }

        $url = Mage::helper('M2ePro/View')->getUrl(
            $model, 'template_sellingFormat', 'edit',
            array(
                'id' => $model->getData('template_selling_format_id'),
                'back' => Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );

        $this->_redirectUrl($url);
    }

    public function goToSynchronizationTemplateAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id)->getChildObject();

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            $this->_redirect('*/*/index');
            return;
        }

        $url = Mage::helper('M2ePro/View')->getUrl(
            $model, 'template_synchronization', 'edit',
            array(
                'id' => $model->getData('template_synchronization_id'),
                'back' => Mage::helper('M2ePro')->getBackUrlParam('list')
            )
        );

        $this->_redirectUrl($url);
    }

    //########################################

    public function confirmTutorialAction()
    {
        $component = $this->getRequest()->getParam('component');

        if (empty($component)) {
            $this->_redirect('*/adminhtml_common_listing/index');
            return;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/view/common/'.$component.'/listing/', 'tutorial_shown', 1
        );

        $this->_redirect(
            '*/adminhtml_common_listing/index',
            array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Common_Component_Abstract::getTabIdByComponent($component)
            )
        );
    }

    //########################################

    public function getVariationEditPopupAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Product must be specified.')
            )));
        }

        $variationEditBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_listing_product_variation_edit','',
            array(
                'component' => $component,
                'listing_product_id' => $listingProductId,
            )
        );

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'text' => $variationEditBlock->toHtml()
        )));
    }

    // ---------------------------------------

    public function getVariationManagePopupAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Product must be specified.')
            )));
        }

        $variationManageBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_common_listing_product_variation_manage','',
            array(
                'component' => $component,
                'listing_product_id' => $listingProductId,
            )
        );

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'text' => $variationManageBlock->toHtml()
        )));
    }

    //########################################

    public function variationEditAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');
        $variationData = $this->getRequest()->getParam('variation_data');

        if (!$listingProductId || !$component || !$variationData) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'Component, Listing Product and Variation Data must be specified.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        $magentoVariations = $listingProduct->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        $magentoVariations = $magentoVariations['variations'];
        foreach ($magentoVariations as $key => $magentoVariation) {
            foreach ($magentoVariation as $option) {
                $value = $option['option'];
                $attribute = $option['attribute'];

                if ($variationData[$attribute] != $value) {
                    unset($magentoVariations[$key]);
                }
            }
        }

        if (count($magentoVariations) != 1) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Only 1 Variation must leave.')
            )));
        }

        if ($listingProduct->isComponentModeAmazon()) {
            $individualModel = $listingProduct->getChildObject()->getVariationManager()->getTypeModel();
        } else {
            $individualModel = $listingProduct->getChildObject()->getVariationManager();
        }
        $individualModel->setProductVariation(reset($magentoVariations));

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('Variation has been successfully edited.')
        )));
    }

    public function variationManageAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');
        $variationsData = $this->getRequest()->getParam('variation_data');

        if (!$listingProductId || !$component || !$variationsData) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'Component, Listing Product and Variation Data must be specified.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();

        if ($listingProduct->isComponentModeAmazon()) {
            $isVariationProductMatched = (
                $variationManager->isIndividualType() &&
                $variationManager->getTypeModel()->isVariationProductMatched()
            );
        } else {
            $isVariationProductMatched = $variationManager->isVariationProductMatched();
        }

        if ($isVariationProductMatched) {
            $listingProduct = $this->duplicateListingProduct($listingProduct);
        } else {

            $listingProduct->setData('search_settings_status', NULL);
            $listingProduct->setData('search_settings_data', NULL);
            $listingProduct->save();

        }

        $magentoVariations = $listingProduct->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        $magentoVariations = $magentoVariations['variations'];

        $isFirst = true;
        foreach ($variationsData as $variationData) {

            !$isFirst && $listingProduct = $this->duplicateListingProduct($listingProduct);
            $isFirst = false;

            $tempMagentoVariations = $magentoVariations;

            foreach ($tempMagentoVariations as $key => $magentoVariation) {
                foreach ($magentoVariation as $option) {
                    $value = $option['option'];
                    $attribute = $option['attribute'];

                    if ($variationData[$attribute] != $value) {
                        unset($tempMagentoVariations[$key]);
                    }
                }
            }

            if (count($tempMagentoVariations) != 1) {
                return $this->getResponse()->setBody(json_encode(array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Only 1 Variation must leave.')
                )));
            }

            if ($listingProduct->isComponentModeAmazon()) {
                /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $listingProductManager */
                $listingProductManager = $listingProduct->getChildObject()->getVariationManager();

                if ($listingProductManager->isRelationParentType() && $listingProductManager->modeCanBeSwitched()) {
                    $listingProductManager->switchModeToAnother();
                }
                $individualModel = $listingProductManager->getTypeModel();
            } else {
                $individualModel = $listingProduct->getChildObject()->getVariationManager();
            }
            $individualModel->setProductVariation(reset($tempMagentoVariations));
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('Variation(s) has been successfully saved.')
        )));
    }

    public function variationResetAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'For changing the Mode of working with Magento Variational Product
                     you have to choose the Specific Product.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        $listingProduct->setData('search_settings_status', NULL);
        $listingProduct->setData('search_settings_data', NULL);
        $listingProduct->save();

        $listingProductManager = $listingProduct->getChildObject()->getVariationManager();
        if ($listingProductManager->isIndividualType() && $listingProductManager->modeCanBeSwitched()) {
            $listingProductManager->switchModeToAnother();
        }

        $listingProductManager->getTypeModel()->getProcessor()->process();

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__(
                'Mode of working with Magento Variational Product has been switched to work with Parent-Child Product.'
            )
        )));

    }

    // ---------------------------------------

    public function variationManageGenerateAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductId = (int)$this->getRequest()->getParam('listing_product_id');

        if (!$listingProductId || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__(
                    'Component and Listing Product must be specified.'
                )
            )));
        }

        /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $component, 'Listing_Product', $listingProductId
        );

        $magentoVariations = $listingProduct->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();
        $magentoVariations = $magentoVariations['variations'];

        if (!$this->getRequest()->getParam('unique',false)) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'success',
                'text' => $magentoVariations
            )));
        }

        $listingProducts = Mage::helper('M2ePro/Component')
            ->getComponentCollection($component,'Listing_Product')
            ->addFieldToFilter('listing_id',$listingProduct->getListingId())
            ->addFieldToFilter('product_id',$listingProduct->getProductId())
            ->getItems();

        foreach ($listingProducts as $listingProduct) {

            $variationManager = $listingProduct->getChildObject()->getVariationManager();

            if ($listingProduct->isComponentModeAmazon()) {
                if (!($variationManager->isIndividualType() &&
                    $variationManager->getTypeModel()->isVariationProductMatched())) {

                    continue;
                }
            } else {
                if (!$variationManager->isVariationProductMatched()) {
                    continue;
                }
            }

            $variations = $listingProduct->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            $options = $variation->getOptions();
            foreach ($options as &$option) {
                $option = array(
                    'product_id' => $option['product_id'],
                    'product_type' => $option['product_type'],
                    'attribute' => $option['attribute'],
                    'option' => $option['option']
                );
            }
            unset($option);

            foreach ($magentoVariations as $key => $variation) {
                if ($variation != $options) {
                    continue;
                }
                unset($magentoVariations[$key]);
            }
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'text' => array_values($magentoVariations)
        )));

    }

    //########################################

    public function duplicateProductsAction()
    {
        $component = $this->getRequest()->getParam('component');
        $listingProductsIds = $this->getRequest()->getParam('ids');
        $listingProductsIds = explode(',',$listingProductsIds);
        $listingProductsIds = array_filter($listingProductsIds);

        if (empty($listingProductsIds) || !$component) {
            return $this->getResponse()->setBody(json_encode(array(
                'type' => 'error',
                'message' => Mage::helper('M2ePro')->__('Component and Listing Products must be specified.')
            )));
        }

        foreach ($listingProductsIds as $listingProductId) {

            /* @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            $listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
                $component, 'Listing_Product', $listingProductId
            );

            $this->duplicateListingProduct($listingProduct);
        }

        return $this->getResponse()->setBody(json_encode(array(
            'type' => 'success',
            'message' => Mage::helper('M2ePro')->__('The Items were successfully duplicated.')
        )));
    }

    //########################################

    private function duplicateListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $duplicatedListingProduct = $listingProduct->getListing()->addProduct(
            $listingProduct->getProductId(),false,false
        );

        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        if (!$variationManager->isVariationProduct()) {
            return $duplicatedListingProduct;
        }

        if ($listingProduct->isComponentModeAmazon()) {
            $duplicatedListingProductManager = $duplicatedListingProduct->getChildObject()->getVariationManager();

            if ($variationManager->isIndividualType() && $duplicatedListingProductManager->modeCanBeSwitched()) {
                $duplicatedListingProductManager->switchModeToAnother();
            }
        }

        return $duplicatedListingProduct;
    }

    //########################################
}