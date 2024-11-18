<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Template_ProductType_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    const ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED = 1;
    const ACTION_STATUS_VARIATIONS_NOT_SUPPORTED = 2;
    const ACTION_STATUS_READY_TO_BE_ASSIGNED = 3;

    protected $_checkNewAsinAccepted = false;

    protected $_productsIds        = array();
    protected $_magentoCategoryIds = array();

    protected $_marketplaceId;
    protected $_variationProductsIds;

    protected $_mapToTemplateJsFn = 'ListingGridObj.templateProductType.mapToTemplateProductType';
    protected $_createNewTemplateJsFn =
        'ListingGridObj.templateProductType.createTemplateProductTypeInNewTab';

    protected $_cacheData = array();

    //########################################

    /**
     * @return string
     */
    public function getMapToTemplateJsFn()
    {
        return $this->_mapToTemplateJsFn;
    }

    /**
     * @param string $mapToTemplateLink
     */
    public function setMapToTemplateJsFn($mapToTemplateLink)
    {
        $this->_mapToTemplateJsFn = $mapToTemplateLink;
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getCreateNewTemplateJsFn()
    {
        return $this->_createNewTemplateJsFn;
    }

    /**
     * @param string $createNewTemplateJsFn
     */
    public function setCreateNewTemplateJsFn($createNewTemplateJsFn)
    {
        $this->_createNewTemplateJsFn = $createNewTemplateJsFn;
    }

    // ---------------------------------------

    /**
     * @param boolean $checkNewAsinAccepted
     */
    public function setCheckNewAsinAccepted($checkNewAsinAccepted)
    {
        $this->_checkNewAsinAccepted = $checkNewAsinAccepted;
    }

    /**
     * @return boolean
     */
    public function getCheckNewAsinAccepted()
    {
        return (bool) $this->_checkNewAsinAccepted;
    }

    // ---------------------------------------

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

    public function setMagentoCategoryIds($magentoCategoryIds)
    {
        $this->_magentoCategoryIds = $magentoCategoryIds;
    }

    public function getMagentoCategoryIds()
    {
        return $this->_magentoCategoryIds;
    }

    // ---------------------------------------

    public function __construct()
    {
        parent::__construct();

        $this->setId('amazonTemplateProductTypeGrid');

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

    protected function _prepareCollection()
    {
        $this->setNoTemplatesText();
        /** @var Ess_M2ePro_Model_Resource_Amazon_Dictionary_ProductType $dictionaryProductTypeResource */
        $dictionaryProductTypeResource = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType');

        /** @var Ess_M2ePro_Model_Resource_Amazon_Template_ProductType_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType_Collection');
        $collection->getSelect()
            ->join(
                array('adpt' => $dictionaryProductTypeResource->getMainTable()),
                'adpt.id = main_table.dictionary_product_type_id',
                array('product_type_title' => 'adpt.title')
            );

        $collection->addFieldToFilter('marketplace_id', $this->getMarketplaceId());

        $this->setCollection($collection);
        $this->prepareCacheData();

        return parent::_prepareCollection();
    }

    // ---------------------------------------

    protected function prepareCacheData()
    {
        $this->_cacheData = array();
        $tempCollection   = clone $this->getCollection();

        /** @var Ess_M2ePro_Model_Amazon_Template_ProductType $item */
        foreach ($tempCollection->getItems() as $item) {
            if (!$this->getCheckNewAsinAccepted()) {
                $this->_cacheData[$item->getId()] = self::ACTION_STATUS_READY_TO_BE_ASSIGNED;
                continue;
            }

            if (!$item->getData('is_new_asin_accepted')) {
                $this->_cacheData[$item->getId()] = self::ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED;
                continue;
            }

            $variationProductsIds = $this->getVariationsProductsIds();
            if (!empty($variationProductsIds)) {

                $themes = $item->getDictionary()->getVariationThemes();
                if (empty($themes)) {
                    $this->_cacheData[$item->getId()] = self::ACTION_STATUS_VARIATIONS_NOT_SUPPORTED;
                    continue;
                }
            }

            $this->_cacheData[$item->getId()] = self::ACTION_STATUS_READY_TO_BE_ASSIGNED;
            continue;
        }
    }

    // ---------------------------------------

    protected function _prepareColumns()
    {
        $this->addColumn(
            'title', array(
                'header'       => Mage::helper('M2ePro')->__('Title'),
                'align'        => 'left',
                'type'         => 'text',
                'index'        => 'title',
                'filter_index' => 'main_table.title',
                'sortable'     => true,
                'frame_callback' => array($this, 'callbackColumnTitle')
            )
        );

        $this->addColumn(
            'action', array(
                'header'       => Mage::helper('M2ePro')->__('Action'),
                'align'        => 'left',
                'type'         => 'number',
                'width'        => '55px',
                'index'        => 'id',
                'filter'       => false,
                'sortable'     => false,
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
                        'id'      => 'product_type_template_refresh_btn',
                        'label'   => Mage::helper('M2ePro')->__('Refresh'),
                        'onclick' => $this->getJsObjectName() . '.reload()'
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
        $templateProductTypeEditUrl = $this->getUrl(
            '*/adminhtml_amazon_productTypes/edit', array(
                'id' => $row->getData('id')
            )
        );

        $title = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));

        return <<<HTML
<a target="_blank" href="{$templateProductTypeEditUrl}">{$title}</a>
HTML;

    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $status = $this->_cacheData[$row->getId()];

        switch($status) {
            case self::ACTION_STATUS_NEW_ASIN_NOT_ACCEPTED:
                return '<span style="color: #808080;">' .
                    Mage::helper('M2ePro')->__('New ASIN/ISBN feature is disabled') . '</span>';

            case self::ACTION_STATUS_VARIATIONS_NOT_SUPPORTED:
                return '<span style="color: #808080;">' .
                    Mage::helper('M2ePro')->__(
                        'Selected Category doesn\'t support Variational Products'
                    ) . '</span>';
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

        return '<a href="javascript:void(0);"'
            . 'onclick="' . $this->getMapToTemplateJsFn() . '(this, '
            . $value . $mapToAsin .');">'.$assignText.'</a>';
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Template_ProductType_Collection $collection */

        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        $collection->getSelect()->where(
            'title LIKE ? OR category_path LIKE ? OR browsenode_id LIKE ?', '%'.$value.'%'
        );
    }

    /**
     * @param Ess_M2ePro_Model_Resource_Amazon_Template_ProductType_Collection $collection
     */
    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        if ($value == null) {
            return;
        }

        foreach ($collection->getItems() as $item) {
            /**@var Ess_M2ePro_Model_Amazon_Template_ProductType $item */

            if ($this->_cacheData[$item->getId()] != $value) {
                $collection->removeItemByKey($item->getId());
            }
        }
    }

    //########################################

    protected function _toHtml()
    {
        $productsIdsStr = implode(',', $this->getProductsIds());
        $magentoIdsStr  = implode(',', $this->getMagentoCategoryIds());

        $javascriptsMain = <<<HTML
<script type="text/javascript">

    $$('#amazonTemplateProductTypeGrid div.grid th').each(function(el) {
        el.style.padding = '5px 5px';
    });

    $$('#amazonTemplateProductTypeGrid div.grid td').each(function(el) {
        el.style.padding = '5px 5px';
    });

    {$this->getJsObjectName()}.reloadParams = {$this->getJsObjectName()}.reloadParams || {};
    {$this->getJsObjectName()}.reloadParams['products_ids'] = '{$productsIdsStr}';
    {$this->getJsObjectName()}.reloadParams['magento_categories_ids'] = '{$magentoIdsStr}';
    {$this->getJsObjectName()}.reloadParams['create_new_template_js_function'] = '{$this->getCreateNewTemplateJsFn()}';

</script>
HTML;

        // ---------------------------------------
        $templateProductTypeNewUrl = $this->getNewTemplateProductTypeUrl();

        $data = array(
            'id'    => 'templateProductType_addNew_button',
            'label' => Mage::helper('M2ePro')->__('Add New Product Type'),
            'class' => 'templateProductType_addNew_button',
            'style' => 'float: right;',
            'onclick' => "{$this->getCreateNewTemplateJsFn()}('$templateProductTypeNewUrl')"
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
            '*/*/viewTemplateProductTypesGrid', array(
                '_current' => true,
                '_query'   => array(
                    'check_is_new_asin_accepted' => $this->getCheckNewAsinAccepted()
                )
            )
        );
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    protected function getMarketplaceId()
    {
        if (empty($this->_marketplaceId)) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $productsIds          = $this->getProductsIds();
            $listingProduct       = Mage::helper('M2ePro/Component_Amazon')->getObject(
                'Listing_Product', $productsIds[0]
            );
            $this->_marketplaceId = $listingProduct->getListing()->getMarketplaceId();
        }

        return $this->_marketplaceId;
    }

    // ---------------------------------------

    protected function setNoTemplatesText()
    {
        $messageTxt = Mage::helper('M2ePro')->__('Product Types are not found for current Marketplace.');

        $message = <<<HTML
<p>{$messageTxt}</p>
HTML;

        $this->setEmptyText($message);
    }

    protected function getNewTemplateProductTypeUrl()
    {
        $isActiveWizard = Mage::helper('M2ePro/Module_Wizard')->isActive(
            Ess_M2ePro_Helper_View_Amazon::WIZARD_INSTALLATION_NICK
        );

        return $this->getUrl(
            '*/adminhtml_amazon_productTypes/new', array(
                'is_new_asin_accepted' => $this->getCheckNewAsinAccepted(),
                'marketplace_id'       => $this->getMarketplaceId(),
                'wizard'               => $isActiveWizard,
                'close_on_save'        => true
            )
        );
    }

    // ---------------------------------------

    protected function getVariationsProductsIds()
    {
        if ($this->_variationProductsIds === null) {
            $this->_variationProductsIds = array();

            /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Collection $collection */
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

            $this->_variationProductsIds = $collection->getData();
        }

        return $this->_variationProductsIds;
    }

    //########################################
}