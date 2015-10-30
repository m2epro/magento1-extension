<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings_Grid_Abstract
{
    /** @var Mage_Eav_Model_Entity_Attribute_Abstract */
    private $motorsAttribute = NULL;

    private $productsMotorsData = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingViewSettingsGrid'.$this->getListing()->getId());
        // ---------------------------------------

        if ($this->isMotorsAvailable()) {
            $attributeCode = Mage::helper('M2ePro/Component_Ebay_Motors')
                ->getAttribute($this->getMotorsType());

            $this->motorsAttribute = Mage::getModel('catalog/product')->getResource()
                                                                               ->getAttribute($attributeCode);
        }
    }

    //########################################

    public function getMotorsType()
    {
        if (!$this->isMotorsAvailable()) {
            return null;
        }

        if ($this->isMotorEpidsAvailable()) {
            return Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID;
        }

        return Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_KTYPE;
    }

    //########################################

    public function getMainButtonsHtml()
    {
        $data = array(
            'current_view_mode' => $this->getParentBlock()->getViewMode()
        );
        $viewModeSwitcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_view_modeSwitcher');
        $viewModeSwitcherBlock->addData($data);

        return $viewModeSwitcherBlock->toHtml() . parent::getMainButtonsHtml();
    }

    //########################################

    public function getAdvancedFilterButtonHtml()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            return '';
        }

        return parent::getAdvancedFilterButtonHtml();
    }

    //########################################

    protected function isShowRuleBlock()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return false;
        }

        return parent::isShowRuleBlock();
    }

    //########################################

    protected function getGridHandlerJs()
    {
        return 'EbayListingSettingsGridHandler';
    }

    //########################################

    protected function _prepareCollection()
    {
        // ---------------------------------------
        // Get collection
        // ---------------------------------------
        /* @var $collection Ess_M2ePro_Model_Mysql4_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance('Ess_M2ePro_Model_Mysql4_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource());
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');
        // ---------------------------------------

        // Join listing product tables
        // ---------------------------------------
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
                'min_online_price'      => 'IF(
                    (`t`.`variation_min_price` IS NULL),
                    `elp`.`online_current_price`,
                    `t`.`variation_min_price`
                )',
                'max_online_price'      => 'IF(
                    (`t`.`variation_max_price` IS NULL),
                    `elp`.`online_current_price`,
                    `t`.`variation_max_price`
                )'
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

        if ($this->motorsAttribute) {
            $collection->addAttributeToSelect($this->motorsAttribute->getAttributeCode());

            $collection->joinTable(
                array('eea' => Mage::getSingleton('core/resource')->getTableName('eav_entity_attribute')),
                'attribute_set_id=attribute_set_id',
                array(
                    'is_motors_attribute_in_product_attribute_set' => 'entity_attribute_id',
                ),
                '{{table}}.attribute_id = ' . $this->motorsAttribute->getAttributeId(),
                'left'
            );
        }

        $collection->getSelect()->joinLeft(
            new Zend_Db_Expr('(
                SELECT
                    `mlpv`.`listing_product_id`,
                    MIN(`melpv`.`online_price`) as variation_min_price,
                    MAX(`melpv`.`online_price`) as variation_max_price
                FROM `'. Mage::getResourceModel('M2ePro/Listing_Product_Variation')->getMainTable() .'` AS `mlpv`
                INNER JOIN `' .
                Mage::getResourceModel('M2ePro/Ebay_Listing_Product_Variation')->getMainTable() .
                '` AS `melpv`
                    ON (`mlpv`.`id` = `melpv`.`listing_product_variation_id`)
                WHERE `melpv`.`status` != ' . Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED . '
                GROUP BY `mlpv`.`listing_product_id`
            )'),
            'elp.listing_product_id=t.listing_product_id',
            array(
                'variation_min_price' => 'variation_min_price',
                'variation_max_price' => 'variation_max_price',
            )
        );

        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        parent::_prepareCollection();

        if ($this->isMotorsAvailable()) {
            $this->prepareExistingMotorsData();
        }

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumns();

        $this->addColumnAfter('name', array(
            'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU / eBay Category'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter'    => 'M2ePro/adminhtml_ebay_listing_view_settings_grid_column_filter_titleSkuCategory',
            'frame_callback' => array($this, 'callbackColumnTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ), 'product_id');

        if ($this->isMotorsAvailable() && $this->motorsAttribute) {
            $this->addColumnAfter('parts_motors_attribute_value', array(
                'header'    => Mage::helper('M2ePro')->__('Compatibility'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'options',
                'index'     => $this->motorsAttribute->getAttributeCode(),
                'sortable'  => false,
                'options'   => array(
                    1 => Mage::helper('M2ePro')->__('Filled'),
                    0 => Mage::helper('M2ePro')->__('Empty')
                ),
                'frame_callback' => array($this, 'callbackColumnMotorsAttribute'),
                'filter_condition_callback' => array($this, 'callbackFilterMotorsAttribute'),
            ), 'name');
        }

        return parent::_prepareColumns();
    }

    //########################################

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

        if ($this->isMotorsAvailable() && $this->motorsAttribute) {
            $this->getMassactionBlock()->addItem('editMotors', array(
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

    //########################################

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

    public function callbackColumnMotorsAttribute($value, $row, $column, $isExport)
    {
        if (!$this->motorsAttribute) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_motors_attribute_in_product_attribute_set')) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $attributeCode = $this->motorsAttribute->getAttributeCode();
        $attributeValue = $row->getData($attributeCode);

        if (empty($attributeValue)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $motorsData = $this->productsMotorsData[$row->getData('listing_product_id')];

        $countOfItems = count($motorsData['items']);
        $countOfFilters = count($motorsData['filters']);
        $countOfGroups = count($motorsData['groups']);

        $showAll = false;

        if ($countOfItems + $countOfFilters + $countOfGroups === 0) {
            $showAll = true;
        }

        if ($this->getMotorsType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            $motorsTypeTitle = 'ePIDs';
        } else {
            $motorsTypeTitle = 'kTypes';
        }

        $html = '<div style="padding: 4px; color: #666666">';
        $label = Mage::helper('M2ePro')->__('Show');
        $labelFilters = Mage::helper('M2ePro')->__('Filters');
        $labelGroups = Mage::helper('M2ePro')->__('Groups');

        if ($showAll || $countOfItems > 0) {
            $html .= <<<HTML
<span style="text-decoration: underline; font-weight: bold">{$motorsTypeTitle}</span>:
<span>{$countOfItems}</span><br/>
HTML;

            if ($countOfItems) {
                $html .= <<<HTML
[<a href="javascript:void(0);"
    onclick="EbayMotorsHandlerObj.openViewItemPopup(
        {$row->getData('id')},
        EbayListingSettingsGridHandlerObj
    );">{$label}</a>]<br/>
HTML;
            }
        }

        if ($showAll || $countOfFilters > 0) {
            $html .= <<<HTML
<span style="text-decoration: underline; font-weight: bold">{$labelFilters}</span>:
<span>{$countOfFilters}</span><br/>
HTML;

            if ($countOfFilters) {
                $html .= <<<HTML
[<a href="javascript:void(0);"
    onclick="EbayMotorsHandlerObj.openViewFilterPopup(
        {$row->getData('id')},
        EbayListingSettingsGridHandlerObj
    );">{$label}</a>]<br/>
HTML;
            }
        }

        if ($showAll || $countOfGroups > 0) {
            $html .= <<<HTML
<span style="text-decoration: underline; font-weight: bold">{$labelGroups}</span>:
<span>{$countOfGroups}</span><br/>
HTML;

            if ($countOfGroups) {
                $html .= <<<HTML
[<a href="javascript:void(0);"
    onclick="EbayMotorsHandlerObj.openViewGroupPopup(
        {$row->getData('id')},
        EbayListingSettingsGridHandlerObj
    );">{$label}</a>]
HTML;
            }
        }

        $html .= '</div>';

        return $html;
    }

    //########################################

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

    public function callbackFilterMotorsAttribute(Varien_Data_Collection_Db $collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (is_null($value)) {
            return;
        }

        if (!$this->motorsAttribute) {
            return;
        }

        if ($value == 1) {
            $attributeCode = $this->motorsAttribute->getAttributeCode();

            $collection->addFieldToFilter($attributeCode,array('notnull'=>true));
            $collection->addFieldToFilter($attributeCode,array('neq'=>''));
            $collection->addFieldToFilter(
                'is_motors_attribute_in_product_attribute_set',array('notnull'=>true)
            );
        } else {
            $attributeId = $this->motorsAttribute->getId();
            $storeId = $this->getListing()->getStoreId();

            $joinCondition = 'eaa.entity_id = e.entity_id and eaa.attribute_id = '.$attributeId;
            if (!$this->motorsAttribute->isScopeGlobal()) {
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

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing/viewGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

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

    //########################################

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

        if ($this->isMotorsAvailable() && $this->motorsAttribute) {
            $actions['addCompatibleVehicles'] =  array(
                'caption' => $helper->__('Add Compatible Vehicles'),
                'group'   => 'other',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editMotorsAction\']'
            );
        }

        return array_merge(parent::getColumnActionsItems(), $actions);
    }

    //########################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        // ---------------------------------------
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

        $urls = array_merge(
            $urls,
            $helper->getControllerActions('adminhtml_ebay_motor')
        );

        $urls = json_encode($urls);
        // ---------------------------------------

        if ($this->getMotorsType() == Ess_M2ePro_Helper_Component_Ebay_Motors::TYPE_EPID) {
            $motorsTypeTitle = 'ePID';
        } else {
            $motorsTypeTitle = 'kType';
        }

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
                Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(NULL, NULL,
                    'x/BQAJAQ#SellonanothereBaySite-Account')
                ),
            'Amount to Pay.' => $helper->__('Amount to Pay'),
            'Insert amount to be credited to an Account' => $helper->__('Insert amount to be credited to an Account.'),
            'Confirm' => $helper->__('Confirm'),
            'Add Compatible Vehicles' => $helper->__('Add Compatible Vehicles'),
            'Save Filter' => $helper->__('Save Filter'),
            'Save as Group' => $helper->__('Save as Group'),
            'Set Note' => $helper->__('Set Note'),
            'View Items' => $helper->__('Selected %items_title%s', $motorsTypeTitle),
            'View Filters' => $helper->__('Selected Filters'),
            'View Groups' => $helper->__('Selected Groups'),
            'Selected Items' => $helper->__('Selected %items_title%s',$motorsTypeTitle),
            'Selected Filters' => $helper->__('Selected Filters'),
            'Selected Groups' => $helper->__('Selected Groups'),
            'Motor Item' => $motorsTypeTitle,
            'Note' => $helper->__('Note'),
            'Filter' => $helper->__('Filter'),
            'Group' => $helper->__('Group'),
            'kType' => $helper->__('kType'),
            'ePID' => $helper->__('ePID'),
            'Type' => $helper->__('Type'),
            'Year From' => $helper->__('Year From'),
            'Year To' => $helper->__('Year To'),
            'Body Style' => $helper->__('Body Style')
        ));
        // ---------------------------------------

        // ---------------------------------------
        $constants = $helper->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');
        // ---------------------------------------

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

        $motorsType = '';
        if ($this->isMotorsAvailable()) {
            $motorsType = $this->getMotorsType();
        }

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

        EbayMotorsHandlerObj = new EbayMotorsHandler({$this->getListing()->getId()}, '{$motorsType}');
    });

</script>
HTML;

        // ---------------------------------------
        if ($this->getRequest()->getParam('auto_actions')) {
            $html .= <<<HTML
<script type="text/javascript">
    Event.observe(window, 'load', function() {
        ListingAutoActionHandlerObj.loadAutoActionHtml();
    });
</script>
HTML;
        }
        // ---------------------------------------

        return parent::_toHtml() . $html;
    }

    //########################################

    private function isMotorsAvailable()
    {
        return $this->isMotorEpidsAvailable() || $this->isMotorKtypesAvailable();
    }

    private function isMotorEpidsAvailable()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors')->isMarketplaceSupportsEpid(
            $this->getListing()->getMarketplaceId()
        );
    }

    private function isMotorKtypesAvailable()
    {
        return Mage::helper('M2ePro/Component_Ebay_Motors')->isMarketplaceSupportsKtype(
            $this->getListing()->getMarketplaceId()
        );
    }

    //########################################

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

    //########################################

    private function prepareExistingMotorsData()
    {
        $motorsHelper = Mage::helper('M2ePro/Component_Ebay_Motors');

        $products = $this->getCollection()->getItems();

        $productsMotorsData = array();

        $items = array();
        $filters = array();
        $groups = array();

        foreach ($products as $product) {
            if (!$product->getData('is_motors_attribute_in_product_attribute_set')) {
                continue;
            }

            $productId = $product->getData('listing_product_id');

            $attributeCode = $this->motorsAttribute->getAttributeCode();
            $attributeValue = $product->getData($attributeCode);

            $productsMotorsData[$productId] = $motorsHelper->parseAttributeValue($attributeValue);

            $items = array_merge($items, array_keys($productsMotorsData[$productId]['items']));
            $filters = array_merge($filters, $productsMotorsData[$productId]['filters']);
            $groups = array_merge($groups, $productsMotorsData[$productId]['groups']);
        }

        //-------------------------------
        $typeIdentifier = $motorsHelper->getIdentifierKey($this->getMotorsType());

        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                $motorsHelper->getDictionaryTable($this->getMotorsType()),
                array($typeIdentifier)
            )
            ->where('`'.$typeIdentifier.'` IN (?)', $items);

        $existedItems = $select->query()->fetchAll(PDO::FETCH_COLUMN);
        //-------------------------------

        //-------------------------------
        $filtersTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_motor_filter');
        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                $filtersTable,
                array('id')
            )
            ->where('`id` IN (?)', $filters);

        $existedFilters = $select->query()->fetchAll(PDO::FETCH_COLUMN);
        //-------------------------------

        //-------------------------------
        $groupsTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_motor_group');
        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                $groupsTable,
                array('id')
            )
            ->where('`id` IN (?)', $groups);

        $existedGroups = $select->query()->fetchAll(PDO::FETCH_COLUMN);
        //-------------------------------

        foreach ($productsMotorsData as $productId => $productMotorsData) {

            foreach ($productMotorsData['items'] as $item => $itemData) {
                if (!in_array($item, $existedItems)) {
                    unset($productsMotorsData[$productId]['items'][$item]);
                }
            }

            foreach ($productMotorsData['filters'] as $key => $filterId) {
                if (!in_array($filterId, $existedFilters)) {
                    unset($productsMotorsData[$productId]['filters'][$key]);
                }
            }

            foreach ($productMotorsData['groups'] as $key => $groupId) {
                if (!in_array($groupId, $existedGroups)) {
                    unset($productsMotorsData[$productId]['groups'][$key]);
                }
            }
        }

        $this->productsMotorsData = $productsMotorsData;

        return $this;
    }

    //########################################
}