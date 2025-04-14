<?php

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Add_ValidateProductTypes_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    public function __construct()
    {
        parent::__construct();

        $this->setId('product_type_validation_grid');

        $this->setDefaultSort('product_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $dictionaryProductTypeResource = Mage::getResourceModel('M2ePro/Amazon_Dictionary_ProductType');
        $templateProductTypeResource = Mage::getResourceModel('M2ePro/Amazon_Template_ProductType');
        $validationProductTypeResource = Mage::getResourceModel('M2ePro/Amazon_ProductType_Validation');
        $listingProductsIds = $this->getListingProductIds();

        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection
            ->setListingProductModeOn()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku');

        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id = entity_id',
            array('id' => 'id',
                'additional_data' => 'additional_data',
            )
        );

        $collection->joinTable(
            array('alp' => 'M2ePro/Amazon_Listing_Product'),
            'listing_product_id = id',
            array(
                'product_type_id' => 'template_product_type_id',
            ),
            '{{table}}.variation_parent_id is NULL'
        );

        $collection->getSelect()->joinLeft(
            array('tpt' => $templateProductTypeResource->getMainTable()),
            'tpt.id = alp.template_product_type_id',
            array(
                'product_type_title' => 'title'
            )
        );

        $collection->getSelect()->joinLeft(
            array('pt' => $dictionaryProductTypeResource->getMainTable()),
            'pt.id = tpt.dictionary_product_type_id',
            array()
        );

        $collection->getSelect()->joinLeft(
            array('vd' => $validationProductTypeResource->getMainTable()),
            'lp.id = vd.listing_product_id',
            array(
                'validation_status' => 'status',
                'validation_error_messages' => 'error_messages',
            )

        );

        $collection->getSelect()->where('alp.general_id IS NULL');
        $collection->getSelect()->where('alp.template_product_type_id IS NOT NULL');
        $collection->getSelect()->where('lp.id IN (?)', $listingProductsIds);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header' => Mage::helper('M2ePro')->__('Product ID'),
                'align' => 'right',
                'type' => 'number',
                'index' => 'entity_id',
                'filter_index' => 'entity_id',
                'renderer'     => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name', array(
                'header' => Mage::helper('M2ePro')->__('Product Title / Product SKU'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'name',
                'filter_index' => 'name',
                'escape' => false,
                'frame_callback' => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle'),
            )
        );

        $this->addColumn(
            'product_type', array(
                'header' => Mage::helper('M2ePro')->__('Product Type'),
                'align' => 'left',
                'type' => 'text',
                'index' => 'product_type_title',
                'filter_index' => 'product_type',
                'escape' => false,
                'frame_callback' => array($this, 'callbackColumnProductType'),
                'filter_condition_callback' => array($this, 'callbackFilterProductType'),
            )
        );

        $this->addColumn(
            'status', array(
                'header' => Mage::helper('M2ePro')->__('Product Data'),
                'sortable' => false,
                'align' => 'center',
                'index' => 'validation_status',
                'filter_index' => 'validation_status',
                'type' => 'options',
                'options' => array(
                    Ess_M2ePro_Model_Amazon_ProductType_Validation::STATUS_INVALID => Mage::helper('M2ePro')->__('Incomplete'),
                    Ess_M2ePro_Model_Amazon_ProductType_Validation::STATUS_VALID => Mage::helper('M2ePro')->__('Complete')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus'),
                'filter_condition_callback' => array($this, 'callbackFilterStatus'),
            )
        );

        $this->addColumn(
            'errors', array(
                'header' => Mage::helper('M2ePro')->__('Error'),
                'width' => '200px',
                'index' => 'validation_error_messages',
                'filter_index' => 'validation_error_messages',
                'sortable' => false,
                'frame_callback' => array($this, 'callbackColumnErrors'),
                'filter_condition_callback' => array($this, 'callbackFilterColumnErrors'),
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->addItem('validateProductType',
            array(
                'label' => Mage::helper('M2ePro')->__('Validate Product Data'),
                'url' => '',
            )
           );

        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        if ($productTitle === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $value = sprintf(
            '<span>%s</span>',
            Mage::helper('M2ePro')->escapeHtml($productTitle)
        );

        $productSku = $row->getData('sku');
        if ($productSku === null) {
            $productSku =  Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))
                ->getSku();
        }

        $value .= sprintf(
            '<br><strong>%s</strong>: %s',
            Mage::helper('M2ePro')->__('SKU'),
            Mage::helper('M2ePro')->escapeHtml($productSku)
        );

        return $value;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute' => 'sku', 'like' => '%' . $value . '%'),
                array('attribute' => 'name', 'like' => '%' . $value . '%')
            )
        );
    }

    public function callbackColumnProductType($value, $row, $column, $isExport)
    {
        if ($value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $productTypeId = (int)$row->getData('product_type_id');

        return sprintf(
            '<a target="_blank" href="%s">%s</a>',
            $this->getUrl(
                '*/adminhtml_amazon_productTypes/edit', array(
                'id' =>$productTypeId,
                'close_on_save' => true
              )
            ),
            Mage::helper('M2ePro')->escapeHtml($value)
        );
    }

    protected function callbackFilterProductType($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        $collection->getSelect()->where('pt.title LIKE ?', "%$value%");
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $status = $row->getData('validation_status');
        if ($status === null) {
            return '';
        }

        if ((int)$status === Ess_M2ePro_Model_Amazon_ProductType_Validation::STATUS_VALID) {
            return sprintf(
                '<span style="color: green">%s</span>',
                Mage::helper('M2ePro')->__('Complete')
            );
        }

        return sprintf(
            '<span style="color: red">%s</span>',
            Mage::helper('M2ePro')->__('Incomplete')
        );
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        $collection->getSelect()->where('vd.status = ?', $value);
    }

    public function callbackColumnErrors($value, $row, $column, $isExport)
    {
        $errorMessagesString = $row->getData('validation_error_messages') ?$row->getData('validation_error_messages') : '';

        $errorMessages = Mage::helper('M2ePro')->jsonDecode($errorMessagesString );

        if (!$errorMessages) {
            return '';
        }

        $errorList = array();
        foreach ($errorMessages as $message) {
            $errorList[] = sprintf('<li>%s</li>', $message);
        }

        return sprintf(
            '<div class="product-type-validation-grid-error-message-block"><ul>%s</ul></div>',
            implode('', $errorList)
        );
    }

    public function callbackFilterColumnErrors($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        $collection->getSelect()->where('vd.error_messages LIKE ?', "%$value%");
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _toHtml()
    {
        $helper = Mage::helper('M2ePro');

        $translations = $helper->jsonEncode(
            array(
                'progress_bar_title' => $helper->escapeJs(
                    $helper->__(
                        'Product Data Validation'
                    )
                ),
                'progress_bar_status' => $helper->escapeJs(
                    $helper->__(
                        'Validation in progress...'
                    )
                ),
            )
        );

        $url = $helper->jsonEncode(
            array(
                'product_type_validation_url' =>  Mage::helper('adminhtml')
                                                    ->getUrl('M2ePro/adminhtml_amazon_productTypes_validation/validate')
            )
        );

        $gridSelector = $helper->jsonEncode($this->getId());
        $progressBarSelector = $helper->jsonEncode('product_type_validation_progress_bar');

        $javascript = <<<HTML
<script type="text/javascript">
    if (typeof M2ePro == 'undefined') {
        M2ePro = {};
        M2ePro.url = {};
        M2ePro.formData = {};
        M2ePro.customData = {};
        M2ePro.text = {};
    }

    M2ePro.translator.add({$translations});
    M2ePro.url.add({$url});
    
    var objectName = 'ProductTypeValidatorGridObj';
    var validatorGridObject;
    if (typeof window[objectName] === "undefined") {
        validatorGridObject = new ProductTypeValidatorGrid($gridSelector, $progressBarSelector);
        window['productTypeValidatorObjectName'] = objectName;
        window[objectName] = validatorGridObject
        Event.observe(window, 'load', function() {
        setTimeout(function() {
            validatorGridObject.afterInitPage();
            validatorGridObject.validateAll();
        }, 350);
    });
        
    } else {
        validatorGridObject = window[objectName];
        validatorGridObject.afterInitPage();
        
        Event.observe(window, 'load', function() {
        setTimeout(function() {
            validatorGridObject.afterInitPage();
        }, 350);
    });
   }
</script>
HTML;

        return $javascript . parent::_toHtml();
    }

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    protected function getListingProductIds()
    {
        try {
            return $this->getListing()->getSetting(
                'additional_data',
                'adding_listing_products_ids'
            );
        } catch (Exception $e) {
            return $this->getData('listingProductIds');
        }
    }
}
