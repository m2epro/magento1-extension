<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Template_Category_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Category');
    }

    // ########################################

    /**
     * @param $primaryCategoriesData
     * @return  Ess_M2ePro_Model_Ebay_Template_Category[]
    */
    public function getItemsByPrimaryCategories($primaryCategoriesData)
    {
        $conn = $this->getConnection();

        $where = '';
        foreach ($primaryCategoriesData as $categoryData) {

            $where && $where .= ' OR ';

            $categoryData['category_main_id'] = (int)$categoryData['category_main_id'];
            $categoryData['marketplace_id']   = (int)$categoryData['marketplace_id'];

            $where .= "(marketplace_id  = {$categoryData['marketplace_id']} AND";
            $where .= " category_main_id   = {$categoryData['category_main_id']} AND";
            $where .= " category_main_mode = {$conn->quote($categoryData['category_main_mode'])} AND";
            $where .= " category_main_attribute = {$conn->quote($categoryData['category_main_attribute'])}) ";
        }

        $this->getSelect()->where($where);
        $this->getSelect()->order('create_date DESC');

        $templates = array();
        /* @var $template Ess_M2ePro_Model_Ebay_Template_Category */
        foreach ($this->getItems() as $template) {

            if ($template['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $key = $template['category_main_id'];
            } else {
                $key = $template['category_main_attribute'];
            }

            if (isset($templates[$key])) {
                continue;
            }

            $templates[$key] = $template;
        }

        return $templates;
    }

    // ########################################
}