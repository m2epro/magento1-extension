<?php


class Ess_M2ePro_Model_Amazon_Connector_ProductType_SearchByCriteria_Response
{
    /** @var array */
    private $categories = array();

    public function addCategory($name, $isLeaf, array $nicksOfProductTypes)
    {
        $this->categories[] = array(
            'name' => $name,
            'isLeaf' => $isLeaf,
            'nicksOfProductTypes' => $nicksOfProductTypes,
        );
    }

    /**
     * @return list<array{name: string, isLeaf: bool, nicksOfProductTypes: string[]}>
     */
    public function getCategories()
    {
        return $this->categories;
    }
}