<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as Category;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;

class Ess_M2ePro_Model_Ebay_Template_Category_Chooser_Converter
{
    protected $_marketplaceId;
    protected $_accountId;

    protected $_categoriesData = array();

    //########################################

    /**
     * @param array $data
     * @param $type
     * @return $this
     */
    public function setCategoryDataFromTemplate(array $data, $type)
    {
        if (!isset($data['category_mode'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Category mode is not provided.');
        }

        $converted = array(
            'category_mode'      => $data['category_mode'],
            'category_id'        => $data['category_id'],
            'category_attribute' => $data['category_attribute'],
            'category_path'      => $data['category_path'],
            'template_id'        => $data['id'],
            'is_custom_template' => isset($data['is_custom_template']) ? $data['is_custom_template'] : null,
            'specific'           => isset($data['specific'])           ? $data['specific'] : array()
        );

        $this->_categoriesData[$type] = $converted;
        return $this;
    }

    /**
     * @param array $data
     * @param $type
     * @return $this
     */
    public function setCategoryDataFromChooser(array $data, $type)
    {
        if (empty($data)) {
            return $this;
        }

        $converted = array(
            'category_mode'      => $data['mode'],
            'category_id'        => $data['mode'] == TemplateCategory::CATEGORY_MODE_EBAY ? $data['value'] : null,
            'category_attribute' => $data['mode'] == TemplateCategory::CATEGORY_MODE_ATTRIBUTE ? $data['value'] : null,
            'category_path'      => isset($data['path'])               ? $data['path'] : null,
            'template_id'        => isset($data['template_id'])        ? $data['template_id'] : null,
            'is_custom_template' => isset($data['is_custom_template']) ? $data['is_custom_template'] : null,
            'specific'           => isset($data['specific'])           ? $data['specific'] : array()
        );

        $this->_categoriesData[$type] = $converted;
        return $this;
    }

    //----------------------------------------

    public function getCategoryDataForChooser($type = null)
    {
        if ($type === null) {
            $result = array();
            foreach ($this->getCategoriesTypes() as $cType) {
                $temp = $this->getCategoryDataForChooser($cType);
                $temp !== null && $result[$cType] = $temp;
            }

            return $result;
        }

        if (!isset($this->_categoriesData[$type])) {
            return null;
        }

        $part = $this->_categoriesData[$type];
        return array(
            'mode'               => $part['category_mode'],
            'value'              => $part['category_mode'] == TemplateCategory::CATEGORY_MODE_EBAY
                                        ? $part['category_id'] : $part['category_attribute'],
            'path'               => $part['category_path'],
            'template_id'        => $part['template_id'],
            'is_custom_template' => $part['is_custom_template'],
        );
    }

    public function getCategoryDataForTemplate($type)
    {
        if (!isset($this->_categoriesData[$type])) {
            return array();
        }

        $part = $this->_categoriesData[$type];
        $part['account_id']     = $this->_accountId;
        $part['marketplace_id'] = $this->_marketplaceId;

        return $part;
    }

    //########################################

    public function setMarketplaceId($marketplaceId)
    {
        $this->_marketplaceId = $marketplaceId;
        return $this;
    }

    public function setAccountId($accountId)
    {
        $this->_accountId = $accountId;
        return $this;
    }

    //########################################

    protected function getCategoriesTypes()
    {
        return array(
            Category::TYPE_EBAY_MAIN,
            Category::TYPE_EBAY_SECONDARY,
            Category::TYPE_STORE_MAIN,
            Category::TYPE_STORE_SECONDARY
        );
    }

    //########################################
}
