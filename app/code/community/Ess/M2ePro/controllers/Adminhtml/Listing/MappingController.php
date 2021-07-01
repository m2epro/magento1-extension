<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Listing_MappingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function mapProductPopupHtmlAction()
    {
        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_mapping_view',
            '',
            array(
                'grid_url'           => '*/adminhtml_listing_mapping/mapProductGrid',
                'mapping_handler_js' => $this->getMovingHandlerJs(),
                'mapping_action'     => 'remap'
            )
        );

        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function mapProductGridAction()
    {
        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_mapping_grid',
            '',
            array(
                'grid_url'           => '*/adminhtml_listing_mapping/mapProductGrid',
                'mapping_handler_js' => $this->getMovingHandlerJs(),
                'mapping_action'     => 'remap'
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    //########################################

    public function remapAction()
    {
        $componentMode = $this->getRequest()->getParam('component_mode');
        $productId = $this->getRequest()->getPost('product_id');
        $listingProductId = $this->getRequest()->getPost('listing_product_id');

        if (!$productId || !$listingProductId || !$componentMode) {
            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => false)));
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);

        if (!$magentoProduct->exists()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__('Product does not exist.')
                    )
                )
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product $listingProductInstance */
        $listingProductInstance = Mage::helper('M2ePro/Component')->getComponentObject(
            $componentMode,
            'Listing_Product',
            $listingProductId
        );

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::getModel('M2ePro/Listing_Product')->getCollection()
            ->addFieldToFilter('listing_id', $listingProductInstance->getListingId())
            ->addFieldToFilter('product_id', $productId);

        if (!$listingProductCollection->getFirstItem()->isEmpty()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__(
                            'Item cannot be linked to Magento Product that already exists in the Listing.'
                        )
                    )
                )
            );
        }

        if ($listingProductInstance->isSetProcessingLock()) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'result'  => false,
                        'message' => Mage::helper('M2ePro')->__(
                            'Another Action is being processed. Please wait until the Action is completed.'
                        )
                    )
                )
            );
        }

        $listingProductInstance->remapProduct($magentoProduct);

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                    'result'  => true,
                    'message' => Mage::helper('M2ePro')->__('Product(s) was Linked.')
                )
            )
        );
    }

    //########################################

    protected function getMovingHandlerJs()
    {
        if ($this->getRequest()->getParam('component_mode') == Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return 'EbayListingSettingsGridObj.mappingHandler';
        }

        return 'ListingGridObj.mappingHandler';
    }

    //########################################
}
