<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ProductId
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
    //########################################

    public function render(Varien_Object $row)
    {
        $productId = $this->_getValue($row);

        if ($productId === null) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($this->getColumn()->getData('store_id') !== null) {
            $storeId = (int)$this->getColumn()->getData('store_id');
        } elseif ($row->getData('store_id') !== null) {
            $storeId = (int)$row->getData('store_id');
        } else {
            $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        }

        $url = $this->getUrl('adminhtml/catalog_product/edit', array('id' => $productId, 'store' => $storeId));
        $withoutImageHtml = '<a href="'.$url.'" target="_blank">' . $productId . '</a>';

        $showProductsThumbnails = (bool)Mage::helper('M2ePro/Module_Configuration')
            ->getViewShowProductsThumbnailsMode();
        if (!$showProductsThumbnails) {
            return $withoutImageHtml;
        }

        /** @var $magentoProduct Ess_M2ePro_Model_Magento_Product */
        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($productId);
        $magentoProduct->setStoreId($storeId);

        $thumbnail = $magentoProduct->getThumbnailImage();
        if ($thumbnail === null) {
            return $withoutImageHtml;
        }

        return <<<HTML
<a href="{$url}" target="_blank">
    {$productId}
    <hr style="border: 1px solid silver; border-bottom: none;">
    <img style="max-width: 100px; max-height: 100px;" src="{$thumbnail->getUrl()}" /></a>
HTML;
    }

    //########################################
}
