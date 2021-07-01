<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Magento_Product_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    protected $_listingProductMode = false;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    /** @var string */
    protected $_componentMode;

    protected $_isNeedToInjectPrices     = false;

    //########################################

    public function setListingProductModeOn()
    {
        $this->_listingProductMode = true;
        $this->_setIdFieldName('id');

        return $this;
    }

    public function setListing($value)
    {
        if (!($value instanceof Ess_M2ePro_Model_Listing)) {
            $value = Mage::helper('M2ePro/Component')->getUnknownObject('Listing', $value);
        }

        $this->_listing = $value;
        return $this;
    }

    public function setComponentMode($componentMode)
    {
        $components = Mage::helper('M2ePro/Component')->getComponents();

        if (!in_array($componentMode, $components)) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                "Wrong component provided [$componentMode]"
            );
        }

        $this->_componentMode = $componentMode;
        return $this;
    }

    public function setIsNeedToInjectPrices($value)
    {
        $this->_isNeedToInjectPrices = $value;
        return $this;
    }

    //########################################

    public function getAllIds($limit = null, $offset = null)
    {
        if (!$this->_listingProductMode) {
            return parent::getAllIds($limit, $offset);
        }

        // hack for selecting listing product ids instead entity ids
        $idsSelect = $this->_getClearSelect();
        $idsSelect->columns('lp.' . $this->getIdFieldName());
        $idsSelect->limit($limit, $offset);

        $data = $this->getConnection()->fetchAll($idsSelect, $this->_bindParams);

        $ids = array();
        foreach ($data as $row) {
            $ids[] = $row[$this->getIdFieldName()];
        }

        return $ids;
    }

    //########################################

    /**
     * @return int
     * @throws Zend_Db_Select_Exception
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $this->_renderFilters();

            $countSelect = $this->_getClearSelect();

            $tableAlias = 'lp';
            $idField = 'id';

            if (!$this->_listingProductMode) {
                $tableAlias = 'e';
                $idField = 'entity_id';
                $countSelect->reset(Zend_Db_Select::GROUP);
            }

            $countSelect->columns("{$tableAlias}.{$idField}");

            $query = <<<SQL
SELECT COUNT(DISTINCT temp_table.{$idField}) FROM ({$countSelect->__toString()}) temp_table
SQL;

            $this->_totalRecords = $this->getConnection()->fetchOne($query, $this->_bindParams);
        }

        return (int)$this->_totalRecords;
    }

    /**
     * @return Varien_Db_Select
     * @throws Zend_Db_Select_Exception
     */
    protected function _getClearSelect()
    {
        $havingColumns = $this->getHavingColumns();
        $parentSelect  = parent::_getClearSelect();

        if (empty($havingColumns)) {
            return $parentSelect;
        }

        foreach ($this->getSelect()->getPart('columns') as $columnData) {
            if (in_array($columnData[2], $havingColumns, true)) {
                $parentSelect->columns(array($columnData[2] => $columnData[1]), $columnData[0]);
            }
        }

        return $parentSelect;
    }

    /**
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    protected function getHavingColumns()
    {
        $having = $this->getSelect()->getPart('having');

        if (empty($having)) {
            return array();
        }

        $columnsInHaving = array();

        foreach ($having as $havingPart) {
            preg_match_all(
                '/((`{0,1})\w+(`{0,1}))' .
                '( = | > | < | >= | <= | <> | <=> | != | LIKE | NOT | BETWEEN | IS NULL| IS NOT NULL| IN\(.*?\))/i',
                $havingPart,
                $matches
            );

            foreach ($matches[1] as $match) {
                $columnsInHaving[] = trim($match);
            }
        }

        return array_unique($columnsInHaving);
    }

    //########################################

    // Price Sorting Hack
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            foreach ($this->_orders as $attribute => $direction) {
                if ($attribute == 'min_online_price' || $attribute == 'max_online_price') {
                    $this->getSelect()->order($attribute . ' ' . $direction);
                } else {
                    $this->addAttributeToSort($attribute, $direction);
                }
            }

            $this->_isOrdersRendered = true;
        }

        return $this;
    }

    //########################################

    public function joinIndexerParent()
    {
        if (!in_array(
            $this->_listing->getComponentMode(), array(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Walmart::NICK
            )
        )) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                "This component is not supported [{$this->_listing->getComponentMode()}]"
            );
        }

        /** @var Ess_M2ePro_Model_Listing_Product_Indexer_VariationParent_Manager $manager */
        $manager = Mage::getModel('M2ePro/Listing_Product_Indexer_VariationParent_Manager', array($this->_listing));
        $manager->prepare();

        if ($this->_listing->isComponentModeAmazon()) {
            $this->joinAmazonIndexerParent();
        } elseif ($this->_listing->isComponentModeEbay()) {
            $this->joinEbayIndexerParent();
        } elseif ($this->_listing->isComponentModeWalmart()) {
            $this->joinWalmartIndexerParent();
        }

        return;
    }

    protected function joinAmazonIndexerParent()
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Indexer_VariationParent $resource */
        $resource = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Indexer_VariationParent');

        $this->getSelect()->joinLeft(
            array('indexer' => $resource->getMainTable()),
            '(`alp`.`listing_product_id` = `indexer`.`listing_product_id`)',
            $this->getAmazonIndexerParentColumns()
        );
    }

    protected function joinWalmartIndexerParent()
    {
        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Indexer_VariationParent $resource */
        $resource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Indexer_VariationParent');

        $this->getSelect()->joinLeft(
            array('indexer' => $resource->getMainTable()),
            '(`wlp`.`listing_product_id` = `indexer`.`listing_product_id`)',
            $this->getWalmartIndexerParentColumns()
        );
    }

    protected function joinEbayIndexerParent()
    {
        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_Indexer_VariationParent $resource */
        $resource = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Indexer_VariationParent');

        $this->getSelect()->joinLeft(
            array('indexer' => $resource->getMainTable()),
            '(`elp`.`listing_product_id` = `indexer`.`listing_product_id`)',
            $this->getEbayIndexerParentColumns()
        );
    }

    //----------------------------------------

    protected function getIndexerParentColumns()
    {
        if (!in_array(
            $this->_listing->getComponentMode(), array(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Walmart::NICK
            )
        )) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                "This component is not supported [{$this->_listing->getComponentMode()}]"
            );
        }

        if ($this->_listing->isComponentModeAmazon()) {
            return $this->getAmazonIndexerParentColumns();
        } elseif ($this->_listing->isComponentModeEbay()) {
            return $this->getEbayIndexerParentColumns();
        } elseif ($this->_listing->isComponentModeWalmart()) {
            return $this->getWalmartIndexerParentColumns();
        }
    }

    protected function getAmazonIndexerParentColumns()
    {
        return array(

            'min_online_regular_price' => new Zend_Db_Expr(
                'IF(
                (`indexer`.`min_regular_price` IS NULL),
                IF(
                  `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                  `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_regular_sale_price`,
                  `alp`.`online_regular_price`
                ),
                `indexer`.`min_regular_price`
            )'
            ),

            'max_online_regular_price' => new Zend_Db_Expr(
                'IF(
                (`indexer`.`max_regular_price` IS NULL),
                IF(
                  `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                  `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_regular_sale_price`,
                  `alp`.`online_regular_price`
                ),
                `indexer`.`max_regular_price`
            )'
            ),

            'min_online_business_price' => new Zend_Db_Expr(
                'IF(
                (`indexer`.`min_business_price` IS NULL),
                `alp`.`online_business_price`,
                `indexer`.`min_business_price`
            )'
            ),

            'max_online_business_price' => new Zend_Db_Expr(
                'IF(
                (`indexer`.`max_business_price` IS NULL),
                `alp`.`online_business_price`,
                `indexer`.`max_business_price`
            )'
            ),

            'min_online_price' => new Zend_Db_Expr(
                'IF(
                `indexer`.`min_regular_price` IS NULL AND `indexer`.`min_business_price` IS NULL,
                IF(
                   `alp`.`online_regular_price` IS NULL,
                   `alp`.`online_business_price`,
                   IF(
                      `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                      `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                      `alp`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                      `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                      `alp`.`online_regular_sale_price`,
                      `alp`.`online_regular_price`
                   )
                ),
                IF(
                    `indexer`.`min_regular_price` IS NULL,
                    `indexer`.`min_business_price`,
                    `indexer`.`min_regular_price`
                )
            )'
            ),

            'max_online_price' => new Zend_Db_Expr(
                'IF(
                `indexer`.`max_regular_price` IS NULL AND `indexer`.`max_business_price` IS NULL,
                IF(
                   `alp`.`online_regular_price` IS NULL,
                   `alp`.`online_business_price`,
                   IF(
                      `alp`.`online_regular_sale_price_start_date` IS NOT NULL AND
                      `alp`.`online_regular_sale_price_end_date` IS NOT NULL AND
                      `alp`.`online_regular_sale_price_start_date` <= CURRENT_DATE() AND
                      `alp`.`online_regular_sale_price_end_date` >= CURRENT_DATE(),
                      `alp`.`online_regular_sale_price`,
                      `alp`.`online_regular_price`
                   )
                ),
                IF(
                    `indexer`.`max_regular_price` IS NULL,
                    `indexer`.`max_business_price`,
                    `indexer`.`max_regular_price`
                )
            )'
            )
        );
    }

    protected function getWalmartIndexerParentColumns()
    {
        return array(
            'min_online_price' => new Zend_Db_Expr(
                'IF(
                `indexer`.`min_price` IS NULL,
                `wlp`.`online_price`,
                `indexer`.`min_price`
            )'
            ),

            'max_online_price' => new Zend_Db_Expr(
                'IF(
                `indexer`.`max_price` IS NULL,
                `wlp`.`online_price`,
                `indexer`.`max_price`
            )'
            ),
        );
    }

    protected function getEbayIndexerParentColumns()
    {
        return array(
            'min_online_price' => new Zend_Db_Expr(
                'IF(
                `indexer`.`min_price` IS NULL,
                `elp`.`online_current_price`,
                `indexer`.`min_price`
            )'
            ),

            'max_online_price' => new Zend_Db_Expr(
                'IF(
                `indexer`.`max_price` IS NULL,
                `elp`.`online_current_price`,
                `indexer`.`max_price`
            )'
            ),
        );
    }

    //----------------------------------------

    protected function _afterLoad()
    {
        $result = parent::_afterLoad();

        if ($this->_isNeedToInjectPrices) {
            $this->injectParentPrices();
        }

        return $result;
    }

    protected function injectParentPrices()
    {
        if (!in_array(
            $this->_listing->getComponentMode(), array(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Walmart::NICK,
            )
        )) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                "This component is not supported [{$this->_listing->getComponentMode()}]"
            );
        }

        if ($this->_listing->isComponentModeAmazon()) {
            $this->injectAmazonParentPrices();
        } else if ($this->_listing->isComponentModeEbay()) {
            $this->injectEbayParentPrices();
        } else if ($this->_listing->isComponentModeWalmart()) {
            $this->injectWalmartParentPrices();
        }

        return;
    }

    protected function injectAmazonParentPrices()
    {
        $listingProductsData = array();
        foreach ($this as $product) {
            $listingProductsData[(int)$product->getData('id')] = array(
                'min_online_regular_price'  => $product->getData('online_regular_price'),
                'max_online_regular_price'  => $product->getData('online_regular_price'),
                'min_online_business_price' => $product->getData('online_business_price'),
                'max_online_business_price' => $product->getData('online_business_price'),
            );
        }

        if (empty($listingProductsData)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Indexer_VariationParent $resource */
        $resource = Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Indexer_VariationParent');

        $selectStmt = $resource->getBuildIndexSelect($this->_listing);
        $selectStmt->where('malp.variation_parent_id IN (?)', array_keys($listingProductsData));

        $data = $this->getConnection()->fetchAll($selectStmt);
        foreach ($data as $row) {
            $listingProductsData[(int)$row['variation_parent_id']] = array(
                'min_online_regular_price'  => $row['variation_min_regular_price'],
                'max_online_regular_price'  => $row['variation_max_regular_price'],
                'min_online_business_price' => $row['variation_min_business_price'],
                'max_online_business_price' => $row['variation_max_business_price'],
            );
        }

        foreach ($this as $product) {
            if (isset($listingProductsData[(int)$product->getData('id')])) {
                $dataPart = $listingProductsData[(int)$product->getData('id')];
                $product->setData('min_online_regular_price', $dataPart['min_online_regular_price']);
                $product->setData('max_online_regular_price', $dataPart['max_online_regular_price']);
                $product->setData('min_online_business_price', $dataPart['min_online_business_price']);
                $product->setData('max_online_business_price', $dataPart['max_online_business_price']);
            }
        }
    }

    protected function injectEbayParentPrices()
    {
        $listingProductsData = array();
        foreach ($this as $product) {
            $listingProductsData[(int)$product->getData('id')] = array(
                'min_online_price' => $product->getData('online_current_price'),
                'max_online_price' => $product->getData('online_current_price'),
            );
        }

        if (empty($listingProductsData)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Ebay_Listing_Product_Indexer_VariationParent $resource */
        $resource = Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Indexer_VariationParent');

        $selectStmt = $resource->getBuildIndexSelect($this->_listing);
        $selectStmt->where('mlpv.listing_product_id IN (?)', array_keys($listingProductsData));

        $data = $this->getConnection()->fetchAll($selectStmt);
        foreach ($data as $row) {
            $listingProductsData[(int)$row['listing_product_id']] = array(
                'min_online_price' => $row['variation_min_price'],
                'max_online_price' => $row['variation_max_price'],
            );
        }

        foreach ($this as $product) {
            if (isset($listingProductsData[(int)$product->getData('id')])) {
                $dataPart = $listingProductsData[(int)$product->getData('id')];
                $product->setData('min_online_price', $dataPart['min_online_price']);
                $product->setData('max_online_price', $dataPart['max_online_price']);
            }
        }
    }

    protected function injectWalmartParentPrices()
    {
        $listingProductsData = array();
        foreach ($this as $product) {
            $listingProductsData[(int)$product->getData('id')] = array(
                'min_online_price' => $product->getData('online_price'),
                'max_online_price' => $product->getData('online_price'),
            );
        }

        if (empty($listingProductsData)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product_Indexer_VariationParent $resource */
        $resource = Mage::getResourceModel('M2ePro/Walmart_Listing_Product_Indexer_VariationParent');

        $selectStmt = $resource->getBuildIndexSelect($this->_listing);
        $selectStmt->where('malp.variation_parent_id IN (?)', array_keys($listingProductsData));

        $data = $this->getConnection()->fetchAll($selectStmt);
        foreach ($data as $row) {
            $listingProductsData[(int)$row['variation_parent_id']] = array(
                'min_online_price' => $row['variation_min_price'],
                'max_online_price' => $row['variation_max_price'],
            );
        }

        foreach ($this as $product) {
            if (isset($listingProductsData[(int)$product->getData('id')])) {
                $dataPart = $listingProductsData[(int)$product->getData('id')];
                $product->setData('min_online_price', $dataPart['min_online_price']);
                $product->setData('max_online_price', $dataPart['max_online_price']);
            }
        }
    }

    //########################################

    public function joinStockItem($columnsMap = array('qty' => 'qty'))
    {
        if ($this->_storeId === null) {
            throw new Ess_M2ePro_Model_Exception('Store view was not set.');
        }

        $stockId = Mage::helper('M2ePro/Magento_Store')->getStockId($this->getStoreId());

        $this->joinTable(
            array('cisi' => 'cataloginventory/stock_item'),
            'product_id=entity_id',
            $columnsMap,
            "{{table}}.stock_id={$stockId}",
            'left'
        );

        return $this;
    }

    //########################################
}
