<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Settings_Grid_Abstract
    extends Ess_M2ePro_Block_Adminhtml_Magento_Product_Grid_Abstract
{
    protected $listing = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingSettingsGridAbstract');
        // ---------------------------------------

        $this->showAdvancedFilterProductsOption = false;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     **/
    abstract protected function getListing();

    abstract protected function getGridHandlerJs();

    protected function getListingProductCollection()
    {
        $collection = $this->getData('listing_product_collection');

        if (is_null($collection)) {

            $ids = array();

            foreach ($this->getCollection()->getItems() as $item) {
                $ids[] = $item->getData('listing_product_id');
            }

            $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
            $collection->addFieldToFilter('id', array('in' => $ids));

            $this->setData('listing_product_collection',$collection);
        }

        return $collection;
    }

    //########################################

    protected function addColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => Mage::helper('M2ePro')->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId'),
        ));

        $modeParent   = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT;
        $modeCustom   = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM;
        $modeTemplate = Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE;

        $templatePayment  = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT;
        $templateShipping = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING;
        $templateReturn   = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN;

        $this->addColumn('general_settings',array(
            'header'=> Mage::helper('catalog')->__('Payment and Shipping Settings'),
            'width' => '170px',
            'type'  => 'options',
            'sortable'  => false,
            'option_groups' => array(
                array(
                    'label' => Mage::helper('M2ePro')->__('Payment'),
                    'value' => array(
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeParent,
                                'template' => $templatePayment
                            )),
                            'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeCustom,
                                'template' => $templatePayment
                            )),
                            'label' => Mage::helper('M2ePro')->__('Custom Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeTemplate,
                                'template' => $templatePayment
                            )),
                            'label' => Mage::helper('M2ePro')->__('Policies')
                        ),
                    ),
                ),

                array(
                    'label' => Mage::helper('M2ePro')->__('Shipping'),
                    'value' => array(
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeParent,
                                'template' => $templateShipping
                            )),
                            'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeCustom,
                                'template' => $templateShipping
                            )),
                            'label' => Mage::helper('M2ePro')->__('Custom Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeTemplate,
                                'template' => $templateShipping
                            )),
                            'label' => Mage::helper('M2ePro')->__('Policies')
                        )
                    ),
                ),

                array(
                    'label' => Mage::helper('M2ePro')->__('Return'),
                    'value' => array(
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeParent,
                                'template' => $templateReturn
                            )),
                            'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeCustom,
                                'template' => $templateReturn
                            )),
                            'label' => Mage::helper('M2ePro')->__('Custom Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeTemplate,
                                'template' => $templateReturn
                            )),
                            'label' => Mage::helper('M2ePro')->__('Policies')
                        )
                    ),
                )
            ),
            'filter_condition_callback' => array($this, 'callbackFilterSettings'),
            'frame_callback' => array($this, 'callbackColumnGeneralSettings')
        ));

        $templateSelling     = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
        $templateDescription = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION;

        $this->addColumn('selling_settings',array(
            'header'=> Mage::helper('catalog')->__('Selling Settings'),
            'width' => '170px',
            'type'  => 'options',
            'sortable'  => false,
            'option_groups' => array(

                array(
                    'label' => Mage::helper('M2ePro')->__('Price, Quantity and Format'),
                    'value' => array(
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeParent,
                                'template' => $templateSelling
                            )),
                            'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeCustom,
                                'template' => $templateSelling
                            )),
                            'label' => Mage::helper('M2ePro')->__('Custom Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeTemplate,
                                'template' => $templateSelling
                            )),
                            'label' => Mage::helper('M2ePro')->__('Policies')
                        ),
                    ),
                ),

                array(
                    'label' => Mage::helper('M2ePro')->__('Description'),
                    'value' => array(
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeParent,
                                'template' => $templateDescription
                            )),
                            'label' => Mage::helper('M2ePro')->__('Use from Listing Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeCustom,
                                'template' => $templateDescription
                            )),
                            'label' => Mage::helper('M2ePro')->__('Custom Settings')
                        ),
                        array(
                            'value' => json_encode(array(
                                'mode'     => $modeTemplate,
                                'template' => $templateDescription
                            )),
                            'label' => Mage::helper('M2ePro')->__('Policies')
                        ),
                    ),
                )
            ),
            'filter_condition_callback' => array($this, 'callbackFilterSettings'),
            'frame_callback' => array($this, 'callbackColumnSellingSettings')
        ));

        if (Mage::helper('M2ePro/View_Ebay')->isAdvancedMode()) {
            $templateSynch = Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;

            $this->addColumn('synch_settings',array(
                'header'=> Mage::helper('catalog')->__('Synchronization'),
                'width' => '170px',
                'type'  => 'options',
                'sortable'  => false,
                'options' => array(
                    json_encode(array(
                        'mode'     => $modeParent,
                        'template' => $templateSynch
                    )) => Mage::helper('M2ePro')->__('Use from Listing Settings'),

                    json_encode(array(
                        'mode'     => $modeCustom,
                        'template' => $templateSynch
                    )) => Mage::helper('M2ePro')->__('Custom Settings'),

                    json_encode(array(
                        'mode'     => $modeTemplate,
                        'template' => $templateSynch
                    )) => Mage::helper('M2ePro')->__('Policies'),
                ),
                'filter_condition_callback' => array($this, 'callbackFilterSettings'),
                'frame_callback' => array($this, 'callbackColumnSynchSettings')
            ));
        }

        $this->addColumn('actions', array(
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
        ));
    }

    //########################################

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('listing_product_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
        $this->_prepareMassactionGroup()
             ->_prepareMassactionItems();
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    protected function _prepareMassactionGroup()
    {
        $this->getMassactionBlock()->setGroups(array(
            'edit_settings' => Mage::helper('M2ePro')->__('Edit Settings'),
            'edit_categories_settings' => Mage::helper('M2ePro')->__('Edit Categories Settings'),
            'other' => Mage::helper('M2ePro')->__('Other')
        ));

        return $this;
    }

    protected function _prepareMassactionItems()
    {
        $this->getMassactionBlock()->addItem('editAllSettings', array(
            'label'    => Mage::helper('M2ePro')->__('All Settings'),
            'url'      => '',
        ), 'edit_settings');

        $this->getMassactionBlock()->addItem('editSellingSettings', array(
            'label'    => Mage::helper('M2ePro')->__('Selling'),
            'url'      => '',
        ), 'edit_settings');

        $this->getMassactionBlock()->addItem('editSynchSettings', array(
            'label'    => Mage::helper('M2ePro')->__('Synchronization'),
            'url'      => '',
        ), 'edit_settings');

        $this->getMassactionBlock()->addItem('editGeneralSettings', array(
            'label'    => Mage::helper('M2ePro')->__('Payment and Shipping'),
            'url'      => '',
        ), 'edit_settings');

        return $this;
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        $value = '<span>'.Mage::helper('M2ePro')->escapeHtml($value).'</span>';

        $sku = $row->getData('sku');
        if (is_null($sku)) {
            $sku = Mage::getModel('M2ePro/Magento_Product')->setProductId($row->getData('entity_id'))->getSku();
        }

        $value .= '<br/><strong>'.Mage::helper('M2ePro')->__('SKU') . ':</strong>&nbsp;';
        $value .= Mage::helper('M2ePro')->escapeHtml($sku);

        return $value;
    }

    //########################################

    public function callbackColumnGeneralSettings($value, $row, $column, $isExport)
    {
        $helper = Mage::helper('M2ePro');

        /* @var $listingProduct Ess_M2ePro_Model_Ebay_Listing_Product */
        $listingProduct = $this->getListingProductCollection()
            ->getItemById($row->getData('id'))
            ->getChildObject();

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT);

        $paymentSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $paymentSettings = $helper->__('Use from Listing Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $paymentSettings = $helper->__('Custom Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $paymentSettings = $tm->getResultObject()->getTitle(); break;
        }

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING);

        $shippingSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $shippingSettings = $helper->__('Use from Listing Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $shippingSettings = $helper->__('Custom Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $shippingSettings = $tm->getResultObject()->getTitle(); break;
        }

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN);

        $returnSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $returnSettings = $helper->__('Use from Listing Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $returnSettings = $helper->__('Custom Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $returnSettings = $tm->getResultObject()->getTitle(); break;
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

        /* @var $listingProduct Ess_M2ePro_Model_Ebay_Listing_Product */
        $listingProduct = $this->getListingProductCollection()
            ->getItemById($row->getData('id'))
            ->getChildObject();

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);

        $sellingSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $sellingSettings = $helper->__('Use from Listing Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $sellingSettings = $helper->__('Custom Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $sellingSettings = $tm->getResultObject()->getTitle(); break;
        }

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_DESCRIPTION);

        $descriptionSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $descriptionSettings = $helper->__('Use from Listing Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $descriptionSettings = $helper->__('Custom Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $descriptionSettings = $tm->getResultObject()->getTitle(); break;
        }

        $html = <<<HTML
<div style="padding: 4px; color: #666666">
    <span style="text-decoration: underline; font-weight: bold">{$helper->__('Price, Quantity and Format')}</span><br/>
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

        /* @var $listingProduct Ess_M2ePro_Model_Ebay_Listing_Product */
        $listingProduct = $this->getListingProductCollection()
            ->getItemById($row->getData('id'))
            ->getChildObject();

        $tm = $listingProduct->getTemplateManager(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION);

        $synchSettings = '';
        switch ($tm->getModeValue()) {
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT:
                $synchSettings = $helper->__('Use from Listing Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_CUSTOM:
                $synchSettings = $helper->__('Custom Settings'); break;
            case Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE:
                $synchSettings = $tm->getResultObject()->getTitle(); break;
        }

        $html = <<<HTML
<div style="padding: 4px">
    <span style="color: #666666">{$synchSettings}</span><br/>
</div>
HTML;

        return $html;
    }

    //########################################

    protected function getGroupOrder()
    {
        return array(
            'edit_general_settings'    => Mage::helper('M2ePro')->__('Edit General Settings'),
            'edit_categories_settings' => Mage::helper('M2ePro')->__('Edit Categories Settings'),
            'other'                    => Mage::helper('M2ePro')->__('Other')
        );
    }

    protected function getColumnActionsItems()
    {
        $helper = Mage::helper('M2ePro');

        $actions = array(
            'allSettings' => array(
                'caption' => $helper->__('All Settings'),
                'group' => 'edit_general_settings',
                'field' => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editAllSettingsAction\']'
            ),

            'editSelling' => array(
                'caption' => $helper->__('Selling'),
                'group'   => 'edit_general_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editSellingSettingsAction\']'
            ),

            'editSynchSettings' => array(
                'caption' => $helper->__('Synchronization'),
                'group'   => 'edit_general_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editSynchSettingsAction\']'
            ),

            'paymentAndShipping' => array(
                'caption' => $helper->__('Payment and Shipping'),
                'group'   => 'edit_general_settings',
                'field'   => 'id',
                'onclick_action' => 'EbayListingSettingsGridHandlerObj.actions[\'editGeneralSettingsAction\']'
            )
        );

        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            unset($actions['editSynchSettings']);
        }

        return $actions;
    }

    //########################################

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name','like'=>'%'.$value.'%')
            )
        );
    }

    //########################################

    protected function callbackFilterSettings($collection, $column)
    {
        $filter = $column->getFilter()->getValue();
        if (is_null($filter == null)) {
            return;
        }

        $filter = json_decode($filter, true);

        $field = 'template_'.$filter['template'].'_mode';
        $value = $filter['mode'];

        $collection->addFieldToFilter(
            array(
                array('attribute'=>$field,'eq'=>$value),
            )
        );
    }

    //########################################

    protected function _toHtml()
    {
        $key = 'adminhtml_ebay_template/editListingProduct';
        $urls[$key] = $this->getUrl("*/{$key}");

        $key = 'adminhtml_ebay_template/saveListingProduct';
        $urls[$key] = $this->getUrl("*/{$key}");

        $urls = json_encode($urls);

        $translations = json_encode(array(
            'Edit Payment and Shipping Settings' => Mage::helper('M2ePro')->__('Edit Payment and Shipping Settings'),
            'Edit Selling Settings' => Mage::helper('M2ePro')->__('Edit Selling Settings'),
            'Edit Synchronization Settings' => Mage::helper('M2ePro')->__('Edit Synchronization Settings'),
            'Edit Settings' => Mage::helper('M2ePro')->__('Edit Settings'),
            'for' => Mage::helper('M2ePro')->__('for')
        ));

        $commonJs = <<<HTML
<script type="text/javascript">
    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    EbayListingSettingsGridHandlerObj.afterInitPage();
    EbayListingSettingsGridHandlerObj.getGridMassActionObj().setGridIds('{$this->getGridIdsJson()}');
</script>
HTML;

        $additionalJs = '';
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $additionalJs = <<<HTML
<script type="text/javascript">
    EbayListingSettingsGridHandlerObj = new {$this->getGridHandlerJs()}(
        '{$this->getId()}',
        '{$this->getListing()->getId()}'
    );
</script>
HTML;
        }

        return parent::_toHtml() . $additionalJs . $commonJs;
    }

    //########################################

    private function getGridIdsJson()
    {
        $select = clone $this->getCollection()->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->resetJoinLeft();

        $select->columns('elp.listing_product_id');

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        return implode(',',$connRead->fetchCol($select));
    }

    //########################################
}