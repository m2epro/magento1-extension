<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_ActiveRecord_Relation[] getItems()
 * @method Ess_M2ePro_Model_ActiveRecord_Relation[] getItemsByColumnValue($column, $value)
 * @method Ess_M2ePro_Model_ActiveRecord_Relation getFirstItem()
 * @method Ess_M2ePro_Model_ActiveRecord_Relation getLastItem()
 * @method Ess_M2ePro_Model_ActiveRecord_Relation getItemByColumnValue($column, $value)
 * @method Ess_M2ePro_Model_ActiveRecord_Relation getItemById($idValue)
 * @method Ess_M2ePro_Model_Resource_ActiveRecord_Relation_Collection addFieldToFilter($field, $condition = null)
 * @method Ess_M2ePro_Model_Resource_ActiveRecord_Relation_Collection setOrder($field, $direction)
 */
class Ess_M2ePro_Model_Resource_ActiveRecord_Relation_Collection
    extends Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Relation */
    protected $_relationModel;

    //########################################

    public function __construct($arguments = NULL)
    {
        list($resource, $relationModel) = $arguments;

        if (empty($relationModel) || !($relationModel instanceof Ess_M2ePro_Model_ActiveRecord_Relation)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Require Ess_M2ePro_Model_Relation model.');
        }

        $this->_relationModel = $relationModel;
        parent::__construct($resource);
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/ActiveRecord_Relation');
    }

    //########################################

    public function getMainTable()
    {
        return $this->_relationModel->getParentObject()->getResource()->getMainTable();
    }

    public function getSecondTable()
    {
        return $this->_relationModel->getChildObject()->getResource()->getMainTable();
    }

    public function getNewEmptyItem()
    {
        $childModel = $this->_activeRecordFactory->getObject(
            $this->_relationModel->getChildObject()->getObjectModelName()
        );

        $parentModel = $this->_activeRecordFactory->getObject(
            $this->_relationModel->getParentObject()->getObjectModelName()
        );

        return Mage::getModel('M2ePro/ActiveRecord_Relation', array($parentModel, $childModel));
    }

    /**
     * @return Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract[]
     */
    public function getParentItems()
    {
        $items = array();
        foreach ($this->getItems() as $item) {
            $parentModel = $item->getParentObject();
            $item[$parentModel->getId()] = $parentModel;
        }

        return $items;
    }

    /**
     * @return Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract[]
     */
    public function getChildItems()
    {
        $items = array();
        foreach ($this->getItems() as $item) {
            $childModel = $item->getChildObject();
            $item[$childModel->getId()] = $childModel;
        }

        return $items;
    }

    //########################################

    protected function _initSelect()
    {
        parent::_initSelect();

        $primaryKey = $this->_relationModel->getParentObject()->getResource()->getIdFieldName();
        $foreignKey = $this->_relationModel->getRelationKey();

        $this->getSelect()->join(
            array('second_table' => $this->getSecondTable()),
            "`second_table`.`{$foreignKey}` = `main_table`.`{$primaryKey}`"
        );

        return $this;
    }

    protected function _initSelectFields()
    {
        parent::_initSelectFields();

        $removeChildTableWildcard = false;
        $columns = array();
        foreach ($this->_select->getPart(Zend_Db_Select::COLUMNS) as $fieldData) {
            list($tableAlias, $fieldName, $alias) = $fieldData;
            /**
             * By default addFieldToSelect() method set all fields under main_table
             * We need split main_table and second table fields
             */
            if ($tableAlias === 'main_table') {
                if ($this->getResource()->isModelContainField($this->_relationModel->getChildObject(), $fieldName)) {
                    $columns[] = array('second_table', $fieldName, $alias);
                    $removeChildTableWildcard = true;
                    continue;
                }
            }

            $columns[] = $fieldData;
        }

        if ($removeChildTableWildcard) {
            array_splice(
                $columns,
                array_search(array('second_table', '*'), $columns),
                1
            );
        }

        $this->_select->setPart(Zend_Db_Select::COLUMNS, $columns);
        return $this;
    }

    //########################################
}
