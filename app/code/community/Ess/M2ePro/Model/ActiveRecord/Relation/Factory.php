<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ActiveRecord_Relation_Factory
{
    /** @var Ess_M2ePro_Model_ActiveRecord_Factory */
    protected $_activeRecordFactory;

    //########################################

    public function __construct()
    {
        $this->_activeRecordFactory = Mage::getSingleton('M2ePro/ActiveRecord_Factory');
    }

    //########################################

    /**
     * @param $component
     * @param $parentModelName
     * @return false|Ess_M2ePro_Model_ActiveRecord_Relation
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObject($component, $parentModelName)
    {
        if (!in_array($component, Mage::helper('M2ePro/Component')->getComponents(), true)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Unknown component nick ' . $component);
        }

        $parentModel = $this->_activeRecordFactory->getObject($parentModelName);
        $parentModel->setData('component_mode', $component);

        return Mage::getModel(
            'M2ePro/ActiveRecord_Relation',
            array(
                $parentModel,
                $this->_activeRecordFactory->getObject(ucfirst($component) .'_'. $parentModelName)
            )
        );
    }

    /**
     * @param $component
     * @param $modelName
     * @return Ess_M2ePro_Model_Resource_ActiveRecord_Relation_Collection
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObjectCollection($component, $modelName)
    {
        return $this->getObject($component, $modelName)->getCollection();
    }

    /**
     * @param $component
     * @param $modelName
     * @param $value
     * @param null $field
     * @param bool $throwException
     * @return Ess_M2ePro_Model_ActiveRecord_Relation|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getObjectLoaded($component, $modelName, $value, $field = null, $throwException = true)
    {
        try {
            return $this->getObject($component, $modelName)->loadInstance($value, $field);
        } catch (Ess_M2ePro_Model_Exception_Logic $e) {
            if ($throwException) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * @param $component
     * @param $modelName
     * @param $value
     * @param null $field
     * @param bool $throwException
     * @return Ess_M2ePro_Model_ActiveRecord_Relation|null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getCachedObjectLoaded($component, $modelName, $value, $field = null, $throwException = true)
    {
        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return $this->getObjectLoaded($component, $modelName, $value, $field, $throwException);
        }

        $parentModel = $this->_activeRecordFactory->getCachedObjectLoaded(
            $modelName,
            $value,
            $field,
            $throwException
        );

        $childModel = $this->_activeRecordFactory->getCachedObjectLoaded(
            ucfirst($component) . '_' . $modelName,
            $parentModel->getId(),
            null,
            $throwException
        );

        return Mage::getModel('M2ePro/ActiveRecord_Relation', array($parentModel, $childModel));
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract $parent
     * @return Ess_M2ePro_Model_ActiveRecord_Relation
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getByParent(Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract $parent)
    {
        if (null === $parent->getComponentMode()) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Relation object require `component_mode` from ' . $parent->getObjectModelName()
            );
        }

        /** @var Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract $child */
        $child = $this->_activeRecordFactory->getObject(
            ucfirst($parent->getComponentMode()) .'_'. $parent->getObjectModelName()
        );

        if (null !== $parent->getId()) {
            $child->loadInstance($parent->getId());
        }

        return Mage::getModel('M2ePro/ActiveRecord_Relation', array($parent, $child));
    }

    /**
     * @param Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract $child
     * @return Ess_M2ePro_Model_ActiveRecord_Relation
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getByChild(Ess_M2ePro_Model_ActiveRecord_Relation_ChildAbstract $child)
    {
        if (null === $child->getComponentMode()) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'Relation object require `component_mode` from ' . $child->getObjectModelName()
            );
        }

        /** @var Ess_M2ePro_Model_ActiveRecord_Relation_ParentAbstract $parent */
        $parent = $this->_activeRecordFactory->getObject(
            str_replace($child->getComponentMode(), '', $child->getObjectModelName())
        );

        if (null === $child->getId()) {
            $parent->loadInstance($child->getId());
        }

        return Mage::getModel('M2ePro/ActiveRecord_Relation', array($parent, $child));
    }

    //########################################
}
