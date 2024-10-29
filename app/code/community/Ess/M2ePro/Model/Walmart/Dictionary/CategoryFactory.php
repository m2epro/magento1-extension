<?php

class Ess_M2ePro_Model_Walmart_Dictionary_CategoryFactory
{
    /**
     * @param int $marketplaceId
     * @param int $categoryId
     * @param string $title
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category
     */
    public function createAsRoot(
        $marketplaceId,
        $categoryId,
        $title
    ) {
        return $this->create(
            $marketplaceId,
            $categoryId,
            $title
        );
    }

    /**
     * @param int $marketplaceId
     * @param int $parentCategoryId
     * @param int $categoryId
     * @param string $title
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category
     */
    public function createAsChild(
        $marketplaceId,
        $parentCategoryId,
        $categoryId,
        $title
    ) {
        $category = $this->create(
            $marketplaceId,
            $categoryId,
            $title
        );
        $category->setParentCategoryId($parentCategoryId);

        return $category;
    }

    /**
     * @param int $marketplaceId
     * @param int $parentCategoryId
     * @param int $categoryId
     * @param string $title
     * @param string $productTypeNick
     * @param string $productTypeTitle
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category
     */
    public function createAsLeaf(
        $marketplaceId,
        $parentCategoryId,
        $categoryId,
        $title,
        $productTypeNick,
        $productTypeTitle
    ) {
        $category = $this->create(
            $marketplaceId,
            $categoryId,
            $title
        );
        $category->setParentCategoryId($parentCategoryId);
        $category->markAsLeaf(
            $productTypeNick,
            $productTypeTitle
        );

        return $category;
    }

    /**
     * @param int $marketplaceId
     * @param int $categoryId
     * @param string $title
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category
     */
    private function create($marketplaceId, $categoryId, $title)
    {
        return $this->createEmpty()
            ->init(
                $marketplaceId,
                $categoryId,
                $title
            );
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Dictionary_Category
     */
    public function createEmpty()
    {
        /** @var Ess_M2ePro_Model_Walmart_Dictionary_Category */
        return Mage::getModel('M2ePro/Walmart_Dictionary_Category');
    }
}
