<?php

class Ess_M2ePro_Adminhtml_Amazon_ProductTypes_ValidationController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    const CHUNK_SIZE = 2000;
    const BASE_STORAGE_KEY = '/amazon/product_type/validation/modal/listing_product_ids/part';

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Amazon/product_type_validation_grid.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/Action.js')
            ->addJs('M2ePro/Grid.js')

            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Plugin/Storage.js')

            ->addJs('M2ePro/Listing/Action.js')
            ->addJs('M2ePro/Listing/Grid.js')
            ->addJs('M2ePro/Listing/ProductGrid.js')
            ->addJs('M2ePro/Grid.js')
            ->addJs('M2ePro/Amazon/ProductType/Validator/Grid.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "adding-magento-products-manually");

        return $this;
    }

    public function validateAction()
    {
        $listingProductsIdsString = $this->getRequest()->getParam('listing_product_ids');
        $listingProductsIds = explode(',', $listingProductsIdsString);
        $this->validate($listingProductsIds);

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array()
            )
        );
    }

    public function viewProductTypeValidationDataResultAction()
    {
        $productTypeId = $this->getRequest()->getParam('product_type_id');
        $listingProductIdsString = $this->getRequest()->getParam('listing_product_ids');

        if ($listingProductIdsString === null && $productTypeId === null) {
            $listingProductIds = $this->getListingProductIds();
        } else {
            $listingProductIds = array();

            if (!empty($listingProductIdsString)) {
                $listingProductIds = explode(',', $listingProductIdsString);
            }

            $listingProductIds = $this->filterListingProductIds($productTypeId, $listingProductIds);
            $this->setListingProductIds($listingProductIds);
        }


        $this->_initAction();
        $this->setPageHelpLink(null, null,'amazon-product-type');

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_productType_validation_result_grid',
                '',
                array('listingProductIds' => $listingProductIds)
            );

            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_addContent(
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_amazon_productType_validation_result',
                '',
                array('listingProductIds' => $listingProductIds)
            )

        )
            ->renderLayout();
    }

    /**
     * @param $productTypeId
     * @return mixed
     */
    private function filterListingProductIds($productTypeId, $productIds = null)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Product_Collection */
        $collection = Mage::getModel('M2ePro/Listing_Product')->getCollection();
        $amazonResource = Mage::getResourceModel('M2ePro/Amazon_Listing_Product');
        $collection->getSelect()->reset('columns');
        $collection->getSelect()->join(
            array('amazon_listing_product' => $amazonResource->getMainTable()),
            '(`amazon_listing_product`.`listing_product_id` = `main_table`.`id`)',
            array('template_product_type_id')
        );
        $collection->addFieldToSelect('id', 'id');
        $collection->addFieldToFilter(
            'template_product_type_id',
            $productTypeId
        );

        if (!empty($productIds)) {
            $collection->addFieldToFilter(
                'listing_product_id',
                array('in' =>$productIds)
            );
        }

        $collection->addFieldToFilter(
            'status',
            array('eq' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
        );

        return $collection->getConnection()->fetchCol($collection->getSelect());
    }

    private function validate($listingProductsIds)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Listing_Product_Collection */
        $collection = Mage::getModel('M2ePro/Amazon_Listing_Product')->getCollection();
        $collection->addFieldToFilter('listing_product_id', array('in' => $listingProductsIds));
        $collection->addFieldToFilter('template_product_type_id', array('notnull' => true));

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $listingProduct */
        foreach ($collection->getItems() as $listingProduct) {
            $productTypeId = $listingProduct->getTemplateProductTypeId();
            $productTypeAttributesValidator = Mage::getModel('M2ePro/Amazon_ProductType_AttributesValidator');

            /** @var  $productTypeAttributesValidator Ess_M2ePro_Model_Amazon_ProductType_AttributesValidator */
            $productTypeAttributesValidator->validate($listingProduct, $productTypeId);
        }
    }

    private function setListingProductIds(array $listingProductIds)
    {
        $this->reset();

        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $insertData = array();
        foreach (array_chunk($listingProductIds, self::CHUNK_SIZE) as $index => $listingProductIdsChunk) {
            $insertData[] = array(
                'key' => $this->makeKeyForPart(++$index),
                'value' => Mage::helper('M2ePro')->jsonEncode($listingProductIdsChunk),
                'update_date' => $currentDate,
                'create_date' => $currentDate
            );
        }

        $registryResource = Mage::getResourceModel('M2ePro/Registry');
        $connection = Mage::getResourceModel('core/config')->getReadConnection();
        $connection->insertMultiple(
            $registryResource->getMainTable(),
            $insertData
        );
    }

    private function getListingProductIds()
    {
        $registryResource = Mage::getResourceModel('M2ePro/Registry');
        $connection = Mage::getResourceModel('core/config')->getReadConnection();
        $select = $connection
            ->select()
            ->from(
                $registryResource->getMainTable(),
                array()
            )
            ->columns('value')
            ->where($this->getKeyWhereCondition());

        $listingProductIdsParts =  $connection->fetchCol($select);

        if (!empty($listingProductIdsParts)) {
            array_walk($listingProductIdsParts, function (&$item) {
                $item = Mage::helper('M2ePro')->jsonDecode($item);
            });

            $result = array();
            foreach ($listingProductIdsParts as $listingProductIdsPart) {
                $result = array_merge($result, $listingProductIdsPart);
            }

            return $result;
        }

        return array();
    }

    private function reset()
    {
        $registryResource = Mage::getResourceModel('M2ePro/Registry');
        $connection = Mage::getResourceModel('core/config')->getReadConnection();

        $connection->delete(
            $registryResource->getMainTable(),
            $this->getKeyWhereCondition()
        );
    }

    private function makeKeyForPart($partNumber)
    {
        return sprintf('%s/%s/', self::BASE_STORAGE_KEY, $partNumber);
    }

    private function getKeyWhereCondition()
    {
        return sprintf("`key` LIKE '%s%%'", self::BASE_STORAGE_KEY);
    }
}