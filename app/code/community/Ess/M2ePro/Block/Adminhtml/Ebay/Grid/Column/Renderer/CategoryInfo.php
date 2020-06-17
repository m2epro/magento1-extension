<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;

class Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_CategoryInfo
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /** @var Varien_Object */
    protected $_row;

    /** @var array */
    protected $_categoriesData = array();

    /** @var string */
    protected $_entityIdField;

    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    /** @var bool */
    protected $_hideSpecificsRequiredMark = false;

    /** @var bool */
    protected $_hideUnselectedSpecifics = false;

    //########################################

    public function render(Varien_Object $row)
    {
        $this->_row = $row;

        $id = $row->getData($this->_entityIdField);
        $categoriesData = isset($this->_categoriesData[$id]) ? $this->_categoriesData[$id] : array();

        $html = '';
        $html .= $this->renderCategoryInfo($categoriesData, eBayCategory::TYPE_EBAY_MAIN);
        $html .= $this->renderItemSpecifics($categoriesData);
        $html .= $this->renderCategoryInfo($categoriesData, eBayCategory::TYPE_EBAY_SECONDARY);
        $html .= $this->renderCategoryInfo($categoriesData, eBayCategory::TYPE_STORE_MAIN);
        $html .= $this->renderCategoryInfo($categoriesData, eBayCategory::TYPE_STORE_SECONDARY);

        if (empty($html)) {
            $helper = Mage::helper('M2ePro');

            $iconSrc = $this->getSkinUrl('M2ePro/images/warning.png');
            $html .= <<<HTML
<img src="{$iconSrc}" alt="">&nbsp;<span style="font-style: italic; color: gray">{$helper->__('Not Selected')}</span>
HTML;
        }

        return $html;
    }

    protected function renderCategoryInfo($categoryData, $categoryType)
    {
        $helper = Mage::helper('M2ePro');
        $titles = array(
            eBayCategory::TYPE_EBAY_MAIN       => $helper->__('eBay Primary Category'),
            eBayCategory::TYPE_EBAY_SECONDARY  => $helper->__('eBay Secondary Category'),
            eBayCategory::TYPE_STORE_MAIN      => $helper->__('Store Primary Category'),
            eBayCategory::TYPE_STORE_SECONDARY => $helper->__('Store Secondary Category')
        );

        if (!isset($categoryData[$categoryType], $titles[$categoryType]) ||
            !isset(
                $categoryData[$categoryType]['mode'],
                $categoryData[$categoryType]['path'],
                $categoryData[$categoryType]['value']
            )
        ) {
            return '';
        }

        $info = '';
        if ($categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_EBAY) {
            $info = "{$categoryData[$categoryType]['path']}&nbsp;({$categoryData[$categoryType]['value']})";
        } elseif ($categoryData[$categoryType]['mode'] == TemplateCategory::CATEGORY_MODE_ATTRIBUTE) {
            $info = $categoryData[$categoryType]['path'];
        }

        return <<<HTML
<div>
    <span style="text-decoration: underline">{$titles[$categoryType]}:</span>
    <p style="padding: 2px 0 0 10px;">{$info}</p>
</div>
HTML;
    }

    protected function renderItemSpecifics($categoryData)
    {
        if (empty($categoryData[eBayCategory::TYPE_EBAY_MAIN]['value'])) {
            return '';
        }

        if (!isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template']) &&
            $this->_hideUnselectedSpecifics
        ) {
            return '';
        }

        $helper = Mage::helper('M2ePro');
        $specificsRequired = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
            $categoryData[eBayCategory::TYPE_EBAY_MAIN]['value'],
            $this->_listing->getMarketplaceId()
        );

        $requiredMark = '';
        if ($specificsRequired && !$this->_hideSpecificsRequiredMark) {
            $requiredMark = '&nbsp;<span class="required">*</span>';
        }

        if (!isset($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template'])) {
            $color = $specificsRequired ? 'red' : 'grey';
            $info = <<<HTML
<span style="font-style: italic; color: {$color}">{$helper->__('Not Set')}</span>
HTML;
        } elseif ($categoryData[eBayCategory::TYPE_EBAY_MAIN]['is_custom_template'] == 1) {
            $info = "<span>{$helper->__('Custom')}</span>";
        } else {
            $info = "<span>{$helper->__('Default')}</span>";
        }

        return <<<HTML
<div style="margin-bottom: .5em;">
    <span style="text-decoration: underline;">{$helper->__('Item Specifics')}:</span>{$requiredMark}&nbsp;
    {$info}
</div>
HTML;
    }

    //########################################

    public function setCategoriesData($data)
    {
        $this->_categoriesData = $data;
        return $this;
    }

    public function setHideSpecificsRequiredMark($mode)
    {
        $this->_hideSpecificsRequiredMark = $mode;
        return $this;
    }

    public function setHideUnselectedSpecifics($mode)
    {
        $this->_hideUnselectedSpecifics = $mode;
        return $this;
    }

    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->_listing = $listing;
        return $this;
    }

    public function setEntityIdField($field)
    {
        $this->_entityIdField = $field;
        return $this;
    }

    //########################################
}
