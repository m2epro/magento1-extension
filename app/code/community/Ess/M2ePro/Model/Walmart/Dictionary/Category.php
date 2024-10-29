<?php

use Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category as ResourceModel;

class Ess_M2ePro_Model_Walmart_Dictionary_Category extends Ess_M2ePro_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Dictionary_Category');
    }

    /**
     * @param int $marketplaceId
     * @param int $categoryId
     * @param string $title
     * @return $this
     */
    public function init(
        $marketplaceId,
        $categoryId,
        $title
    ) {
        $this->setData(ResourceModel::COLUMN_MARKETPLACE_ID, $marketplaceId);
        $this->setData(ResourceModel::COLUMN_CATEGORY_ID, $categoryId);
        $this->setData(ResourceModel::COLUMN_TITLE, $title);
        $this->setIsLeaf(false);

        return $this;
    }

    /**
     * @return int
     */
    public function getMarketplaceId()
    {
        return (int)$this->getData(ResourceModel::COLUMN_MARKETPLACE_ID);
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return (int)$this->getData(ResourceModel::COLUMN_CATEGORY_ID);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(ResourceModel::COLUMN_TITLE);
    }

    /**
     * @return bool
     */
    public function isExistsParentCategoryId()
    {
        return $this->getData(ResourceModel::COLUMN_PARENT_CATEGORY_ID) !== null;
    }

    /**
     * @return int
     */
    public function getParentCategoryId()
    {
        if (!$this->isExistsParentCategoryId()) {
            throw new \LogicException('Parent category id not set');
        }

        return (int)$this->getData(ResourceModel::COLUMN_PARENT_CATEGORY_ID);
    }

    /**
     * @param int $parentCategoryId
     * @return $this
     */
    public function setParentCategoryId($parentCategoryId)
    {
        $this->setData(ResourceModel::COLUMN_PARENT_CATEGORY_ID, $parentCategoryId);

        return $this;
    }

    /**
     * @return bool
     */
    public function isLeaf()
    {
        return (bool)$this->getData(ResourceModel::COLUMN_IS_LEAF);
    }

    /**
     * @return string
     */
    public function getProductTypeNick()
    {
        if (!$this->isLeaf()) {
            throw new \LogicException('Category is not leaf');
        }

        return $this->getData(ResourceModel::COLUMN_PRODUCT_TYPE_NICK);
    }

    /**
     * @return string
     */
    public function getProductTypeTitle()
    {
        if (!$this->isLeaf()) {
            throw new \LogicException('Category is not leaf');
        }

        return $this->getData(ResourceModel::COLUMN_PRODUCT_TYPE_TITLE);
    }

    /**
     * @param string $productTypeNick
     * @param string $productTypeTitle
     * @return $this
     */
    public function markAsLeaf(
        $productTypeNick,
        $productTypeTitle
    ) {
        $this->setIsLeaf(true);
        $this->setData(ResourceModel::COLUMN_PRODUCT_TYPE_NICK, $productTypeNick);
        $this->setData(ResourceModel::COLUMN_PRODUCT_TYPE_TITLE, $productTypeTitle);

        return $this;
    }

    /**
     * @param bool $isLeaf
     * @return void
     */
    private function setIsLeaf($isLeaf)
    {
        $this->setData(ResourceModel::COLUMN_IS_LEAF, $isLeaf);
    }
}
