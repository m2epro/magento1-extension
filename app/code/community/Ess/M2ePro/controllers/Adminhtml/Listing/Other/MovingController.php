<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// move from 3rd party to listing

class Ess_M2ePro_Adminhtml_Listing_Other_MovingController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //########################################

    public function moveToListingGridAction()
    {
        Mage::helper('M2ePro/Data_Global')->setValue(
            'componentMode', $this->getRequest()->getParam('componentMode')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'accountId', $this->getRequest()->getParam('accountId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'marketplaceId', $this->getRequest()->getParam('marketplaceId')
        );
        Mage::helper('M2ePro/Data_Global')->setValue(
            'ignoreListings', Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('ignoreListings'))
        );

        $component = ucfirst(strtolower($this->getRequest()->getParam('componentMode')));
        $movingHandlerJs = $component.'ListingOtherGridHandlerObj.movingHandler';

        $block = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_moving_grid','',
            array(
                'grid_url' => $this->getUrl(
                    '*/adminhtml_listing_other_moving/moveToListingGrid',array('_current'=>true)
                ),
                'moving_handler_js' => $movingHandlerJs,
            )
        );
        $this->getResponse()->setBody($block->toHtml());
    }

    public function prepareMoveToListingAction()
    {
        $sessionHelper = Mage::helper('M2ePro/Data_Session');
        $componentMode = $this->getRequest()->getParam('componentMode');
        $sessionKey = $componentMode . '_' . Ess_M2ePro_Helper_View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;

        if ((bool)$this->getRequest()->getParam('is_first_part')) {
            $sessionHelper->removeValue($sessionKey);
        }

        $selectedProducts = array();
        if ($sessionValue = $sessionHelper->getValue($sessionKey)) {
            $selectedProducts = $sessionValue;
        }

        $selectedProductsPart = $this->getRequest()->getParam('products_part');
        $selectedProductsPart = explode(',', $selectedProductsPart);

        $selectedProducts = array_merge($selectedProducts, $selectedProductsPart);
        $sessionHelper->setValue($sessionKey, $selectedProducts);

        if (!(bool)$this->getRequest()->getParam('is_last_part')) {

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
                'result' => true
            )));
        }

        $listingOtherCollection = Mage::helper('M2ePro/Component')
                                  ->getComponentModel($componentMode, 'Listing_Other')
                                  ->getCollection();

        $listingOtherCollection->addFieldToFilter('main_table.id', array('in' => $selectedProducts));
        $listingOtherCollection->addFieldToFilter('main_table.product_id', array('notnull' => true));

        if ($listingOtherCollection->getSize() != count($selectedProducts)) {

            $sessionHelper->removeValue($sessionKey);
            $message = Mage::helper('M2ePro')->__('Only Mapped Products must be selected.');

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
                'result'  => false,
                'message' => $message
            )));
        }

        $listingOtherCollection->getSelect()->join(
            array(
                'cpe' => Mage::helper('M2ePro/Module_Database_Structure')
                       ->getTableNameWithPrefix('catalog_product_entity')
            ),
            '`main_table`.`product_id` = `cpe`.`entity_id`'
        );

        $row = $listingOtherCollection->getSelect()
           ->group(array('main_table.account_id', 'main_table.marketplace_id'))
           ->reset(Zend_Db_Select::COLUMNS)
           ->columns(array('marketplace_id', 'account_id'))
           ->query()
           ->fetch();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array(
            'result'        => true,
            'accountId'     => (int)$row['account_id'],
            'marketplaceId' => (int)$row['marketplace_id'],
        )));
    }

    //########################################
}