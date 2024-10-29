<?php

class Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category
{
    /** @var int */
    private $id;
    /** @var int|null */
    private $parentId;
    /** @var string */
    private $title;
    /** @var bool */
    private $isLeaf;
    /** @var Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category_ProductType|null */
    private $productType = null;

    /**
     * @param int $id
     * @param int|null $parentId
     * @param string $title
     * @param bool $isLeaf
     */
    public function __construct(
        $id,
        $parentId,
        $title,
        $isLeaf
    ) {
        $this->id = $id;
        $this->parentId = $parentId;
        $this->title = $title;
        $this->isLeaf = $isLeaf;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return bool
     */
    public function isLeaf()
    {
        return $this->isLeaf;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category_ProductType|null
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @return void
     */
    public function setProductType(
        Ess_M2ePro_Model_Walmart_Connector_Marketplace_GetCategories_Response_Category_ProductType $productType
    ) {
        $this->productType = $productType;
    }
}
