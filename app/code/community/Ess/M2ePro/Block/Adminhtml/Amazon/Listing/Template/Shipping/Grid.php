<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Template_Shipping_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_marketplaceId;
    protected $_productsIds;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateShippingGrid');

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(true);
        $this->setDefaultSort('title');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);

        // ---------------------------------------
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getMarketplaceId()
    {
        return $this->_marketplaceId;
    }

    /**
     * @param mixed $marketplaceId
     */
    public function setMarketplaceId($marketplaceId)
    {
        $this->_marketplaceId = $marketplaceId;
    }

    /**
     * @param mixed $productsIds
     */
    public function setProductsIds($productsIds)
    {
        $this->_productsIds = $productsIds;
    }

    /**
     * @return mixed
     */
    public function getProductsIds()
    {
        return $this->_productsIds;
    }

    // ---------------------------------------

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();

        /** @var Ess_M2ePro_Model_Resource_Amazon_Template_Shipping_Collection $collection */
        $collection = Mage::getModel('M2ePro/Amazon_Template_Shipping')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title', array(
                'header'         => Mage::helper('M2ePro')->__('Title'),
                'align'          => 'left',
                'type'           => 'text',
                'index'          => 'title',
                'filter_index'   => 'title',
                'sortable'       => true,
                'frame_callback' => array($this, 'callbackColumnTitle')
            )
        );

        $this->addColumn(
            'action', array(
                'header'         => Mage::helper('M2ePro')->__('Action'),
                'align'          => 'left',
                'type'           => 'number',
                'width'          => '55px',
                'index'          => 'id',
                'filter'         => false,
                'sortable'       => false,
                'frame_callback' => array($this, 'callbackColumnAction')
            )
        );
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'refresh_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'id'      => 'shipping_template_refresh_btn',
                        'label'   => Mage::helper('M2ePro')->__('Refresh'),
                        'onclick' => "ListingGridHandlerObj.templateShippingHandler.loadGrid()"
                    )
                )
        );

        return parent::_prepareLayout();
    }

    //########################################

    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    //########################################

    public function getMainButtonsHtml()
    {
        return $this->getRefreshButtonHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $templateEditUrl = $this->getUrl(
            '*/adminhtml_amazon_template_shipping/edit', array(
                'id' => $row->getData('id')
            )
        );

        $title = Mage::helper('M2ePro')->escapeHtml($value);

        return <<<HTML
<a target="_blank" href="{$templateEditUrl}">{$title}</a>
HTML;

    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign');

        return <<<HTML
<a href="javascript:void(0)"
    class="assign-shipping-template"
    templateShippingId="{$value}">
    {$assignText}</a>
HTML;

    }

    //########################################

    protected function _toHtml()
    {
        $productsIdsStr = implode(',', $this->getProductsIds());

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#amazonTemplateShippingGrid div.grid th').each(function(el) {
        el.style.padding = '5px 5px';
    });

    $$('#amazonTemplateShippingGrid div.grid td').each(function(el) {
        el.style.padding = '5px 5px';
    });

    ListingGridHandlerObj.templateShippingHandler.newTemplateUrl='{$this->getNewTemplateShippingUrl()}';

    {$this->getJsObjectName()}.reloadParams = {$this->getJsObjectName()}.reloadParams || {};
    {$this->getJsObjectName()}.reloadParams['products_ids'] = '{$productsIdsStr}';

</script>
HTML;

        // ---------------------------------------
        $data = array(
            'label' => Mage::helper('M2ePro')->__('Add New Shipping Policy'),
            'class' => 'new-shipping-template',
            'style' => 'float: right;'
        );

        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        // ---------------------------------------

        $buttonBlockHtml = ($this->canDisplayContainer()) ? $buttonBlock->toHtml(): '';

        return parent::_toHtml() . $buttonBlockHtml . $javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/viewTemplateShippingGrid', array(
                '_current' => true,
                '_query'   => array(
                    'marketplace_id' => $this->getMarketplaceId()
                )
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function setNoTemplatesText()
    {
        $messageTxt = Mage::helper('M2ePro')->__('Shipping Policies are not found.');
        $linkTitle = Mage::helper('M2ePro')->__('Create New Shipping Policy.');

        $message = <<<HTML
<p>{$messageTxt} <a href="javascript:void(0);"
    class="new-shipping-template">{$linkTitle}</a>
</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateShippingUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_template_shipping/new');
    }

    //########################################
}
