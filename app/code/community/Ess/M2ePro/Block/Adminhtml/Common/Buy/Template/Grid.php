<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_Grid
    extends Ess_M2ePro_Block_Adminhtml_Common_Template_Grid
{
    protected $nick = Ess_M2ePro_Helper_Component_Buy::NICK;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('commonBuyTemplateGrid');
        // ---------------------------------------
    }

    //########################################

    protected function _prepareCollection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        // Prepare selling format collection
        // ---------------------------------------
        $collectionSellingFormat = Mage::getModel('M2ePro/Template_SellingFormat')->getCollection();
        $collectionSellingFormat->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSellingFormat->getSelect()->columns(
            array('id as template_id', 'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SELLING_FORMAT.'\' as `type`'),
                'create_date', 'update_date')
        );
        $collectionSellingFormat->getSelect()->where('component_mode = (?)', $this->nick);
        // ---------------------------------------

        // Prepare synchronization collection
        // ---------------------------------------
        $collectionSynchronization = Mage::getModel('M2ePro/Template_Synchronization')->getCollection();
        $collectionSynchronization->getSelect()->reset(Varien_Db_Select::COLUMNS);
        $collectionSynchronization->getSelect()->columns(
            array('id as template_id', 'title',
                new Zend_Db_Expr('\''.self::TEMPLATE_SYNCHRONIZATION.'\' as `type`'),
                'create_date', 'update_date')
        );
        $collectionSynchronization->getSelect()->where('component_mode = (?)', $this->nick);
        // ---------------------------------------

        // Prepare union select
        // ---------------------------------------
        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $collectionSellingFormat->getSelect(),
            $collectionSynchronization->getSelect()
        ));
        // ---------------------------------------

        // Prepare result collection
        // ---------------------------------------
        $resultCollection = new Varien_Data_Collection_Db($connRead);
        $resultCollection->getSelect()->reset()->from(
            array('main_table' => $unionSelect),
            array('template_id', 'title', 'type', 'create_date', 'update_date')
        );
        // ---------------------------------------

//        echo $resultCollection->getSelectSql(true); exit;

        $this->setCollection($resultCollection);

        return parent::_prepareCollection();
    }

    //########################################

    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/adminhtml_common_template/edit',
            array(
                'id' => $row->getData('template_id'),
                'type' => $row->getData('type'),
                'back' => 1
            )
        );
    }

    //########################################
}