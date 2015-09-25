<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_TemplateDescription_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    const ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED = 1;
    const ACTION_STATUS_VARIATIONS_NOT_SUPPORTED = 2;
    const ACTION_STATUS_READY_TO_BE_ASSIGNED = 3;

    protected $attributesSetsIds;
    protected $marketplaceId;
    protected $listingProduct;
    protected $variationProductsIds;

    protected $checkNewAsinAccepted = false;
    protected $productsIds;
    protected $mapToTemplateJsFn = 'ListingGridHandlerObj.templateDescriptionHandler.mapToTemplateDescription';

    // ####################################

    /**
     * @return string
     */
    public function getMapToTemplateJsFn()
    {
        return $this->mapToTemplateJsFn;
    }

    /**
     * @param string $mapToTemplateLink
     */
    public function setMapToTemplateJsFn($mapToTemplateLink)
    {
        $this->mapToTemplateJsFn = $mapToTemplateLink;
    }

    //------------------------------

    /**
     * @param boolean $checkNewAsinAccepted
     */
    public function setCheckNewAsinAccepted($checkNewAsinAccepted)
    {
        $this->checkNewAsinAccepted = $checkNewAsinAccepted;
    }

    /**
     * @return boolean
     */
    public function getCheckNewAsinAccepted()
    {
        return (bool) $this->checkNewAsinAccepted;
    }

    //------------------------------

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

    //------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateDescriptionGrid');

        // Set default values
        //------------------------------
        $this->setFilterVisibility(false);
        //$this->setPagerVisibility(false);
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        //------------------------------
    }

    //------------------------------

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();

        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Template_Description_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Template_Description');
        $collection->addFieldToFilter('marketplace_id', $this->getMarketplaceId());

        $this->setCollection($this->prepareCollection($collection));

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

        $this->addColumn('status', array(
            'header'       => Mage::helper('M2ePro')->__('Status/Reason'),
            'align'        => 'left',
            'type'         => 'text',
            'width'        => '140px',
            'index'        => 'title',
            'filter'       => false,
            'sortable'     => false,
            'frame_callback' => array($this, 'callbackColumnStatus')
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
                    'id' => 'description_template_refresh_btn',
                    'label'     => Mage::helper('M2ePro')->__('Refresh'),
                    'onclick'   => $this->getJsObjectName().'.reload()'
                ))
        );

        return parent::_prepareLayout();
    }

    // ####################################

    public function getRefreshButtonHtml()
    {
        return $this->getChildHtml('refresh_button');
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        return $this->getRefreshButtonHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $templateDescriptionEditUrl = $this->getUrl('*/adminhtml_common_amazon_template_description/edit', array(
            'id' => $row->getData('id')
        ));

        $title = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));

        $categoryWord = Mage::helper('M2ePro')->__('Category');
        $categoryPath = !empty($row['category_path']) ? "{$row['category_path']} ({$row['browsenode_id']})"
            : Mage::helper('M2ePro')->__('N/A');

        return <<<HTML
<a target="_blank" href="{$templateDescriptionEditUrl}">{$title}</a>
<div>
    <span style="font-weight: bold">{$categoryWord}</span>: <span style="color: #505050">{$categoryPath}</span><br/>
</div>
HTML;

    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch($row->getData('description_template_action_status')) {
            case self::ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED:
                return '<span style="color: #808080;">' .
                    Mage::helper('M2ePro')->__('New ASIN/ISBN feature is disabled') . '</span>';
                break;
            case self::ACTION_STATUS_VARIATIONS_NOT_SUPPORTED:
                return '<span style="color: #808080;">' .
                    Mage::helper('M2ePro')->__(
                        'Selected Category doesn\'t support Variational Products'
                    ) . '</span>';
                break;
        }

        return '<span style="color: green;">' . Mage::helper('M2ePro')->__('Ready to be assigned') . '</span>';
    }

    public function callbackColumnAction($value, $row, $column, $isExport)
    {
        $assignText = Mage::helper('M2ePro')->__('Assign');
        $mapToAsin = '';

        if ($this->getCheckNewAsinAccepted()) {
            $mapToAsin = ',1';
        }

        switch($row->getData('description_template_action_status')) {
            case self::ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED:
                return '<span style="color: #808080;">' . $assignText . '</span>';
                break;
            case self::ACTION_STATUS_VARIATIONS_NOT_SUPPORTED:
                return '<span style="color: #808080;">' . $assignText . '</span>';
                break;
        }

        return '<a href="javascript:void(0);"'
            . 'onclick="' . $this->getMapToTemplateJsFn() . '(this, '
            . $value . $mapToAsin .');">'.$assignText.'</a>';
    }

    // ####################################

    protected function _toHtml()
    {
        $productsIdsStr = implode(',', $this->getProductsIds());

        $javascriptsMain = <<<JAVASCRIPT
<script type="text/javascript">

    $$('#amazonTemplateDescriptionGrid div.grid th').each(function(el){
        el.style.padding = '5px 5px';
    });

    $$('#amazonTemplateDescriptionGrid div.grid td').each(function(el){
        el.style.padding = '5px 5px';
    });

    {$this->getJsObjectName()}.reloadParams = {$this->getJsObjectName()}.reloadParams || {};
    {$this->getJsObjectName()}.reloadParams['products_ids'] = '{$productsIdsStr}';

</script>
JAVASCRIPT;

        //------------------------------
        $templateDescriptionNewUrl = $this->getNewTemplateDescriptionUrl();

        $data = array(
            'id'    => 'templateDescription_addNew_button',
            'label' => Mage::helper('M2ePro')->__('Add New Description Policy'),
            'class' => 'templateDescription_addNew_button',
            'style' => 'float: right;',
            'onclick' => 'ListingGridHandlerObj.templateDescriptionHandler'
                . '.createTemplateDescriptionInNewTab(\'' . $templateDescriptionNewUrl . '\')'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        //------------------------------

        $buttonBlockHtml = ($this->canDisplayContainer()) ? $buttonBlock->toHtml(): '';

        return parent::_toHtml() . $buttonBlockHtml . $javascriptsMain;
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/viewTemplateDescriptionsGrid', array(
            '_current' => true,
            '_query' => array(
                'check_is_new_asin_accepted' => $this->getCheckNewAsinAccepted()
            )
        ));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    protected function getMarketplaceId()
    {
        if(empty($this->marketplaceId)) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $productsIds = $this->getProductsIds();
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productsIds[0]);
            $this->marketplaceId = $listingProduct->getListing()->getMarketplaceId();
        }

        return $this->marketplaceId;
    }

    //---------------------------------------

    protected function setNoTemplatesText()
    {
        $templateDescriptionEditUrl = $this->getNewTemplateDescriptionUrl();

        $messageTxt = Mage::helper('M2ePro')->__('Description Policies are not found for current Marketplace.');
        $linkTitle = Mage::helper('M2ePro')->__('Create New Description Policy.');

        $message = <<<HTML
<p>{$messageTxt} <a href="javascript:void(0);"
    id="templateDescription_addNew_link"
    onclick="ListingGridHandlerObj.templateDescriptionHandler.createTemplateDescriptionInNewTab(
        '{$templateDescriptionEditUrl}');">{$linkTitle}</a>
</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateDescriptionUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_template_description/new', array(
            'is_new_asin_accepted'  => $this->getCheckNewAsinAccepted(),
            'marketplace_id'        => $this->getMarketplaceId()
        ));
    }

    //---------------------------------------

    protected function getParentListingProduct()
    {
        $productIds = $this->getProductsIds();
        if (count($productIds) == 1 && empty($this->listingProduct)) {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productIds[0]);
            if ($listingProduct->getChildObject()->getVariationManager()->isVariationParent()) {
                $this->listingProduct = $listingProduct;
            }
        }
        return $this->listingProduct;
    }

    //---------------------------------------

    protected function getVariationsProductsIds()
    {
        if (is_null($this->variationProductsIds)) {
            $this->variationProductsIds = array();

            /** @var Ess_M2ePro_Model_Mysql4_Amazon_Listing_Product_Collection $collection */
            $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
            $collection->addFieldToFilter('additional_data', array('notnull' => true));
            $collection->addFieldToFilter('id', array('in' => $this->getProductsIds()));
            $collection->addFieldToFilter('is_variation_parent', 1);

            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns(
                array(
                    'main_table.id'
                )
            );

            $this->variationProductsIds = $collection->getData();
        }

        return $this->variationProductsIds;
    }

    // ####################################

    private function prepareCollection($collection)
    {
        /** @var Ess_M2ePro_Model_Mysql4_Amazon_Template_Description_Collection $preparedCollection */
        $preparedCollection = new Varien_Data_Collection();

        $data = $collection->getData();
        $preparedData = array();
        foreach ($data as $item) {
            if (!$this->getCheckNewAsinAccepted()) {
                $item['description_template_action_status'] = self::ACTION_STATUS_READY_TO_BE_ASSIGNED;
                $preparedData[] = $item;
                continue;
            }

            if (!$item['is_new_asin_accepted']) {
                $item['description_template_action_status'] = self::ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED;
                $preparedData[] = $item;
                continue;
            }

            $variationProductsIds = $this->getVariationsProductsIds();

            if (!empty($variationProductsIds)) {
                $detailsModel = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
                $detailsModel->setMarketplaceId($this->getMarketplaceId());
                $themes = $detailsModel->getVariationThemes($item['product_data_nick']);

                if (empty($themes)) {
                    $item['description_template_action_status'] = self::ACTION_STATUS_VARIATIONS_NOT_SUPPORTED;
                    $preparedData[] = $item;
                    continue;
                }
            }

            $item['description_template_action_status'] = self::ACTION_STATUS_READY_TO_BE_ASSIGNED;
            $preparedData[] = $item;
            continue;
        }

        if (!empty($preparedData)) {
            usort($preparedData, function($a, $b)
            {
                return $a["description_template_action_status"] < $b["description_template_action_status"];
            });

            foreach ($preparedData as $item) {
                $preparedCollection->addItem(new Varien_Object($item));
            }
        }

        return $preparedCollection;
    }

    // ####################################
}