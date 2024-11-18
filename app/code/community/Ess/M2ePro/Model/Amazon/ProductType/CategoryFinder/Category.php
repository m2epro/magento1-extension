<?php

class Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_Category
{
    /** @var string */
    private $name;
    /** @var bool */
    private $isLeaf;
    /** @var Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_ProductType[] */
    private $productTypes = array();
    /** @var string[] */
    private $path;

    public function __construct($name, $isLeaf)
    {
        $this->name = $name;
        $this->isLeaf = $isLeaf;
    }

    public function setPath(array $path)
    {
        $this->path = $path;
    }

    public function addProductType(Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_ProductType $productType)
    {
        $this->productTypes[] = $productType;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIsLeaf()
    {
        return $this->isLeaf;
    }

    public function getProductTypes()
    {
        return $this->productTypes;
    }

    public function getPath()
    {
        return $this->path;
    }
}