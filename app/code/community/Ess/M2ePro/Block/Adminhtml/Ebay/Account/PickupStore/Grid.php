<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_PickupStore_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayAccountPickupStoreGrid');
        // ---------------------------------------

        // Set default values
        // ---------------------------------------
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        $pickupStoreCollection = Mage::getModel('M2ePro/Ebay_Account_PickupStore')->getCollection();
        $pickupStoreCollection->getSelect()->join(
            array('mm' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            'main_table.marketplace_id = mm.id',
            array('marketplace_title' => 'title', 'marketplace_id' => 'id')
        );
        $pickupStoreCollection->addFieldToFilter('mm.component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);

        $pickupStoreCollection->getSelect()->join(
            array('ma' => Mage::getResourceModel('M2ePro/Account')->getMainTable()),
            'main_table.account_id = ma.id',
            array('account_title' => 'title')
        );
        $pickupStoreCollection->addFieldToFilter('ma.component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);

        // Set collection to grid
        $this->setCollection($pickupStoreCollection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('M2ePro')->__('Name'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'main_table.name'
        ));

        $this->addColumn('location_id', array(
            'header'    => Mage::helper('M2ePro')->__('Location ID'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'location_id',
            'escape'    => true,
            'filter_index' => 'main_table.location_id',
        ));

        $this->addColumn('marketplace_id', array(
            'header'    => Mage::helper('M2ePro')->__('Country'),
            'align'     => 'left',
            'width'     => '200px',
            'type'      => 'options',
            'index'     => 'marketplace_id',
            'escape'    => true,
            'filter_index' => 'mm.title',
            'filter_condition_callback' => array($this, 'callbackFilterMarketplace'),
            'options' => $this->getEnabledMarketplaceTitles()
        ));

        $this->addColumn('create_date', array(
            'header'    => Mage::helper('M2ePro')->__('Creation Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'create_date',
            'filter_index' => 'main_table.create_date'
        ));

        $this->addColumn('update_date', array(
            'header'    => Mage::helper('M2ePro')->__('Update Date'),
            'align'     => 'left',
            'width'     => '150px',
            'type'      => 'datetime',
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
            'index'     => 'update_date',
            'filter_index' => 'main_table.update_date'
        ));

        $this->addColumn('actions', array(
            'header'    => Mage::helper('M2ePro')->__('Actions'),
            'align'     => 'left',
            'width'     => '100px',
            'type'      => 'action',
            'index'     => 'actions',
            'filter'    => false,
            'sortable'  => false,
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Edit'),
                    'url'       => array(
                        'base' => '*/adminhtml_ebay_accountPickupStore/edit'
                    ),
                    'field'     => 'id'
                ),
                array(
                    'caption'   => Mage::helper('M2ePro')->__('Delete'),
                    'url'       => array(
                        'base' => '*/adminhtml_ebay_accountPickupStore/delete'
                    ),
                    'field'     => 'id',
                    'confirm'  => Mage::helper('M2ePro')->__('Are you sure?')
                )
            )
        ));

        return parent::_prepareColumns();
    }

    //########################################

    protected function callbackFilterMarketplace($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('main_table.marketplace_id = ?', (int)$value);
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/*/pickupStoreGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/adminhtml_ebay_accountPickupStore/edit', array('id' => $row->getData('id')));
    }

    //########################################

    private function getEnabledMarketplaceTitles()
    {
        $marketplaceCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
                            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK)
                            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                            ->addFieldToFilter('is_in_store_pickup', 1)
                            ->setOrder('sorder', 'ASC');

        $pickupStoreHelper = Mage::helper('M2ePro/Component_Ebay_PickupStore');
        $options = array();
        foreach ($marketplaceCollection as $marketplace) {
            $countryData = $pickupStoreHelper->convertMarketplaceToCountry(
                $marketplace->getData()
            );
            $options[$marketplace->getData('id')] = $countryData['name'];
        }

        return $options;
    }

    //########################################
}