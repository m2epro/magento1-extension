<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Product_Builder extends Mage_Core_Model_Abstract
{
    /** @var $_product Mage_Catalog_Model_Product */
    protected $_product = null;

    //########################################

    public function getProduct()
    {
        return $this->_product;
    }

    //########################################

    public function buildProduct()
    {
        $this->createProduct();
    }

    protected function createProduct()
    {
        $this->_product = Mage::getModel('catalog/product');
        $this->_product->setTypeId(Ess_M2ePro_Model_Magento_Product::TYPE_SIMPLE);
        $this->_product->setAttributeSetId(Mage::getModel('catalog/product')->getDefaultAttributeSetId());

        // ---------------------------------------

        $this->_product->setName($this->getData('title'));
        $this->_product->setDescription($this->getData('description'));
        $this->_product->setShortDescription($this->getData('short_description'));
        $this->_product->setSku($this->getData('sku'));

        // ---------------------------------------

        $this->_product->setPrice($this->getData('price'));
        $this->_product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $this->_product->setTaxClassId($this->getData('tax_class_id'));
        $this->_product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        // ---------------------------------------

        $websiteIds = array();
        if ($this->getData('store_id') !== null) {
            $store = Mage::app()->getStore($this->getData('store_id'));
            $websiteIds = array($store->getWebsiteId());
        }

        if (empty($websiteIds)) {
            $websiteIds = array(Mage::helper('M2ePro/Magento_Store')->getDefaultWebsiteId());
        }

        $this->_product->setWebsiteIds($websiteIds);

        // ---------------------------------------

        $gallery = $this->makeGallery();

        if (!empty($gallery)) {
            $firstImage = reset($gallery);
            $firstImage = $firstImage['file'];

            $this->_product->setData('image', $firstImage);
            $this->_product->setData('thumbnail', $firstImage);
            $this->_product->setData('small_image', $firstImage);

            $this->_product->setData(
                'media_gallery', array(
                'images' => Mage::helper('M2ePro')->jsonEncode($gallery),
                'values' => Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'main'        => $firstImage,
                    'image'       => $firstImage,
                    'small_image' => $firstImage,
                    'thumbnail'   => $firstImage
                    )
                )
                )
            );
        }

        // ---------------------------------------

        $this->_product->getResource()->save($this->_product);

        $this->createStockItem();
    }

    //########################################

    protected function createStockItem()
    {
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->setStockId(
            Mage::helper('M2ePro/Magento_Store')->getStockId($this->getData('store_id'))
        );
        $stockItem->assignProduct($this->_product);

        $stockItem->addData(
            array(
            'qty'                         => $this->getData('qty'),
            'stock_id'                    => 1,
            'is_in_stock'                 => 1,
            'use_config_min_qty'          => 1,
            'use_config_min_sale_qty'     => 1,
            'use_config_max_sale_qty'     => 1,
            'is_qty_decimal'              => 0,
            'use_config_backorders'       => 1,
            'use_config_notify_stock_qty' => 1
            )
        );

        $stockItem->save();
    }

    protected function makeGallery()
    {
        if (!is_array($this->getData('images')) || empty($this->getData('images'))) {
            return array();
        }

        $tempMediaPath = Mage::getSingleton('catalog/product_media_config')->getBaseTmpMediaPath();
        $gallery = array();
        $imagePosition = 1;

        foreach ($this->getData('images') as $tempImageName) {
            if (!is_file($tempMediaPath . DS . $tempImageName)) {
                continue;
            }

            $gallery[] = array(
                'file'     => $tempImageName,
                'label'    => '',
                'position' => $imagePosition++,
                'disabled' => 0,
                'removed'  => 0
            );
        }

        return $gallery;
    }

    //########################################
}
