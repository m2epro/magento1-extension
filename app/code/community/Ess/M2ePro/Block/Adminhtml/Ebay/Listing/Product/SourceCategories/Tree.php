<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_SourceCategories_Tree
    extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{
    protected $selectedIds = array();

    /* @var string */
    protected $gridId = NULL;

    /* @var Varien_Data_Tree_Node */
    protected $currentNode = NULL;

    //########################################

    public function setSelectedIds(array $ids)
    {
        $this->selectedIds = $ids;
        return $this;
    }

    public function getSelectedIds()
    {
        return $this->selectedIds;
    }

    public function setCurrentNodeById($categoryId)
    {
        $category = Mage::getModel('catalog/category')->load($categoryId);
        $node = $this->getRoot($category, 1)->getTree()->getNodeById($categoryId);
        return $this->setCurrentNode($node);
    }

    public function setCurrentNode(Varien_Data_Tree_Node $currentNode)
    {
        $this->currentNode = $currentNode;
        return $this;
    }

    public function getCurrentNode()
    {
        return $this->currentNode;
    }

    public function getCurrentNodeId()
    {
        return $this->currentNode ? $this->currentNode->getId() : NULL;
    }

    //########################################

    public function setGridId($gridId)
    {
        $this->gridId = $gridId;
        return $this;
    }

    public function getGridId()
    {
        return $this->gridId;
    }

    //########################################

    public function getLoadTreeUrl()
    {
        return $this->getUrl('*/*/getCategoriesJson', array('_current'=>true));
    }

    //########################################

    public function getCategoryCollection()
    {
        $collection = $this->getData('category_collection');

        if (!$collection) {
            $collection = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('is_active');

            $this->loadProductsCount($collection);

            $this->setData('category_collection', $collection);
        }

        return $collection;
    }

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingCategoryTree');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/product/source_categories/tree.phtml');

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    //########################################

    public function getTreeJson($parentNodeCategory=null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parentNodeCategory));
        $json = Zend_Json::encode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }

    //########################################

    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $item = array();
        $item['text'] = $this->buildNodeName($node);
        $item['id']  = $node->getId();
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $item['path'] = $node->getData('path');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;

        $isParent = $this->_isParentSelectedCategory($node);

        if ((int)$node->getChildrenCount() > 0) {
            $item['children'] = array();
        }

        if ($node->hasChildren()) {

            $item['children'] = array();

            if (!($node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level+1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    protected function _isParentSelectedCategory($node)
    {
        if ($node && $this->getCurrentNode()) {
            $pathIds = explode('/', $this->getCurrentNode()->getData('path'));
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    //########################################

    public function buildNodeName($node)
    {
        $helper = Mage::helper('M2ePro');

        return $this->escapeHtml($node->getName()) . <<<HTML
<span category_id="{$node->getId()}">(0</span>{$helper->__('of')} {$node->getProductCount()})
HTML;
    }

    //########################################

    public function getCategoryChildrenJson($categoryId)
    {
        $this->setCurrentNodeById($categoryId);
        return $this->getTreeJson(Mage::getModel('catalog/category')->load($categoryId));
    }

    //########################################

    public function getAffectedCategoriesCount()
    {
        if (!is_null($this->getData('affected_categories_count'))) {
            return $this->getData('affected_categories_count');
        }

        $dbSelect = Mage::getResourceModel('core/config')->getReadConnection()
                             ->select()
                             ->from(Mage::getSingleton('core/resource')->getTableName('catalog/category_product'),
                                    'category_id')
                             ->where('`product_id` IN(?)',$this->getSelectedIds());

        $affectedCategoriesCount = Mage::getModel('catalog/category')->getCollection()
            ->getSelectCountSql()
            ->where('entity_id IN ('.$dbSelect->__toString().')')
            ->query()
            ->fetchColumn();

        $this->setData('affected_categories_count', (int)$affectedCategoriesCount);

        return $this->getData('affected_categories_count');
    }

    //########################################

    public function getProductsForEachCategory()
    {
        if (!is_null($this->getData('products_for_each_category'))) {
            return $this->getData('products_for_each_category');
        }

        $ids = array_map('intval',$this->selectedIds);
        $ids = implode(',',$ids);
        !$ids && $ids = 0;

        /* @var $select Varien_Db_Select */
        $select = Mage::getModel('catalog/category')->getCollection()->getSelect();
        $select->joinLeft(
            Mage::getSingleton('core/resource')->getTableName('catalog/category_product'),
            "entity_id = category_id AND product_id IN ({$ids})",
            array('product_id')
        );

        $productsForEachCategory = array();
        foreach ($select->query() as $row) {
            if (!isset($productsForEachCategory[$row['entity_id']])) {
                $productsForEachCategory[$row['entity_id']] = array();
            }
            $row['product_id'] && $productsForEachCategory[$row['entity_id']][] = $row['product_id'];
        }

        $this->setData('products_for_each_category', $productsForEachCategory);

        return $this->getData('products_for_each_category');
    }

    public function getProductsCountForEachCategory()
    {
        if (!is_null($this->getData('products_count_for_each_category'))) {
            return $this->getData('products_count_for_each_category');
        }

        $productsCountForEachCategory = $this->getProductsForEachCategory();
        $productsCountForEachCategory = array_map('count',$productsCountForEachCategory);

        $this->setData('products_count_for_each_category', $productsCountForEachCategory);

        return $this->getData('products_count_for_each_category');
    }

    //########################################

    public function getInfoJson()
    {
        return json_encode(array(
            'category_products' => $this->getProductsCountForEachCategory(),
            'total_products_count' => count($this->getSelectedIds()),
            'total_categories_count' => $this->getAffectedCategoriesCount()
        ));
    }

    //########################################

    protected function loadProductsCount($collection)
    {
        $items = $collection->getItems();

        if (!$items) {
            return;
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',$this->getRequest()->getParam('listing_id')
        );

        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');

        // ---------------------------------------
        $excludeProductsSelect = $readConnection->select()->from(
                Mage::getResourceModel('M2ePro/Listing_Product')->getMainTable(),
                new Zend_Db_Expr('DISTINCT `product_id`')
        );

        $excludeProductsSelect->where('`listing_id` = ?',(int)$listing['id']);

        $select = $readConnection->select();
        $select->from(
                array('main_table' => $collection->getTable('catalog/category_product')),
                array('category_id', new Zend_Db_Expr('COUNT(main_table.product_id)'))
            )
            ->where($readConnection->quoteInto('main_table.category_id IN(?)', array_keys($items)))
            ->where('main_table.product_id NOT IN ('.$excludeProductsSelect.')')
            ->group('main_table.category_id');

        $counts = $readConnection->fetchPairs($select);

        foreach ($items as $item) {
            if (isset($counts[$item->getId()])) {
                $item->setProductCount($counts[$item->getId()]);
            } else {
                $item->setProductCount(0);
            }
        }
    }

    //########################################
}