<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Template_ProductTaxCode_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $productsIds;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateProductTaxCodeGrid');

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
     * @param mixed $productsIds
     */
    public function setProductsIds($productsIds)
    {
        $this->productsIds = $productsIds;
    }

    /**
     * @return mixed
     */
    public function getProductsIds()
    {
        return $this->productsIds;
    }

    // ---------------------------------------

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Template_ProductTaxCode_Collection $collection */
        $collection = Mage::getModel('M2ePro/Amazon_Template_ProductTaxCode')->getCollection();
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
            'filter_index' => 'title',
            'sortable'     => true,
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
                    'id' => 'productTaxCode_template_refresh_btn',
                    'label'     => Mage::helper('M2ePro')->__('Refresh'),
                    'onclick'   => "ListingGridHandlerObj.templateProductTaxCodeHandler.loadGrid()"
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
        $templateEditUrl = $this->getUrl('*/adminhtml_amazon_template_productTaxCode/edit', array(
            'id' => $row->getData('id')
        ));

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
    class="assign-productTaxCode-template"
    templateProductTaxCodeId="{$value}">
    {$assignText}</a>
HTML;

    }

    //########################################

    protected function _toHtml()
    {
        $productsIdsStr = implode(',', $this->getProductsIds());

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#amazonTemplateProductTaxCodeGrid div.grid th').each(function(el) {
        el.style.padding = '5px 5px';
    });

    $$('#amazonTemplateProductTaxCodeGrid div.grid td').each(function(el) {
        el.style.padding = '5px 5px';
    });

    ListingGridHandlerObj.templateProductTaxCodeHandler.newTemplateUrl='{$this->getNewTemplateProductTaxCodeUrl()}';

    {$this->getJsObjectName()}.reloadParams = {$this->getJsObjectName()}.reloadParams || {};
    {$this->getJsObjectName()}.reloadParams['products_ids'] = '{$productsIdsStr}';
</script>
HTML;

        // ---------------------------------------
        $data = array(
            'label' => Mage::helper('M2ePro')->__('Add New Product Tax Code Policy'),
            'class' => 'new-productTaxCode-template',
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
        return $this->getUrl('*/adminhtml_amazon_template_productTaxCode/viewGrid', array(
            '_current' => true
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function setNoTemplatesText()
    {
        $messageTxt = Mage::helper('M2ePro')->__('Product Tax Code Policies are not found.');
        $linkTitle = Mage::helper('M2ePro')->__('Create New Product Tax Code Policy.');

        $message = <<<HTML
<p>{$messageTxt} <a href="javascript:void(0);"
    class="new-productTaxCode-template">{$linkTitle}</a>
</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateProductTaxCodeUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_template_productTaxCode/new');
    }

    //########################################
}