<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_ProductSearch_Menu
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_listingProductId;

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct;

    //########################################

    /**
     * @param mixed $listingProductId
     * @return $this
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getListingProductId()
    {
        return $this->_listingProductId;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product|null
     */
    public function getListingProduct()
    {
        if ($this->_listingProduct === null) {
            $this->_listingProduct = Mage::helper('M2ePro/Component_Amazon')
                                         ->getObject('Listing_Product', $this->getListingProductId());
        }

        return $this->_listingProduct;
    }

    public function isIndividualFromBundleOrSimpleOrDownloadable()
    {
        if (!$this->getListingProduct()->getChildObject()->getVariationManager()->isIndividualType()) {
            return false;
        }

        return $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
               $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
               $this->getListingProduct()->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks();
    }

    public function isParentFromBundleOrSimpleOrDownloadable()
    {
        if (!$this->getListingProduct()->getChildObject()->getVariationManager()->isRelationParentType()) {
            return false;
        }

        return $this->getListingProduct()->getMagentoProduct()->isBundleType() ||
               $this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions() ||
               $this->getListingProduct()->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks();
    }

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/listing/product_search/menu.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'    => 'productSearchMenu_cancel_button',
            'label' => Mage::helper('M2ePro')->__('Close'),
            'class' => 'productSearchMenu_cancel_button'
        );
        $buttonCancelBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearchMenu_cancel_button', $buttonCancelBlock);
        // ---------------------------------------

        parent::_beforeToHtml();
    }

    //########################################
}