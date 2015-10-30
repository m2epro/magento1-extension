<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Ebay_Template_DescriptionController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //########################################

    public function saveWatermarkImageAction()
    {
        $templateData = $this->getRequest()->getPost('description');

        if (is_null($templateData['id']) || empty($_FILES['watermark_image']['tmp_name'])) {
            return NULL;
        }

        $varDir = new Ess_M2ePro_Model_VariablesDir(
            array('child_folder' => 'ebay/template/description/watermarks')
        );

        $watermarkPath = $varDir->getPath().(int)$templateData['id'].'.png';
        if (is_file($watermarkPath)) {
            @unlink($watermarkPath);
        }

        $template = Mage::getModel('M2ePro/Ebay_Template_Description')->load((int)$templateData['id']);
        $template->updateWatermarkHashes();

        $data = array(
            'watermark_image' => file_get_contents($_FILES['watermark_image']['tmp_name'])
        );

        $template->addData($data);
        $template->save();
    }

    // ---------------------------------------

    public function previewAction()
    {
        if (!(int)$this->getRequest()->getPost('show', 0)) {

            $templateData = $this->getRequest()->getPost('description');
            $this->_getSession()->setTemplateData($templateData);

            $this->printOutput();
            return;
        }

        $productsEntities = $this->getProductsEntities();

        if (!$productsEntities['magento_product']) {

            $errorMessage = Mage::helper('M2ePro')->__('This Product ID does not exist.');
            $this->printOutput(NULL, NULL, $errorMessage);
            return;
        }

        $title = $productsEntities['magento_product']->getProduct()->getData('name');
        $description = $this->getDescription($productsEntities['magento_product'],
                                             $productsEntities['listing_product']);

        $this->printOutput($title, $description);
    }

    //########################################

    private function printOutput($title = NULL, $description = NULL, $errorMessage = NULL)
    {
        $this->loadLayout();

        $previewFormBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_description_preview_form', '',
            array('error_message' => $errorMessage,
                  'product_id'    => $this->getRequest()->getPost('id'),
                  'store_id'      => $this->getRequest()->getPost('store_id'))
        );

        $previewBodyBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_template_description_preview_body', '',
            array('title'       => $title,
                  'description' => $description)
        );

        $html = $this->getLayout()->getBlock('head')->toHtml() .
                $this->getLayout()->createBlock('M2ePro/adminhtml_general')->toHtml() .
                $previewFormBlock->toHtml() .
                $previewBodyBlock->toHtml();

        $this->getResponse()->setBody($html);
    }

    private function getDescription(Ess_M2ePro_Model_Magento_Product $magentoProduct,
                                    Ess_M2ePro_Model_Listing_Product $listingProduct = NULL)
    {
        $descriptionTemplateData = $this->_getSession()->getTemplateData();

        $descriptionModeProduct = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_PRODUCT;
        $descriptionModeShort   = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_SHORT;
        $descriptionModeCustom  = Ess_M2ePro_Model_Ebay_Template_Description::DESCRIPTION_MODE_CUSTOM;

        if ($descriptionTemplateData['description_mode'] == $descriptionModeProduct) {
            $description = $magentoProduct->getProduct()->getDescription();
        } elseif ($descriptionTemplateData['description_mode'] == $descriptionModeShort) {
            $description = $magentoProduct->getProduct()->getShortDescription();
        } elseif ($descriptionTemplateData['description_mode'] == $descriptionModeCustom) {
            $description = $descriptionTemplateData['description_template'];
        } else {
            $description = '';
        }

        if (empty($description)) {
            return $description;
        }

        $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
        $description = $renderer->parseTemplate($description, $magentoProduct);

        if (!is_null($listingProduct)) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Description_Renderer $renderer */
            $renderer = Mage::getSingleton('M2ePro/Ebay_Listing_Product_Description_Renderer');
            $renderer->setListingProduct($listingProduct->getChildObject());
            $description = $renderer->parseTemplate($description);
        }

        $this->addWatermarkInfoToDescriptionIfNeed($description);
        return $description;
    }

    private function addWatermarkInfoToDescriptionIfNeed(&$description)
    {
        $descriptionTemplateData = $this->_getSession()->getTemplateData();
        if (!$descriptionTemplateData['watermark_mode'] || strpos($description, 'm2e_watermark') === false) {
            return;
        }

        preg_match_all('/<img [^>]*\bm2e_watermark[^>]*>/i', $description, $tagsArr);

        $count = count($tagsArr[0]);
        for ($i = 0; $i < $count; $i++) {

            $dom = new DOMDocument();
            $dom->loadHTML($tagsArr[0][$i]);
            $tag = $dom->getElementsByTagName('img')->item(0);

            $newTag = str_replace(' m2e_watermark="1"', '', $tagsArr[0][$i]);
            $newTag = '<div class="description-preview-watermark-info">'.$newTag;

            if ($tag->getAttribute('width') == '' || $tag->getAttribute('width') > 100) {
                $newTag = $newTag.'<p>Watermark will be applied to this picture.</p></div>';
            } else {
                $newTag = $newTag.'<p>Watermark.</p></div>';
            }
            $description = str_replace($tagsArr[0][$i], $newTag, $description);
        }
    }

    // ---------------------------------------

    private function getProductsEntities()
    {
        $productId = $this->getRequest()->getPost('id');
        $storeId   = $this->getRequest()->getPost('store_id', 0);

        if ($productId) {

            return array(
                'magento_product' => $this->getMagentoProductById($productId, $storeId),
                'listing_product' => $this->getListingProductByMagentoProductId($productId, $storeId)
            );
        }

        $listingProduct = $this->getListingProductByRandom($storeId);

        if (!is_null($listingProduct)) {

            return array(
                'magento_product' => $listingProduct->getMagentoProduct(),
                'listing_product' => $listingProduct
            );
        }

        return array(
            'magento_product' => $this->getMagentoProductByRandom($storeId),
            'listing_product' => null
        );
    }

    private function getMagentoProductById($productId, $storeId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);

        if (is_null($product->getId())) {
            return NULL;
        }

        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($product->getId());
        $magentoProduct->setStoreId($storeId);

        return $magentoProduct;
    }

    private function getMagentoProductByRandom($storeId)
    {
        $products = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->setPageSize(100)
                        ->getItems();

        if (count($products) <= 0) {
            return NULL;
        }

        shuffle($products);
        $product = array_shift($products);

        $magentoProduct = Mage::getModel('M2ePro/Magento_Product');
        $magentoProduct->setProductId($product->getId());
        $magentoProduct->setStoreId($storeId);

        return $magentoProduct;
    }

    // ---------------------------------------

    private function getListingProductByMagentoProductId($productId, $storeId)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
              ->getCollection('Listing_Product')
              ->addFieldToFilter('product_id', $productId);

        $listingProductCollection->getSelect()->joinLeft(
            array('ml' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '`ml`.`id` = `main_table`.`listing_id`',
            array('store_id')
        );

        $listingProductCollection->addFieldToFilter('store_id', $storeId);
        $listingProduct = $listingProductCollection->getFirstItem();

        if (is_null($listingProduct->getId())) {
            return NULL;
        }

        return $listingProduct;
    }

    private function getListingProductByRandom($storeId)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
               ->getCollection('Listing_Product');

        $listingProductCollection->getSelect()->joinLeft(
            array('ml' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            '`ml`.`id` = `main_table`.`listing_id`',
            array('store_id')
        );

        $listingProducts = $listingProductCollection
            ->addFieldToFilter('store_id', $storeId)
            ->setPageSize(100)
            ->getItems();

        if (count($listingProducts) <= 0) {
            return NULL;
        }

        shuffle($listingProducts);
        return array_shift($listingProducts);
    }

    //########################################
}