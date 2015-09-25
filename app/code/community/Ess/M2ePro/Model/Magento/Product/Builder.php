<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Product_Builder extends Mage_Core_Model_Abstract
{
    /** @var $product Mage_Catalog_Model_Product */
    private $product = NULL;

    // ########################################

    public function getProduct()
    {
        return $this->product;
    }

    // ########################################

    public function buildProduct()
    {
        $this->createProduct();
    }

    private function createProduct()
    {
        // --------
        $this->product = Mage::getModel('catalog/product');
        $this->product->setTypeId(Ess_M2ePro_Model_Magento_Product::TYPE_SIMPLE);
        $this->product->setAttributeSetId(Mage::getModel('catalog/product')->getDefaultAttributeSetId());
        // --------

        // --------
        $this->product->setName($this->getData('title'));
        $this->product->setDescription($this->getData('description'));
        $this->product->setShortDescription($this->getData('short_description'));
        $this->product->setSku($this->getData('sku'));
        // --------

        // --------
        $this->product->setPrice($this->getData('price'));
        $this->product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        $this->product->setTaxClassId($this->getData('tax_class_id'));
        $this->product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        // --------

        // --------
        $websiteIds = array();
        if (!is_null($this->getData('store_id'))) {
            $store = Mage::app()->getStore($this->getData('store_id'));
            $websiteIds = array($store->getWebsiteId());
        }

        if (empty($websiteIds)) {
            $websiteIds = array(Mage::helper('M2ePro/Magento_Store')->getDefaultWebsiteId());
        }

        $this->product->setWebsiteIds($websiteIds);
        // --------

        // --------
        $gallery = $this->makeGallery();

        if (count($gallery) > 0) {
            $firstImage = reset($gallery);
            $firstImage = $firstImage['file'];

            $this->product->setData('image', $firstImage);
            $this->product->setData('thumbnail', $firstImage);
            $this->product->setData('small_image', $firstImage);

            $this->product->setData('media_gallery', array(
                'images' => json_encode($gallery),
                'values' => json_encode(array(
                    'main'        => $firstImage,
                    'image'       => $firstImage,
                    'small_image' => $firstImage,
                    'thumbnail'   => $firstImage
                ))
            ));
        }
        // --------

        // --------
        $this->product->getResource()->save($this->product);
        // --------

        // --------
        $this->createStockItem();
        // --------
    }

    // ########################################

    private function createStockItem()
    {
        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->assignProduct($this->product);

        $stockItem->addData(array(
            'qty'                         => $this->getData('qty'),
            'stock_id'                    => 1,
            'is_in_stock'                 => 1,
            'use_config_min_qty'          => 1,
            'use_config_min_sale_qty'     => 1,
            'use_config_max_sale_qty'     => 1,
            'is_qty_decimal'              => 0,
            'use_config_backorders'       => 1,
            'use_config_notify_stock_qty' => 1
        ));

        $stockItem->save();
    }

    private function makeGallery()
    {
        if (!is_array($this->getData('images')) || count($this->getData('images')) == 0) {
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

    // ########################################
}