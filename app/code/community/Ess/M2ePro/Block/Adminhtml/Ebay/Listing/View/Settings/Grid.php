<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings_Grid_Abstract
{
    /** @var Mage_Eav_Model_Entity_Attribute_Abstract */
    private $partsCompatibilityAttribute = NULL;

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewSettingsGrid'.$this->getListing()->getId());
        //------------------------------

        if ($this->isPartsCompatibilityAvailable()) {
            $attributeCode = Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')
                ->getAttribute($this->getCompatibilityType());

            $this->partsCompatibilityAttribute = Mage::getModel('catalog/product')->getResource()
                                                                               ->getAttribute($attributeCode);
        }
    }

    // ####################################

    public function getCompatibilityType()
    {
        if (!$this->isPartsCompatibilityAvailable()) {
            return null;
        }

        if ($this->isMotorSpecificsAvailable()) {
            return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC;
        }

        return Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE;
    }

    // ####################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    // ####################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    // ####################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    // ####################################

    protected function getGridHandlerJs()
    {
        return 'EbayListingSettingsGridHandler';
    }

    // ####################################

    protected function _prepareCollection()
    {
        //--------------------------------
        // Get collection
        //----------------------------
        /* @var $collection Ess_M2ePro_Model_Mysql4_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource());
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        //--------------------------------

        // Join listing product tables
        //----------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'ebay_status' => 'status',
            ),
            '{{table}}.listing_id='.(int)$this->getListing()->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id',

                'template_category_id'  => 'template_category_id',
                'template_other_category_id'  => 'template_other_category_id',

                'template_payment_mode'  => 'template_payment_mode',
                'template_shipping_mode' => 'template_shipping_mode',
                'template_return_mode'   => 'template_return_mode',

                'template_description_mode'     => 'template_description_mode',
                'template_selling_format_mode'  => 'template_selling_format_mode',
                'template_synchronization_mode' => 'template_synchronization_mode',

                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(online_qty - online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_category'       => 'online_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_start_price'    => 'online_start_price',
                'online_current_price'  => 'online_current_price',
                'online_reserve_price'  => 'online_reserve_price',
                'online_buyitnow_price' => 'online_buyitnow_price',
                'min_online_price'      => 'online_current_price'
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            NULL,
            'left'
        );
        $collection->joinTable(
            array('etc' => 'M2ePro/Ebay_Template_Category'),
            'id=template_category_id',
            array(
                'category_main_mode'      => 'category_main_mode',
                'category_main_id'        => 'category_main_id',
                'category_main_path'      => 'category_main_path',
                'category_main_attribute' => 'category_main_attribute',
            ),
            NULL,
            'left'
        );
        $collection->joinTable(
            array('etoc' => 'M2ePro/Ebay_Template_OtherCategory'),
            'id=template_other_category_id',
            array(
                'category_secondary_mode'      => 'category_secondary_mode',
                'category_secondary_id'        => 'category_secondary_id',
                'category_secondary_path'      => 'category_secondary_path',
                'category_secondary_attribute' => 'category_secondary_attribute',

                'store_category_main_mode'      => 'store_category_main_mode',
                'store_category_main_id'        => 'store_category_main_id',
                'store_category_main_path'      => 'store_category_main_path',
                'store_category_main_attribute' => 'store_category_main_attribute',

                'store_category_secondary_mode'      => 'store_category_secondary_mode',
                'store_category_secondary_id'        => 'store_category_secondary_id',
                'store_category_secondary_path'      => 'store_category_secondary_path',
                'store_category_secondary_attribute' => 'store_category_secondary_attribute',
            ),
            NULL,
            'left'
        );

        if ($this->partsCompatibilityAttribute) {
            $collection->addAttributeToSelect($this->partsCompatibilityAttribute->getAttributeCode());

            $collection->joinTable(
                array('eea' => Mage::getSingleton('core/resource')->getTableName('eav_entity_attribute')),
                'attribute_set_id=attribute_set_id',
                array(
                    'is_parts_compatibility_attribute_in_product_attribute_set' => 'entity_attribute_id',
                ),
                '{{table}}.attribute_id = ' . $this->partsCompatibilityAttribute->getAttributeId(),
                'left'
            );
        }
        //----------------------------
//        exit($collection->getSelect()->__toString());

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumns();

        $this->addColumnAfter('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU / eBay Category'),
            'align'     => 'left',
            //'width'     => '300px',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => 'M2ePro/adminhtml_ebay_listing_view_settings_grid_column_filter_titleSkuCategory',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ), 'product_id');

        if ($this->isPartsCompatibilityAvailable() && $this->partsCompatibilityAttribute) {
            $this->addColumnAfter('parts_compatibility_attribute_value', array(
                'header'    => Mage::helper('M2ePro')->__('Compatibility'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'options',
                'index'     => $this->partsCompatibilityAttribute->getAttributeCode(),
                'sortable'  => false,
                'options'   => array(
                    1 => Mage::helper('M2ePro')->__('Filled'),
                    0 => Mage::helper('M2ePro')->__('Empty')
                ),
                'frame_callback' => array($this, 'callbackColumnPartsCompatibilityAttribute'),
                'filter_condition_callback' => array($this, 'callbackFilterPartsCompatibilityAttribute'),
            ), 'name');
        }

        return parent::_prepareColumns();
    }

    // ####################################

    protected function _prepareMassactionGroup()
    {
        $this->getMassactionBlock()->setGroups(array(
            'edit_settings'            => Mage::helper('M2ePro')->__('Edit General Settings'),
            'edit_categories_settings' => Mage::helper('M2ePro')->__('Edit Categories Settings'),
            'other'                    => Mage::helper('M2ePro')->__('Other')
        ));

        return $this;
    }

    protected function _prepareMassactionItems()
    {
        $this->getMassactionBlock()->addItem('editCategorySettings', array(
            'label'    => Mage::helper('M2ePro')->__('All Categories'),
            'url'      => '',
        ), 'edit_categories_settings');

        $this->getMassactionBlock()->addItem('editPrimaryCategorySettings', array(
                'label'    => Mage::helper('M2ePro')->__('Primary Categories'),
                'url'      => '',
            ), 'edit_categories_settings');

        if ($this->getListing()->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $this->getMassactionBlock()->addItem('editStorePrimaryCategorySettings', array(
                'label'    => Mage::helper('M2ePro')->__('Store Primary Categories'),
                'url'      => '',
            ), 'edit_categories_settings');
        }

        parent::_prepareMassactionItems();

        if ($this->isPartsCompatibilityAvailable() && $this->partsCompatibilityAttribute) {
            $this->getMassactionBlock()->addItem('editPartsCompatibility', array(
                'label' => Mage::helper('M2ePro')->__('Add Compatible Vehicles'),
                'url'   => ''
            ), 'other');
        }

        $this->getMassactionBlock()->addItem('moving', array(
            'label'    => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
        ), 'other');

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $this->getMassactionBlock()->addItem('transferring', array(
                'label'    => Mage::helper('M2ePro')->__('Sell on Another eBay Site'),
                'url'      => '',
            ), 'other');
        }

        return $this;
    }

    // ####################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        $value = parent::callbackColumnTitle($value, $row, $column, $isExport);

        $value .= '<br/><br/>';

        if ($row->getData('category_main_mode') == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            $value .= $this->getCategoryInfoHtml(
                $helper->__('eBay Primary Category'),
                '<span style="color: red">'.$helper->__('Not Set').'</span>'
            );
        } else {
            $value .= $this->getEbayCategoryInfoHtml($row,'category_main',$helper->__('eBay Primary Category'));
        }

        $value .= $this->getEbayCategoryInfoHtml($row,'category_secondary',$helper->__('eBay Secondary Category'));

        $value .= $this->getStoreCategoryInfoHtml($row,'category_main',
                                                  $helper->__('eBay Store Primary Category'));
        $value .= $this->getStoreCategoryInfoHtml($row,'category_secondary',
                                                  $helper->__('eBay Store Secondary Category'));
        $value .= '<br/>';

        return $value;
    }

    public function callbackColumnPartsCompatibilityAttribute($value, $row, $column, $isExport)
    {
        if (!$this->partsCompatibilityAttribute) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_parts_compatibility_attribute_in_product_attribute_set')) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $attributeCode = $this->partsCompatibilityAttribute->getAttributeCode();
        $attributeValue = $row->getData($attributeCode);

        $parsedValue = Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->parseAttributeValue($attributeValue);
        $countOfValues = count($parsedValue);

        if ($this->getCompatibilityType() == Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC) {
            $partsCompatibilityTypeTitle = 'ePIDs';
        } else {
            $partsCompatibilityTypeTitle = 'kTypes';
        }

        $html = <<<HTML
        <div style="padding: 4px; color: #666666">
        <span style="text-decoration: underline; font-weight: bold">{$partsCompatibilityTypeTitle}</span>&nbsp;
        <span>{$countOfValues}</span><br/>
HTML;
        if ($countOfValues) {

            $label = Mage::helper('M2ePro')->__('Show');

            $html .= <<<HTML
        [<a href="javascript:void(0);"
            onclick="EbayListingSettingsGridHandlerObj.showCompatibilityDetails(
                {$row->getData('id')}, '{$this->getCompatibilityType()}'
            );">{$label}</a>]
HTML;
        }
        return $html.'</div>';
    }

    // ####################################

    public function callbackFilterTitle($collection, $column)
    {
        if (!is_null($inputValue = $column->getFilter()->getValue('input'))) {

            $fieldsToFilter = array(
                array('attribute'=>'sku','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'name','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'category_main_path','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'category_secondary_path','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'store_category_main_path','like'=>'%'.$inputValue.'%'),
                array('attribute'=>'store_category_secondary_path','like'=>'%'.$inputValue.'%'),
            );

            if (is_numeric($inputValue)) {
                $fieldsToFilter[] = array('attribute'=>'category_main_id','eq'=>$inputValue);
                $fieldsToFilter[] = array('attribute'=>'category_secondary_id','eq'=>$inputValue);
                $fieldsToFilter[] = array('attribute'=>'store_category_main_id','eq'=>$inputValue);
                $fieldsToFilter[] = array('attribute'=>'store_category_secondary_id','eq'=>$inputValue);
            }

            $collection->addFieldToFilter($fieldsToFilter);
        }

        if (!is_null($selectValue = $column->getFilter()->getValue('select'))) {
            $collection->addFieldToFilter('template_category_id',array(($selectValue ? 'notnull' : 'null') => true));
        }
    }

    public function callbackFilterPartsCompatibilityAttribute(Varien_Data_Collection_Db $collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (is_null($value)) {
            return;
        }

        if (!$this->partsCompatibilityAttribute) {
            return;
        }

        if ($value == 1) {
            $attributeCode = $this->partsCompatibilityAttribute->getAttributeCode();

            $collection->addFieldToFilter($attributeCode,array('notnull'=>true));
            $collection->addFieldToFilter($attributeCode,array('neq'=>''));
            $collection->addFieldToFilter(
                'is_parts_compatibility_attribute_in_product_attribute_set',array('notnull'=>true)
            );
        } else {
            $attributeId = $this->partsCompatibilityAttribute->getId();
            $storeId = $this->getListing()->getStoreId();

            $joinCondition = 'eaa.entity_id = e.entity_id and eaa.attribute_id = '.$attributeId;
            if (!$this->partsCompatibilityAttribute->isScopeGlobal()) {
                $joinCondition .= ' and eaa.store_id = '.$storeId;
            }

            $collection->getSelect()->joinLeft(
                array('eaa' => Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_text')),
                $joinCondition,
                array('value')
            );

            $collection->getSelect()->orWhere('eaa.value IS NULL');
            $collection->getSelect()->orWhere('eaa.value = \'\'');
            $collection->getSelect()->orWhere('eea.entity_attribute_id IS NULL');
        }
    }

    // ####################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    // ####################################

    private function getEbayCategoryInfoHtml($row, $modeNick, $modeTitle)
    {
        $helper = Mage::helper('M2ePro');
        $mode = $row->getData($modeNick.'_mode');

        if (is_null($mode) || $mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return '';
        }

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {

            $category = $helper->__('Magento Attribute'). ' > ';
            $category.= $helper->escapeHtml(
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $row->getData($modeNick.'_attribute'),
                    $this->getListing()->getStoreId()
                )
            );

        } else {
            $category = $helper->escapeHtml($row->getData($modeNick.'_path')).' ('.$row->getData($modeNick.'_id').')';
        }

        return $this->getCategoryInfoHtml($modeTitle, $category);
    }

    private function getStoreCategoryInfoHtml($row, $modeNick, $modeTitle)
    {
        $helper = Mage::helper('M2ePro');
        $mode = $row->getData('store_'.$modeNick.'_mode');

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            return '';
        }

        if ($mode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {

            $category = $helper->__('Magento Attribute'). ' > ';
            $category .= $helper->escapeHtml(
                Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $row->getData('store_'.$modeNick.'_attribute'),
                    $this->getListing()->getStoreId()
                )
            );

        } else {
            $category = $helper->escapeHtml($row->getData('store_'.$modeNick.'_path')).
                        ' ('.$row->getData('store_'.$modeNick.'_id').')';
        }

        return $this->getCategoryInfoHtml($modeTitle, $category);
    }

    private function getCategoryInfoHtml($modeTitle, $category)
    {
        return <<<HTML
    <div>
        <span style="text-decoration: underline">{$modeTitle}:</span>
        <p style="padding: 2px 0 0 10px">{$category}</p>
    </div>
HTML;
    }

    // ####################################

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'editCategories' => array(
                'caption' => $helper->__('All Categories'),
                'group'   => 'edit_categories_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editCategorySettingsAction\']'
            ),

            'editPrimaryCategories' => array(
                'caption' => $helper->__('Primary Category'),
                'group'   => 'edit_categories_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editPrimaryCategorySettingsAction\']'
            ),
        );

        if ($this->getListing()->getAccount()->getChildObject()->getEbayStoreCategories()) {
            $actions['editStorePrimaryCategories'] =  array(
                'caption' => $helper->__('Store Primary Category'),
                'group'   => 'edit_categories_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.'
                                    .'actions[\'editStorePrimaryCategorySettingsAction\']'
            );
        }

        if ($this->isPartsCompatibilityAvailable() && $this->partsCompatibilityAttribute) {
            $actions['addCompatibleVehicles'] =  array(
                'caption' => $helper->__('Add Compatible Vehicles'),
                'group'   => 'other',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editPartsCompatibilityAction\']'
            );
        }

        return array_merge(parent::getColumnActionsItems(), $actions);
    }

    // ####################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        //------------------------------
        $urls = $helper->getControllerActions('adminhtml_ebay_listing',array('_current' => true));

        $path = 'adminhtml_ebay_listing/getCategoryChooserHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_listing/getCategorySpecificHtml';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_listing/saveCategoryTemplate';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_listing/runTransferring';
        $urls[$path] = $this->getUrl('*/' . $path, array(
            'listing_id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_log/listing';
        $urls[$path] = $this->getUrl('*/adminhtml_ebay_log/listing', array(
            'id' => $this->getListing()->getId()
        ));

        $path = 'adminhtml_ebay_listing/getEstimatedFees';
        $urls[$path] = $this->getUrl('*/' . $path, array(
                'listing_id' => $this->getListing()->getId()
            ));

        $urls['adminhtml_ebay_listing/getTransferringUrl'] = $this->getUrl('*/adminhtml_ebay_listing/view');

        $urls = json_encode($urls);
        //------------------------------

        //------------------------------
        $translations = json_encode(array(
            'eBay Categories' => $helper->__('eBay Categories'),
            'of Product' => Mage::helper('M2ePro')->__('of Product'),
            'Specifics' => $helper->__('Specifics'),
            'Compatibility Attribute ePIDs' => $helper->__('Compatibility Attribute ePIDs'),
            'Payment for Translation Service' => $helper->__('Payment for Translation Service'),
            'Payment for Translation Service. Help' => $helper->__('Payment for Translation Service'),
            'Specify a sum to be credited to an Account.' =>
                $helper->__('Specify a sum to be credited to an Account.'
                           .' If you are planning to order more Items for Translation in future,'
                           .' you can credit the sum greater than the one needed for current Translation.'
                           .' Click <a href="%url%" target="_blank">here</a> to find out more.',
                Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Ebay::NICK,
                    'Sell+on+another+eBay+Site#SellonanothereBaySite-Account')
                ),
            'Amount to Pay.' => $helper->__('Amount to Pay'),
            'Insert amount to be credited to an Account' => $helper->__('Insert amount to be credited to an Account.'),
            'Confirm' => $helper->__('Confirm'),
        ));
        //------------------------------

        //------------------------------
        $constants = $helper->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');
        //------------------------------

        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list',true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $component . 'ListingViewGrid' . $this->getListing()->getId();
        $ignoreListings = json_encode(array($this->getListing()->getId()));

        $logViewUrl = $this->getUrl('*/adminhtml_ebay_log/listing',array(
            'id'=>$this->getListing()->getId(),
            'back'=>$helper->makeBackUrlParam('*/adminhtml_ebay_listing/view',array('id'=>$this->getListing()->getId()))
        ));
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $taskCompletedMessage = $helper->escapeJs($helper->__('Task completed. Please wait ...'));
        $taskCompletedSuccessMessage = $helper->escapeJs(
            $helper->__('"%task_title%" Task has successfully completed.'));

        // M2ePro_TRANSLATIONS
        // %task_title%" Task has completed with warnings. <a target="_blank" href="%url%">View Log</a> for details.
        $tempString = '"%task_title%" Task has completed with warnings.'
                     .' <a target="_blank" href="%url%">View Log</a> for details.';
        $taskCompletedWarningMessage = $helper->escapeJs($helper->__($tempString));

        // M2ePro_TRANSLATIONS
        // "%task_title%" Task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.
        $tempString = '"%task_title%" Task has completed with errors. '
                     .' <a target="_blank" href="%url%">View Log</a> for details.';
        $taskCompletedErrorMessage = $helper->escapeJs($helper->__($tempString));

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %product_title% Product(s) data on eBay.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs(
            $helper->__('Listing is empty.')
        );

        $selectItemsMessage = $helper->escapeJs($helper->__('Please select Items.'));
        $selectActionMessage = $helper->escapeJs($helper->__('Please select Action.'));

        $successWord = $helper->escapeJs($helper->__('Success'));
        $noticeWord = $helper->escapeJs($helper->__('Notice'));
        $warningWord = $helper->escapeJs($helper->__('Warning'));
        $errorWord = $helper->escapeJs($helper->__('Error'));
        $closeWord = $helper->escapeJs($helper->__('Close'));

        $prepareData = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $getMoveToListingGridHtml = $this->getUrl('*/adminhtml_ebay_listing_settings_moving/moveToListingGrid');
        $getFailedProductsGridHtml = $this->getUrl('*/adminhtml_listing_moving/getFailedProductsGrid');
        $tryToMoveToListing = $this->getUrl('*/adminhtml_listing_moving/tryToMoveToListing');
        $moveToListing = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $successfullyMovedMessage = $helper->escapeJs($helper->__('Product(s) was successfully Moved.'));
        $productsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Product(s) was not Moved. <a target="_blank" href="%url%">View Log</a> for details.')
        );
        $someProductsWereNotMovedMessage = $helper->escapeJs(
            $helper->__('Some Product(s) was not Moved. <a target="_blank" href="%url%">View Log</a> for details.')
        );

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items'));
        $popupTitleSingle = $helper->escapeJs($helper->__('Moving eBay Item'));
        $failedProductsPopupTitle = $helper->escapeJs($helper->__('Product(s) failed to move'));

        $html = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants},'Ess_M2ePro_Helper_Component_Ebay_Category');

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.url.prepareData = '{$prepareData}';
    M2ePro.url.getGridHtml = '{$getMoveToListingGridHtml}';
    M2ePro.url.getFailedProductsGridHtml = '{$getFailedProductsGridHtml}';
    M2ePro.url.tryToMoveToListing = '{$tryToMoveToListing}';
    M2ePro.url.moveToListing = '{$moveToListing}';

    M2ePro.text.popup_title = '{$popupTitle}';
    M2ePro.text.popup_title_single = '{$popupTitleSingle}';
    M2ePro.text.failed_products_popup_title = '{$failedProductsPopupTitle}';

    M2ePro.text.task_completed_message = '{$taskCompletedMessage}';
    M2ePro.text.task_completed_success_message = '{$taskCompletedSuccessMessage}';
    M2ePro.text.task_completed_warning_message = '{$taskCompletedWarningMessage}';
    M2ePro.text.task_completed_error_message = '{$taskCompletedErrorMessage}';

    M2ePro.text.sending_data_message = '{$sendingDataToEbayMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.text.select_items_message = '{$selectItemsMessage}';
    M2ePro.text.select_action_message = '{$selectActionMessage}';

    M2ePro.text.success_word = '{$successWord}';
    M2ePro.text.notice_word = '{$noticeWord}';
    M2ePro.text.warning_word = '{$warningWord}';
    M2ePro.text.error_word = '{$errorWord}';
    M2ePro.text.close_word = '{$closeWord}';

    M2ePro.text.successfully_moved = '{$successfullyMovedMessage}';
    M2ePro.text.products_were_not_moved = '{$productsWereNotMovedMessage}';
    M2ePro.text.some_products_were_not_moved = '{$someProductsWereNotMovedMessage}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {
        EbayListingTransferringHandlerObj = new EbayListingTransferringHandler();
        EbayListingSettingsGridHandlerObj.movingHandler.setOptions(M2ePro);

        EbayListingTransferringPaymentHandlerObj = new EbayListingTransferringPaymentHandler();
    });

</script>
HTML;

        //------------------------------
        if ($this->getRequest()->getParam('auto_actions')) {
            $html .= <<<HTML
<script type="text/javascript">
    Event.observe(window, 'load', function() {
        ListingAutoActionHandlerObj.loadAutoActionHtml();
    });
</script>
HTML;
        }
        //------------------------------

        //------------------------------
        if ($this->isPartsCompatibilityAvailable()) {

            if ($this->isMotorSpecificsAvailable()) {
                $compatibilityType = Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_SPECIFIC;
            } else {
                $compatibilityType = Ess_M2ePro_Helper_Component_Ebay_Motor_Compatibility::TYPE_KTYPE;
            }

            /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Motor_Add $partsCompatibilityBlock */
            $partsCompatibilityBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_add');
            $partsCompatibilityBlock->setCompatibilityType($compatibilityType);
            $partsCompatibilityBlock->setProductGridId($this->getId());

            $html .= $partsCompatibilityBlock->toHtml();
        }
        //------------------------------

        return parent::_toHtml() . $html;
    }

    // ####################################

    private function isPartsCompatibilityAvailable()
    {
        return $this->isMotorSpecificsAvailable() || $this->isMotorKtypesAvailable();
    }

    private function isMotorSpecificsAvailable()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->isMarketplaceSupportsSpecific(
            $this->getListing()->getMarketplaceId()
        );
    }

    private function isMotorKtypesAvailable()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motor_Compatibility')->isMarketplaceSupportsKtype(
            $this->getListing()->getMarketplaceId()
        );
    }

    // ####################################

    /**
     * @inheritdoc
     **/
    protected function getListing()
    {
        if (is_null($this->listing)) {
            $this->listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Listing', $this->getRequest()->getParam('id')
            );
        }

        return $this->listing;
    }

    // ####################################
}