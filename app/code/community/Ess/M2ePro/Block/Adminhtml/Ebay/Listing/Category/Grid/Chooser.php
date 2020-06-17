<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Grid_Chooser
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_marketplaceId;
    protected $_accountId;
    protected $_categoryMode;

    protected $_categoriesData = array();

    //########################################

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button');
        $buttonBlock->setData(
            array(
                'class'   => 'save done',
                'label'   => $helper->__('Save'),
                'onclick' => 'EbayListingCategoryGridObj.confirmCategoriesData();'
            )
        );

        /** @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Template_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_category_chooser');
        $chooserBlock->setAccountId($this->_accountId);
        $chooserBlock->setMarketplaceId($this->_marketplaceId);
        $chooserBlock->setCategoryMode($this->_categoryMode);
        $chooserBlock->setCategoriesData($this->_categoriesData);

        return <<<HTML
<div id="ebay_category_chooser" style="padding-top: 15px">
    {$chooserBlock->toHtml()}
</div>

<div style="position: absolute; bottom: 0; right: 0; margin: 10px 25px;">
    <a onclick="EbayListingCategoryGridObj.cancelCategoriesData();"
       href="javascript:void(0);">{$helper->__('Cancel')}
</a>
    &nbsp;&nbsp;&nbsp;
    {$buttonBlock->toHtml()}
</div>

<div style="clear: both"></div>
HTML;
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

    public function setCategoryMode($mode)
    {
        $this->_categoryMode = $mode;
        return $this;
    }

    public function setCategoriesData(array $data)
    {
        $this->_categoriesData = $data;
        return $this;
    }

    //########################################
}
