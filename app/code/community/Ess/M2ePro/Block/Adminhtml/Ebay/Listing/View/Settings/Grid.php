<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Helper_Component_Ebay_Category as eBayCategory;
use Ess_M2ePro_Model_Ebay_Template_Category as TemplateCategory;
use Ess_M2ePro_Model_Ebay_Template_Manager as TemplateManager;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Settings_Grid
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    /** @var Ess_M2ePro_Model_Listing */
    protected $_listing;

    /** @var Mage_Eav_Model_Entity_Attribute_Abstract */
    protected $_motorsAttribute;

    protected $_productsMotorsData = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListingViewGrid' . $this->getListing()->getId());

        if ($this->isMotorsAvailable()) {
            $this->_motorsAttribute = Mage::getModel('catalog/product')->getResource()->getAttribute(
                Mage::helper('M2ePro/Component_Ebay_Motors')->getAttribute($this->getMotorsType())
            );
        }

        $this->_showAdvancedFilterProductsOption = false;
    }

    //########################################

    public function getMotorsType()
    {
        if (!$this->isMotorsAvailable()) {
            return null;
        }

        if ($this->isMotorEpidsAvailable()) {
            return Mage::helper('M2ePro/Component_Ebay_Motors')->getEpidsTypeByMarketplace(
                $this->getListing()->getMarketplaceId()
            );
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

    protected function getGridHandlerJs()
    {
        return 'EbayListingSettingsGrid';
    }

    //########################################

    protected function _prepareCollection()
    {
        // ---------------------------------------
        // Get collection
        // ---------------------------------------
        /** @var $collection Ess_M2ePro_Model_Resource_Magento_Product_Collection */
        $collection = Mage::getConfig()->getModelInstance(
            'Ess_M2ePro_Model_Resource_Magento_Product_Collection',
            Mage::getModel('catalog/product')->getResource()
        );

        $collection->setListingProductModeOn();
        $collection->setListing($this->getListing());
        $collection->setStoreId($this->getListing()->getStoreId());

        if ($this->isFilterOrSortByPriceIsUsed(null, 'ebay_online_current_price')) {
            $collection->setIsNeedToUseIndexerParent(true);
        }

        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        // Join listing product tables
        // ---------------------------------------
        $collection->joinTable(
            array('lp' => 'M2ePro/Listing_Product'),
            'product_id=entity_id',
            array(
                'id' => 'id',
                'ebay_status' => 'status',
                'additional_data' => 'additional_data'
            ),
            '{{table}}.listing_id='.(int)$this->getListing()->getId()
        );
        $collection->joinTable(
            array('elp' => 'M2ePro/Ebay_Listing_Product'),
            'listing_product_id=id',
            array(
                'listing_product_id' => 'listing_product_id',

                'template_payment_mode'       => 'template_payment_mode',
                'template_shipping_mode'      => 'template_shipping_mode',
                'template_return_policy_mode' => 'template_return_policy_mode',

                'template_description_mode'     => 'template_description_mode',
                'template_selling_format_mode'  => 'template_selling_format_mode',
                'template_synchronization_mode' => 'template_synchronization_mode',

                'end_date'              => 'end_date',
                'start_date'            => 'start_date',
                'online_title'          => 'online_title',
                'online_sku'            => 'online_sku',
                'available_qty'         => new Zend_Db_Expr('(online_qty - online_qty_sold)'),
                'ebay_item_id'          => 'ebay_item_id',
                'online_main_category'  => 'online_main_category',
                'online_qty_sold'       => 'online_qty_sold',
                'online_start_price'    => 'online_start_price',
                'online_current_price'  => 'online_current_price',
                'online_reserve_price'  => 'online_reserve_price',
                'online_buyitnow_price' => 'online_buyitnow_price',

                'template_category_id'                 => 'template_category_id',
                'template_category_secondary_id'       => 'template_category_secondary_id',
                'template_store_category_id'           => 'template_store_category_id',
                'template_store_category_secondary_id' => 'template_store_category_secondary_id',
            )
        );
        $collection->joinTable(
            array('ei' => 'M2ePro/Ebay_Item'),
            'id=ebay_item_id',
            array(
                'item_id' => 'item_id',
            ),
            null,
            'left'
        );
        $collection->joinTable(
            array('etc1' => 'M2ePro/Ebay_Template_Category'),
            'id=template_category_id',
            array(
                'category_main_mode'               => 'category_mode',
                'category_main_id'                 => 'category_id',
                'category_main_path'               => 'category_path',
                'category_main_attribute'          => 'category_attribute',
                'category_main_is_custom_template' => 'is_custom_template'
            ),
            null,
            'left'
        );
        $collection->joinTable(
            array('etc2' => 'M2ePro/Ebay_Template_Category'),
            'id=template_category_secondary_id',
            array(
                'category_secondary_mode'      => 'category_mode',
                'category_secondary_id'        => 'category_id',
                'category_secondary_path'      => 'category_path',
                'category_secondary_attribute' => 'category_attribute',
            ),
            null,
            'left'
        );
        $collection->joinTable(
            array('etsc1' => 'M2ePro/Ebay_Template_StoreCategory'),
            'id=template_store_category_id',
            array(
                'store_category_main_mode'      => 'category_mode',
                'store_category_main_id'        => 'category_id',
                'store_category_main_path'      => 'category_path',
                'store_category_main_attribute' => 'category_attribute',
            ),
            null,
            'left'
        );
        $collection->joinTable(
            array('etsc2' => 'M2ePro/Ebay_Template_StoreCategory'),
            'id=template_store_category_secondary_id',
            array(
                'store_category_secondary_mode'      => 'category_mode',
                'store_category_secondary_id'        => 'category_id',
                'store_category_secondary_path'      => 'category_path',
                'store_category_secondary_attribute' => 'category_attribute',
            ),
            null,
            'left'
        );

        if ($this->_motorsAttribute) {
            $collection->joinAttribute(
                $this->_motorsAttribute->getAttributeCode(),
                $this->_motorsAttribute,
                'entity_id',
                null,
                'left',
                $this->getListing()->getStoreId()
            );

            $collection->joinTable(
                array(
                    'eea' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('eav_entity_attribute')
                ),
                'attribute_set_id=attribute_set_id',
                array(
                    'is_motors_attribute_in_product_attribute_set' => 'entity_attribute_id',
                ),
                '{{table}}.attribute_id = ' . $this->_motorsAttribute->getAttributeId(),
                'left'
            );
        }

        if ($collection->isNeedUseIndexerParent()) {
            $collection->joinIndexerParent();
        }

        $this->setCollection($collection);
        $result = parent::_prepareCollection();

        if ($this->isMotorsAvailable() && $this->_motorsAttribute) {
            $this->prepareExistingMotorsData();
        }

        return $result;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'   => Mage::helper('M2ePro')->__('Product ID'),
                'align'    => 'right',
                'width'    => '100px',
                'type'     => 'number',
                'index'    => 'entity_id',
                'store_id' => $this->getListing()->getStoreId(),
                'renderer' => 'M2ePro/adminhtml_grid_column_renderer_productId'
            )
        );

        $this->addColumn(
            'name',
            array(
                'header'    => Mage::helper('M2ePro')->__('Product Title / Product SKU / eBay Category'),
                'align'     => 'left',
                'type'      => 'text',
                'index'     => 'name',
                'filter'    => 'M2ePro/adminhtml_ebay_listing_view_settings_grid_column_filter_titleSkuCategory',
                'frame_callback'            => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        if ($this->isMotorsAvailable() && $this->_motorsAttribute) {
            $this->addColumn(
                'parts_motors_attribute_value',
                array(
                    'header'    => Mage::helper('M2ePro')->__('Compatibility'),
                    'align'     => 'left',
                    'width'     => '100px',
                    'type'      => 'options',
                    'index'     => $this->_motorsAttribute->getAttributeCode(),
                    'sortable'  => false,
                    'options'   => array(
                        1 => Mage::helper('M2ePro')->__('Filled'),
                        0 => Mage::helper('M2ePro')->__('Empty')
                    ),
                    'frame_callback' => array($this, 'callbackColumnMotorsAttribute'),
                    'filter_condition_callback' => array($this, 'callbackFilterMotorsAttribute'),
                )
            );
        }

        $this->addColumn(
            'general_settings', array(
                'header'=> Mage::helper('catalog')->__('Payment and Shipping Settings'),
                'width' => '170px',
                'type'  => 'options',
                'sortable'  => false,
                'option_groups' => array(
                    array(
                        'label' => Mage::helper('M2ePro')->__('Payment'),
                        'value' => array(
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_PARENT,
                                        'template' => TemplateManager::TEMPLATE_PAYMENT
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_CUSTOM,
                                        'template' => TemplateManager::TEMPLATE_PAYMENT
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Custom Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_TEMPLATE,
                                        'template' => TemplateManager::TEMPLATE_PAYMENT
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Policies')
                            ),
                        ),
                    ),

                    array(
                        'label' => Mage::helper('M2ePro')->__('Shipping'),
                        'value' => array(
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_PARENT,
                                        'template' => TemplateManager::TEMPLATE_SHIPPING
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_CUSTOM,
                                        'template' => TemplateManager::TEMPLATE_SHIPPING
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Custom Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_TEMPLATE,
                                        'template' => TemplateManager::TEMPLATE_SHIPPING
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Policies')
                            )
                        ),
                    ),

                    array(
                        'label' => Mage::helper('M2ePro')->__('Return'),
                        'value' => array(
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_PARENT,
                                        'template' => TemplateManager::TEMPLATE_RETURN_POLICY
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_CUSTOM,
                                        'template' => TemplateManager::TEMPLATE_RETURN_POLICY
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Custom Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_TEMPLATE,
                                        'template' => TemplateManager::TEMPLATE_RETURN_POLICY
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Policies')
                            )
                        ),
                    )
                ),
                'filter_condition_callback' => array($this, 'callbackFilterSettings'),
                'frame_callback' => array($this, 'callbackColumnGeneralSettings')
            )
        );

        $this->addColumn(
            'selling_settings', array(
                'header'=> Mage::helper('catalog')->__('Selling Settings'),
                'width' => '170px',
                'type'  => 'options',
                'sortable'  => false,
                'option_groups' => array(

                    array(
                        'label' => Mage::helper('M2ePro')->__('Selling'),
                        'value' => array(
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_PARENT,
                                        'template' => TemplateManager::TEMPLATE_SELLING_FORMAT
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_CUSTOM,
                                        'template' => TemplateManager::TEMPLATE_SELLING_FORMAT
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Custom Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_TEMPLATE,
                                        'template' => TemplateManager::TEMPLATE_SELLING_FORMAT
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Policies')
                            ),
                        ),
                    ),

                    array(
                        'label' => Mage::helper('M2ePro')->__('Description'),
                        'value' => array(
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_PARENT,
                                        'template' => TemplateManager::TEMPLATE_DESCRIPTION
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_CUSTOM,
                                        'template' => TemplateManager::TEMPLATE_DESCRIPTION
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Custom Settings')
                            ),
                            array(
                                'value' => Mage::helper('M2ePro')->jsonEncode(
                                    array(
                                        'mode'     => TemplateManager::MODE_TEMPLATE,
                                        'template' => TemplateManager::TEMPLATE_DESCRIPTION
                                    )
                                ),
                                'label' => Mage::helper('M2ePro')->__('Policies')
                            ),
                        ),
                    )
                ),
                'filter_condition_callback' => array($this, 'callbackFilterSettings'),
                'frame_callback' => array($this, 'callbackColumnSellingSettings')
            )
        );

        $templateSynch = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;

        $this->addColumn(
            'synch_settings', array(
                'header'=> Mage::helper('catalog')->__('Synchronization'),
                'width' => '170px',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'mode'     => TemplateManager::MODE_PARENT,
                            'template' => $templateSynch
                        )
                    ) => Mage::helper('M2ePro')->__('Use from Listing Settings'),

                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'mode'     => TemplateManager::MODE_CUSTOM,
                            'template' => $templateSynch
                        )
                    ) => Mage::helper('M2ePro')->__('Custom Settings'),

                    Mage::helper('M2ePro')->jsonEncode(
                        array(
                            'mode'     => TemplateManager::MODE_TEMPLATE,
                            'template' => $templateSynch
                        )
                    ) => Mage::helper('M2ePro')->__('Policies'),
                ),
                'filter_condition_callback' => array($this, 'callbackFilterSettings'),
                'frame_callback' => array($this, 'callbackColumnSynchSettings')
            )
        );

        $this->addColumn(
            'actions', array(
                'header'    => Mage::helper('M2ePro')->__('Actions'),
                'align'     => 'left',
                'width'     => '100px',
                'type'      => 'action',
                'index'     => 'actions',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'M2ePro/adminhtml_grid_column_renderer_action',
                'field' => 'id',
                'group_order' => $this->getGroupOrder(),
                'actions'     => $this->getColumnActionsItems()
            )
        );

        return parent::_prepareColumns();
    }

    //########################################

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('listing_product_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        $this->_prepareMassactionGroup()
            ->_prepareMassactionItems();
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareMassactionGroup()
    {
        $this->getMassactionBlock()->setGroups(
            array(
                'edit_settings'            => Mage::helper('M2ePro')->__('Edit General Settings'),
                'edit_categories_settings' => Mage::helper('M2ePro')->__('Edit Category Settings'),
                'other'                    => Mage::helper('M2ePro')->__('Other')
            )
        );

        return $this;
    }

    protected function _prepareMassactionItems()
    {
        $this->getMassactionBlock()->addItem(
            'editCategorySettings', array(
                'label'    => Mage::helper('M2ePro')->__('Categories & Specifics'),
                'url'      => '',
            ), 'edit_categories_settings'
        );

        $this->getMassactionBlock()->addItem(
            'editAllSettings', array(
            'label'    => Mage::helper('M2ePro')->__('All Settings'),
            'url'      => '',
        ), 'edit_settings'
        );

        $this->getMassactionBlock()->addItem(
            'editSellingSettings', array(
            'label'    => Mage::helper('M2ePro')->__('Selling'),
            'url'      => '',
        ), 'edit_settings'
        );

        $this->getMassactionBlock()->addItem(
            'editSynchSettings', array(
            'label'    => Mage::helper('M2ePro')->__('Synchronization'),
            'url'      => '',
        ), 'edit_settings'
        );

        $this->getMassactionBlock()->addItem(
            'editGeneralSettings', array(
            'label'    => Mage::helper('M2ePro')->__('Payment and Shipping'),
            'url'      => '',
        ), 'edit_settings'
        );

        if ($this->isMotorsAvailable() && $this->_motorsAttribute) {
            $this->getMassactionBlock()->addItem(
                'editMotors', array(
                'label' => Mage::helper('M2ePro')->__('Add Compatible Vehicles'),
                'url'   => ''
                ), 'other'
            );
        }

        $this->getMassactionBlock()->addItem(
            'moving', array(
            'label'    => Mage::helper('M2ePro')->__('Move Item(s) to Another Listing'),
            'url'      => '',
            'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );

        $this->getMassactionBlock()->addItem(
            'transferring', array(
            'label'    => Mage::helper('M2ePro')->__('Sell on Another Marketplace'),
            'url'      => '',
            ), 'other'
        );

        return $this;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        $sku = $row->getData('sku');
        if ($sku === null) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;';
        $value .= Mage::helper('M2ePro')->escapeHtml($sku);

        /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
        $listingProduct = Mage::helper('M2ePro/Component_Ebay')
            ->getObject('Listing_Product', $row->getData('listing_product_id'));

        if ($listingProduct->getChildObject()->isVariationsReady()) {
            $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

            $productAttributes = isset($additionalData['variations_sets'])
                ? array_keys($additionalData['variations_sets']) : array();

            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin: 7px 0 0 7px">';
            $value .= implode(', ', $productAttributes);
            $value .= '</div>';
        }

        $categoriesData = array(
            eBayCategory::TYPE_EBAY_MAIN => array(
                'mode'  => $row->getData('category_main_mode'),
                'value' => $row->getData('category_main_mode') == TemplateCategory::CATEGORY_MODE_EBAY
                              ? $row->getData('category_main_id')
                              : $row->getData('category_main_attribute'),
                'path'  => $row->getData('category_main_path'),
                'is_custom_template' => $row->getData('category_main_is_custom_template')
            ),
            eBayCategory::TYPE_EBAY_SECONDARY => array(
                'mode'  => $row->getData('category_secondary_mode'),
                'value' => $row->getData('category_secondary_mode') == TemplateCategory::CATEGORY_MODE_EBAY
                              ? $row->getData('category_secondary_id')
                              : $row->getData('category_secondary_attribute'),
                'path'  => $row->getData('category_secondary_path'),
            ),
            eBayCategory::TYPE_STORE_MAIN => array(
                'mode'  => $row->getData('store_category_main_mode'),
                'value' => $row->getData('store_category_main_mode') == TemplateCategory::CATEGORY_MODE_EBAY
                              ? $row->getData('store_category_main_id')
                              : $row->getData('store_category_main_attribute'),
                'path'  => $row->getData('store_category_main_path'),
            ),
            eBayCategory::TYPE_STORE_SECONDARY => array(
                'mode'  => $row->getData('store_category_secondary_mode'),
                'value' => $row->getData('store_category_secondary_mode') == TemplateCategory::CATEGORY_MODE_EBAY
                              ? $row->getData('store_category_secondary_id')
                              : $row->getData('store_category_secondary_attribute'),
                'path'  => $row->getData('store_category_secondary_path'),
            ),
        );

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Grid_Column_Renderer_CategoryInfo $renderer */
        $renderer = Mage::getBlockSingleton('M2ePro/adminhtml_ebay_grid_column_renderer_categoryInfo');
        $renderer->setColumn($column);
        $renderer->setEntityIdField('listing_product_id');
        $renderer->setListing($this->_listing);
        $renderer->setHideSpecificsRequiredMark(true);
        $renderer->setCategoriesData(
            array($row->getData('listing_product_id') => $categoriesData)
        );

        return $value . '<br/><br/>' . $renderer->render($row) . '<br/>';
    }

    //########################################

    public function callbackColumnGeneralSettings($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        /** @var $listingProduct Ess_M2ePro_Model_Ebay_Listing_Product */
        $listingProduct = $this->getListingProductCollection()
            ->getItemById($row->getData('id'))
            ->getChildObject();

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT);

        $paymentSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $paymentSettings = $helper->__('Use from Listing Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $paymentSettings = $helper->__('Custom Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $paymentSettings = $tm->getResultObject()->getTitle();
                break;
        }

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);

        $shippingSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $shippingSettings = $helper->__('Use from Listing Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $shippingSettings = $helper->__('Custom Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $shippingSettings = $tm->getResultObject()->getTitle();
                break;
        }

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY);

        $returnSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $returnSettings = $helper->__('Use from Listing Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $returnSettings = $helper->__('Custom Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $returnSettings = $tm->getResultObject()->getTitle();
                break;
        }

        $html = <<<HTML
<div style="padding: 4px; color: #666666">
    <span style="text-decoration: underline; font-weight: bold">{$helper->__('Payment')}</span><br/>
    <span>{$paymentSettings}</span><br/>

    <span style="text-decoration: underline; font-weight: bold">{$helper->__('Shipping')}</span><br/>
    <span>{$shippingSettings}</span><br/>

    <span style="text-decoration: underline; font-weight: bold">{$helper->__('Return')}</span><br/>
    <span>{$returnSettings}</span>
</div>
HTML;

        return $html;
    }

    //########################################

    public function callbackColumnSellingSettings($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        /** @var $listingProduct Ess_M2ePro_Model_Ebay_Listing_Product */
        $listingProduct = $this->getListingProductCollection()
            ->getItemById($row->getData('id'))
            ->getChildObject();

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);

        $sellingSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $sellingSettings = $helper->__('Use from Listing Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $sellingSettings = $helper->__('Custom Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $sellingSettings = $tm->getResultObject()->getTitle();
                break;
        }

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);

        $descriptionSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $descriptionSettings = $helper->__('Use from Listing Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $descriptionSettings = $helper->__('Custom Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $descriptionSettings = $tm->getResultObject()->getTitle();
                break;
        }

        $html = <<<HTML
<div style="padding: 4px; color: #666666">
    <span style="text-decoration: underline; font-weight: bold">{$helper->__('Selling')}</span><br/>
    <span>{$sellingSettings}</span><br/>

    <span style="text-decoration: underline; font-weight: bold">{$helper->__('Description')}</span><br/>
    <span>{$descriptionSettings}</span>
</div>
HTML;

        return $html;
    }

    //########################################

    public function callbackColumnSynchSettings($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        /** @var $listingProduct Ess_M2ePro_Model_Ebay_Listing_Product */
        $listingProduct = $this->getListingProductCollection()
            ->getItemById($row->getData('id'))
            ->getChildObject();

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION);

        $synchSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $synchSettings = $helper->__('Use from Listing Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $synchSettings = $helper->__('Custom Settings');
                break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $synchSettings = $tm->getResultObject()->getTitle();
                break;
        }

        $html = <<<HTML
<div style="padding: 4px">
    <span style="color: #666666">{$synchSettings}</span><br/>
</div>
HTML;

        return $html;
    }

    //########################################

    public function callbackColumnMotorsAttribute($value, $row, $column, $isExport)
    {
        if (!$this->_motorsAttribute) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if (!$row->getData('is_motors_attribute_in_product_attribute_set')) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $attributeCode = $this->_motorsAttribute->getAttributeCode();
        $attributeValue = $row->getData($attributeCode);

        if (empty($attributeValue)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $motorsData = $this->_productsMotorsData[$row->getData('listing_product_id')];

        $countOfItems = count($motorsData['items']);
        $countOfFilters = count($motorsData['filters']);
        $countOfGroups = count($motorsData['groups']);

        $showAll = false;

        if ($countOfItems + $countOfFilters + $countOfGroups === 0) {
            $showAll = true;
        }

        if (Mage::helper('M2ePro/Component_Ebay_Motors')->isTypeBasedOnEpids($this->getMotorsType())){
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
    onclick="EbayMotorsObj.openViewItemPopup(
        {$row->getData('id')},
        EbayListingSettingsGridObj
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
    onclick="EbayMotorsObj.openViewFilterPopup(
        {$row->getData('id')},
        EbayListingSettingsGridObj
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
    onclick="EbayMotorsObj.openViewGroupPopup(
        {$row->getData('id')},
        EbayListingSettingsGridObj
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
        $value = $column->getFilter()->getValue();
        $inputValue = null;

        if (is_array($value) && isset($value['input'])) {
            $inputValue = $value['input'];
        } elseif (is_string($value)) {
            $inputValue = $value;
        }

        if ($inputValue !== null) {
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

        if (isset($value['select'])) {
            $collection->addFieldToFilter(
                'template_category_id',
                array(
                ($value['select'] ? 'notnull' : 'null') => true)
            );
        }
    }

    protected function callbackFilterSettings($collection, $column)
    {
        $filter = $column->getFilter()->getValue();
        if ($filter === null) {
            return;
        }

        $filter = Mage::helper('M2ePro')->jsonDecode($filter);

        $field = 'template_'.$filter['template'].'_mode';
        $value = $filter['mode'];

        $collection->addFieldToFilter(
            array(
                array('attribute'=>$field,'eq'=>$value),
            )
        );
    }

    public function callbackFilterMotorsAttribute(Varien_Data_Collection_Db $collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value === null) {
            return;
        }

        if (!$this->_motorsAttribute) {
            return;
        }

        $attributeCode = $this->_motorsAttribute->getAttributeCode();

        if ($value == 1) {
            $collection->addFieldToFilter($attributeCode, array('notnull'=>true));
            $collection->addFieldToFilter($attributeCode, array('neq'=>''));
            $collection->addFieldToFilter(
                'is_motors_attribute_in_product_attribute_set',
                array('notnull'=>true)
            );
        } else {
            $collection->addFieldToFilter(
                array(
                    array('attribute' => $attributeCode, 'null' => true),
                    array('attribute' => $attributeCode, 'eq' => ''),
                    array('attribute' => 'is_motors_attribute_in_product_attribute_set', 'null' => true)
                )
            );
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

    /**
     * @inheritdoc
     **/
    private function getListing()
    {
        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
                'Listing', $this->getRequest()->getParam('id')
            );
        }

        return $this->_listing;
    }

    private function getListingProductCollection()
    {
        $collection = $this->getData('listing_product_collection');

        if ($collection === null) {
            $ids = array();

            foreach ($this->getCollection()->getItems() as $item) {
                $ids[] = $item->getData('listing_product_id');
            }

            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
            $collection->addFieldToFilter('id', array('in' => $ids));

            $this->setData('listing_product_collection', $collection);
        }

        return $collection;
    }

    private function getGroupOrder()
    {
        return array(
            'edit_general_settings'    => Mage::helper('M2ePro')->__('Edit General Settings'),
            'edit_categories_settings' => Mage::helper('M2ePro')->__('Edit Category Settings'),
            'other'                    => Mage::helper('M2ePro')->__('Other')
        );
    }

    private function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'allSettings' => array(
                'caption' => $helper->__('All Settings'),
                'group' => 'edit_general_settings',
                'field' => 'id',
                'onclick_action' => 'EbayListingSettingsGridObj.actions[\'editAllSettingsAction\']'
            ),

            'editSelling' => array(
                'caption' => $helper->__('Selling'),
                'group'   => 'edit_general_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridObj.actions[\'editSellingSettingsAction\']'
            ),

            'editSynchSettings' => array(
                'caption' => $helper->__('Synchronization'),
                'group'   => 'edit_general_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridObj.actions[\'editSynchSettingsAction\']'
            ),

            'paymentAndShipping' => array(
                'caption' => $helper->__('Payment and Shipping'),
                'group'   => 'edit_general_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridObj.actions[\'editGeneralSettingsAction\']'
            ),

            'editCategories' => array(
                'caption' => $helper->__('Categories & Specifics'),
                'group'   => 'edit_categories_settings',
                'field'   => 'id',
                'onclick_action' => "EbayListingSettingsGridObj.actions['editCategorySettingsAction']"
            )
        );

        if ($this->isMotorsAvailable() && $this->_motorsAttribute) {
            $actions['addCompatibleVehicles'] =  array(
                'caption' => $helper->__('Add Compatible Vehicles'),
                'group'   => 'other',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridObj.actions[\'editMotorsAction\']'
            );
        }

        return $actions;
    }

    //########################################

    private function isMotorsAvailable()
    {
        return $this->isMotorEpidsAvailable() || $this->isMotorKtypesAvailable();
    }

    private function isMotorEpidsAvailable()
    {
        return $this->getListing()->getChildObject()->isPartsCompatibilityModeEpids();
    }

    private function isMotorKtypesAvailable()
    {
        return $this->getListing()->getChildObject()->isPartsCompatibilityModeKtypes();
    }

    //########################################

    private function prepareExistingMotorsData()
    {
        $this->injectMotorAttributeData();

        $this->_productsMotorsData = array();
        $motorsHelper              = Mage::helper('M2ePro/Component_Ebay_Motors');

        $items   = array();
        $filters = array();
        $groups  = array();

        foreach ($this->getCollection()->getItems() as $product) {
            if (!$product->getData('is_motors_attribute_in_product_attribute_set')) {
                continue;
            }

            $productId = $product->getData('listing_product_id');

            $attributeCode = $this->_motorsAttribute->getAttributeCode();
            $attributeValue = $product->getData($attributeCode);

            $this->_productsMotorsData[$productId] = $motorsHelper->parseAttributeValue($attributeValue);

            $items   = array_merge($items, array_keys($this->_productsMotorsData[$productId]['items']));
            $filters = array_merge($filters, $this->_productsMotorsData[$productId]['filters']);
            $groups  = array_merge($groups, $this->_productsMotorsData[$productId]['groups']);
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

        if ($motorsHelper->isTypeBasedOnEpids($this->getMotorsType())) {
            $select->where('scope = ?', $motorsHelper->getEpidsScopeByType($this->getMotorsType()));
        }

        $existedItems = $select->query()->fetchAll(PDO::FETCH_COLUMN);
        //-------------------------------

        //-------------------------------
        $filtersTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_motor_filter');
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
        $groupsTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_motor_group');
        $select = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                $groupsTable,
                array('id')
            )
            ->where('`id` IN (?)', $groups);

        $existedGroups = $select->query()->fetchAll(PDO::FETCH_COLUMN);
        //-------------------------------

        foreach ($this->_productsMotorsData as $productId => $productMotorsData) {
            foreach (array_diff(array_keys($productMotorsData['items']), $existedItems) as $key) {
                unset($this->_productsMotorsData[$productId]['items'][$key]);
            }

            $invalidFilters = array_diff(
                $productMotorsData['filters'],
                array_intersect($productMotorsData['filters'], $existedFilters)
            );
            foreach ($invalidFilters as $key => $filterId) {
                unset($this->_productsMotorsData[$productId]['filters'][$key]);
            }

            $invalidGroups = array_diff(
                $productMotorsData['groups'],
                array_intersect($productMotorsData['groups'], $existedGroups)
            );
            foreach ($invalidGroups as $key => $groupId) {
                unset($this->_productsMotorsData[$productId]['groups'][$key]);
            }
        }

        return $this;
    }

    private function injectMotorAttributeData()
    {
        $productsIds = array();
        foreach ($this->getCollection()->getItems() as $product) {
            if (!$product->getData('is_motors_attribute_in_product_attribute_set')) {
                continue;
            }

            $productsIds[] = $product->getEntityId();
        }

        $attributeData = array();
        $queryStmt = Mage::getResourceModel('core/config')->getReadConnection()
            ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('catalog_product_entity_text'),
                array(
                    'entity_id', 'value'
                )
            )
            ->where('attribute_id = ?', $this->_motorsAttribute->getId())
            ->where('entity_id IN (?)', $productsIds)
            ->where(
                'store_id IN (?)', array(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                $this->getListing()->getStoreId())
            )
            ->order('store_id ASC')
            ->query();

        while ($row = $queryStmt->fetch()) {
            $attributeData[$row['entity_id']] = $row['value'];
        }

        $attributeCode = $this->_motorsAttribute->getAttributeCode();
        foreach ($this->getCollection()->getItems() as $product) {
            if (isset($attributeData[$product->getEntityId()])) {
                $product->setData($attributeCode, $attributeData[$product->getEntityId()]);
            }
        }
    }

    //########################################

    protected function _toHtml()
    {
        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $urls = array_merge(
            $helper->getControllerActions('adminhtml_ebay_motor')
        );

        $path = 'adminhtml_ebay_template/editListingProduct';
        $urls[$path] = $this->getUrl("*/{$path}");

        $path = 'adminhtml_ebay_template/saveListingProduct';
        $urls[$path] = $this->getUrl("*/{$path}");

        $path = 'adminhtml_ebay_listing/saveCategoryTemplate';
        $urls[$path] = $this->getUrl('*/' . $path);

        $path = 'adminhtml_ebay_listing/runTransferring';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
                'listing_id' => $this->getListing()->getId()
            )
        );

        $path = 'adminhtml_ebay_log/listing';
        $urls[$path] = $this->getUrl(
            '*/adminhtml_ebay_log/listing', array(
                'listing_id' => $this->getListing()->getId()
            )
        );

        $path = 'adminhtml_ebay_listing/getEstimatedFees';
        $urls[$path] = $this->getUrl(
            '*/' . $path, array(
                'listing_id' => $this->getListing()->getId()
            )
        );

        $path = 'adminhtml_ebay_listing/getCategoryChooserHtml';
        $urls[$path] = $this->getUrl('*/' . $path);

        $urls['moveToListingPopupHtml'] = $this->getUrl('*/adminhtml_listing_moving/moveToListingPopupHtml');
        $urls['prepareMoveToListing'] = $this->getUrl('*/adminhtml_listing_moving/prepareMoveToListing');
        $urls['moveToListing'] = $this->getUrl('*/adminhtml_listing_moving/moveToListing');

        $urls = Mage::helper('M2ePro')->jsonEncode($urls);
        // ---------------------------------------

        if (Mage::helper('M2ePro/Component_Ebay_Motors')->isTypeBasedOnEpids($this->getMotorsType())) {
            $motorsTypeTitle = 'ePID';
        } else {
            $motorsTypeTitle = 'kType';
        }

        //------------------------------
        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
                'Edit Payment and Shipping Settings' => $helper->__('Edit Payment and Shipping Settings'),
                'Edit Selling Settings' => $helper->__('Edit Selling Settings'),
                'Edit Synchronization Settings' => $helper->__('Edit Synchronization Settings'),
                'Edit Settings' => $helper->__('Edit Settings'),
                'for' => $helper->__('for'),
                'Category Settings' => $helper->__('Category Settings'),
                'Specifics' => $helper->__('Specifics'),
                'Compatibility Attribute ePIDs' => $helper->__('Compatibility Attribute ePIDs'),
                'Confirm' => $helper->__('Confirm'),
                'Add Compatible Vehicles' => $helper->__('Add Compatible Vehicles'),
                'Save Filter' => $helper->__('Save Filter'),
                'Save as Group' => $helper->__('Save as Group'),
                'Set Note' => $helper->__('Set Note'),
                'View Items' => $helper->__('Selected %items_title%s', $motorsTypeTitle),
                'View Filters' => $helper->__('Selected Filters'),
                'View Groups' => $helper->__('Selected Groups'),
                'Selected Items' => $helper->__('Selected %items_title%s', $motorsTypeTitle),
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
                'Body Style' => $helper->__('Body Style'),
                'Street Name' => $helper->__('Street Name')
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $constants = $helper->getClassConstantAsJson('Ess_M2ePro_Helper_Component_Ebay_Category');
        // ---------------------------------------

        $component = Ess_M2ePro_Helper_Component_Ebay::NICK;

        $temp = Mage::helper('M2ePro/Data_Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

        $gridId = $component . 'ListingViewGrid' . $this->getListing()->getId();
        $ignoreListings = Mage::helper('M2ePro')->jsonEncode(array($this->getListing()->getId()));

        $logViewUrl = $this->getUrl(
            '*/adminhtml_ebay_log/listing', array(
                'listing_id' => $this->getListing()->getId(),
                'back' => $helper->makeBackUrlParam(
                    '*/adminhtml_ebay_listing/view', array('id' => $this->getListing()->getId())
                )
            )
        );
        $getErrorsSummary = $this->getUrl('*/adminhtml_listing/getErrorsSummary');

        $sendingDataToEbayMessage = $helper->escapeJs($helper->__('Sending %product_title% Product(s) data on eBay.'));
        $viewAllProductLogMessage = $helper->escapeJs($helper->__('View All Product Log.'));

        $listingLockedMessage = $helper->escapeJs(
            $helper->__('The Listing was locked by another process. Please try again later.')
        );
        $listingEmptyMessage = $helper->escapeJs(
            $helper->__('Listing is empty.')
        );

        $popupTitle = $helper->escapeJs($helper->__('Moving eBay Items'));

        $motorsType = '';
        if ($this->isMotorsAvailable()) {
            $motorsType = $this->getMotorsType();
        }

        $html = <<<HTML
<script type="text/javascript">

    EbayListingSettingsGridObj.afterInitPage();

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});
    M2ePro.php.setConstants({$constants},'Ess_M2ePro_Helper_Component_Ebay_Category');

    M2ePro.productsIdsForList = '{$productsIdsForList}';

    M2ePro.url.logViewUrl = '{$logViewUrl}';
    M2ePro.url.getErrorsSummary = '{$getErrorsSummary}';

    M2ePro.text.popup_title = '{$popupTitle}';

    M2ePro.text.sending_data_message = '{$sendingDataToEbayMessage}';
    M2ePro.text.view_all_product_log_message = '{$viewAllProductLogMessage}';

    M2ePro.text.listing_locked_message = '{$listingLockedMessage}';
    M2ePro.text.listing_empty_message = '{$listingEmptyMessage}';

    M2ePro.customData.componentMode = '{$component}';
    M2ePro.customData.gridId = '{$gridId}';
    M2ePro.customData.ignoreListings = '{$ignoreListings}';

    Event.observe(window, 'load', function() {
        EbayListingTransferringObj = new EbayListingTransferring({$this->getListing()->getId()});
        EbayMotorsObj = new EbayMotors({$this->getListing()->getId()}, '{$motorsType}');
    });

</script>
HTML;

        // ---------------------------------------
        if ($this->getRequest()->getParam('auto_actions')) {
            $html .= <<<HTML
<script type="text/javascript">
    Event.observe(window, 'load', function() {
        ListingAutoActionObj.loadAutoActionHtml();
    });
</script>
HTML;
        }

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">
    EbayListingSettingsGridObj = new {$this->getGridHandlerJs()}(
        '{$this->getId()}',
        '{$this->getListing()->getId()}',
        '{$this->getListing()->getMarketplaceId()}',
        '{$this->getListing()->getAccountId()}'
    );
    EbayListingCategoryObj = new EbayListingCategory(EbayListingSettingsGridObj);
</script>
HTML;
        }

        // ---------------------------------------

        return parent::_toHtml() . $additionalJs . $html;
    }

    //########################################
}
