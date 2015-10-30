<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Listing_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{
    private $_selectedCategories = array();

    private $_highlightedCategories = array();

    private $_callback = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingCategoryTree');
        // ---------------------------------------

        $this->setTemplate('M2ePro/listing/category/tree.phtml');

        $this->_isAjax = $this->getRequest()->isXmlHttpRequest();
    }

    //########################################

    public function getSelectedCategories()
    {
        return $this->_selectedCategories;
    }

    public function setSelectedCategories($categories)
    {
        $this->_selectedCategories = $categories;
        return $this;
    }

    public function getHighlightedCategories()
    {
        return $this->_highlightedCategories;
    }

    public function setHighlightedCategories($categories)
    {
        $this->_highlightedCategories = $categories;
        return $this;
    }

    public function getCallback()
    {
        return $this->_callback;
    }

    public function setCallback($callback)
    {
        $this->_callback = $callback;
        return $this;
    }

    //########################################

    public function buildNodeName($node)
    {
        return $this->escapeHtml($node->getName());
    }

    public function getTreeJson($parentNodeCategory=null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parentNodeCategory, 0));
        $json = Zend_Json::encode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }

    public function _getNodeJson($node, $level = 0)
    {
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $item = array();
        $item['text'] = $this->buildNodeName($node);
        $item['id']  = $node->getId();
        $item['allowDrop'] = false;

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = false;
        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
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

    //########################################
}