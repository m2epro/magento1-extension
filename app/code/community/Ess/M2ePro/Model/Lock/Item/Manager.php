<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Lock_Item_Manager
{
    const DEFAULT_MAX_INACTIVE_TIME = 900;

    protected $_nick = null;

    //########################################

    public function __construct($args)
    {
        if (empty($args['nick'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Nick is not defined.');
        }

        $this->_nick = $args['nick'];
    }

    //########################################

    public function getNick()
    {
        return $this->_nick;
    }

    //########################################

    public function create($parentNick = NULL)
    {
        $parentLockItem = Mage::getModel('M2ePro/Lock_Item');
        if ($parentNick !== null) {
            $parentLockItem->load($parentNick, 'nick');
        }

        $data = array(
            'nick'      => $this->_nick,
            'parent_id' => $parentLockItem->getId(),
        );

        /** @var $lockItem Ess_M2ePro_Model_Lock_Item **/
        $lockItem = Mage::getModel('M2ePro/Lock_Item')->setData($data);
        $lockItem->save();

        return $this;
    }

    public function remove()
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return false;
        }

        $childLockItemCollection = Mage::getModel('M2ePro/Lock_Item')->getCollection();
        $childLockItemCollection->addFieldToFilter('parent_id', $lockItem->getId());

        /** @var Ess_M2ePro_Model_Lock_Item[] $childLockItems */
        $childLockItems = $childLockItemCollection->getItems();

        foreach ($childLockItems as $childLockItem) {
            $childManager = Mage::getModel('M2ePro/Lock_Item_Manager', array('nick' => $childLockItem->getNick()));
            $childManager->remove();
        }

        $lockItem->delete();

        return true;
    }

    // ---------------------------------------

    public function isExist()
    {
        return $this->getLockItemObject() !== null;
    }

    public function isInactiveMoreThanSeconds($maxInactiveInterval)
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return true;
        }

        $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $updateTimestamp  = strtotime($lockItem->getUpdateDate());

        if ($updateTimestamp < $currentTimestamp - $maxInactiveInterval) {
            return true;
        }

        return false;
    }

    public function activate()
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new Ess_M2ePro_Model_Exception(
                sprintf(
                    'Lock Item with nick "%s" does not exist.', $this->_nick
                )
            );
        }

        if ($lockItem->getParentId() !== null) {

            /** @var Ess_M2ePro_Model_Lock_Item $parentLockItem */
            $parentLockItem = Mage::getModel('M2ePro/Lock_Item')->load($lockItem->getParentId());

            if ($parentLockItem->getId()) {
                /** @var $parentManager Ess_M2ePro_Model_Lock_Item_Manager **/
                $parentManager = Mage::getModel(
                    'M2ePro/Lock_Item_Manager', array('nick' => $parentLockItem->getNick())
                );
                $parentManager->activate();
            }
        }

        $lockItem->setDataChanges(true);
        $lockItem->save();

        return $this;
    }

    //########################################

    public function addContentData($key, $value)
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new Ess_M2ePro_Model_Exception(
                sprintf(
                    'Lock Item with nick "%s" does not exist.', $this->_nick
                )
            );
        }

        $data = $lockItem->getContentData();
        if (!empty($data)) {
            $data = Mage::helper('M2ePro')->jsonDecode($data);
        } else {
            $data = array();
        }

        $data[$key] = $value;

        $lockItem->setData('data', Mage::helper('M2ePro')->jsonEncode($data));
        $lockItem->save();

        return $this;
    }

    public function setContentData(array $data)
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            throw new Ess_M2ePro_Model_Exception(
                sprintf(
                    'Lock Item with nick "%s" does not exist.', $this->_nick
                )
            );
        }

        $lockItem->setData('data', Mage::helper('M2ePro')->jsonEncode($data));
        $lockItem->save();

        return true;
    }

    // ---------------------------------------

    public function getContentData($key = NULL)
    {
        $lockItem = $this->getLockItemObject();
        if ($lockItem === null) {
            return NULL;
        }

        if ($lockItem->getData('data') == '') {
            return NULL;
        }

        $data = Mage::helper('M2ePro')->jsonDecode($lockItem->getContentData());
        if ($key === null) {
            return $data;
        }

        if (isset($data[$key])) {
            return $data[$key];
        }

        return NULL;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Lock_Item
     */
    protected function getLockItemObject()
    {
        $lockItemCollection = Mage::getModel('M2ePro/Lock_Item')->getCollection();
        $lockItemCollection->addFieldToFilter('nick', $this->_nick);

        /** @var Ess_M2ePro_Model_Lock_Item $lockItem */
        $lockItem = $lockItemCollection->getFirstItem();

        return $lockItem->getId() ? $lockItem : NULL;
    }

    //########################################
}
