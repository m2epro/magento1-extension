<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Template_ShippingOverride_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $marketplaceId;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateShippingOverrideGrid');

        // Set default values
        // ---------------------------------------
        $this->setFilterVisibility(false);
        $this->setDefaultSort('id');
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
        return $this->marketplaceId;
    }

    /**
     * @param mixed $marketplaceId
     */
    public function setMarketplaceId($marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;
    }

    // ---------------------------------------

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Template_Description_Collection $collection */
        $collection = Mage::getModel('M2ePro/Amazon_Template_ShippingOverride')->getCollection();
        $collection->addFieldToFilter('marketplace_id', $this->getMarketplaceId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('title', array(
            'header'       => Mage::helper('M2ePro')->__('Title'),
            'align'        => 'left',
            'type'         => 'text',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnTitle')
        ));

        $this->addColumn('action', array(
            'header'       => Mage::helper('M2ePro')->__('Action'),
            'align'        => 'left',
            'type'         => 'number',
            'width'        => '55px',
            'index'        => 'id',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnAction')
        ));
    }

    protected function _prepareLayout()
    {
        $this->setChild('refresh_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'id' => 'shipping_override_template_refresh_btn',
                    'label'     => Mage::helper('M2ePro')->__('Refresh'),
                    'onclick'   => $this->getJsObjectName().'.reload()'
                ))
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
        $templateDescriptionEditUrl = $this->getUrl('*/adminhtml_common_amazon_template_shippingOverride/edit', array(
            'id' => $row->getData('id')
        ));

        $title = Mage::helper('M2ePro')->escapeHtml($value);

        return <<<HTML
<a target="_blank" href="{$templateDescriptionEditUrl}">{$title}</a>
HTML;

    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign');

        return <<<HTML
<a href="javascript:void(0)"
    class="assign-shipping-override-template"
    templateShippingOverrideId="{$value}">
    {$assignText}
</a>
HTML;

    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#amazonTemplateShippingOverrideGrid div.grid th').each(function(el) {
        el.style.padding = '5px 5px';
    });

    $$('#amazonTemplateShippingOverrideGrid div.grid td').each(function(el) {
        el.style.padding = '5px 5px';
    });

    ListingGridHandlerObj.templateShippingOverrideHandler.newTemplateUrl='{$this->getNewTemplateShippingOverrideUrl()}';

</script>
HTML;

        // ---------------------------------------
        $data = array(
            'label' => Mage::helper('M2ePro')->__('Add New Shipping Override Policy'),
            'class' => 'new-shipping-override-template',
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
        return $this->getUrl('*/*/viewTemplateShippingOverrideGrid', array(
            '_current' => true,
            '_query' => array(
                'marketplace_id' => $this->getMarketplaceId()
            )
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function setNoTemplatesText()
    {
        $messageTxt = Mage::helper('M2ePro')->__('Shipping Override Policies are not found for current Marketplace.');
        $linkTitle = Mage::helper('M2ePro')->__('Create New Shipping Override Policy.');

        $message = <<<HTML
<p>{$messageTxt} <a href="javascript:void(0);"
    class="new-shipping-override-template">{$linkTitle}</a>
</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateShippingOverrideUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_shippingOverride/new', array(
            'marketplace_id'        => $this->getMarketplaceId()
        ));
    }

    //########################################
}