<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function __construct(
        $resource = null
    ) {
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
        parent::__construct($resource);
    }

    //########################################

    /**
     * @param null|array $columns
     * @return $this
     */
    public function setColumns($columns = null)
    {
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $columns && $this->getSelect()->columns($columns);

        return $this;
    }

    //########################################
}
