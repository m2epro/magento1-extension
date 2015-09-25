<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_LockItem extends Ess_M2ePro_Model_Abstract
{
    private $nick = 'undefined';
    private $maxInactiveTime = 1800; // 30 min

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/LockItem');
    }

    //####################################

    public function setNick($value)
    {
        $this->nick = $value;
    }

    public function getNick()
    {
        return $this->nick;
    }

    // -----------------------------------

    public function setMaxInactiveTime($value)
    {
        $this->maxInactiveTime = (int)$value;
    }

    public function getMaxInactiveTime()
    {
        return $this->maxInactiveTime;
    }

    //####################################

    public function create($parentId = NULL)
    {
        $data = array(
            'nick' => $this->nick,
            'parent_id' => $parentId
        );

        Mage::getModel('M2ePro/LockItem')->setData($data)->save();

        return true;
    }

    public function remove()
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return false;
        }

        $childrenCollection = Mage::getModel('M2ePro/LockItem')->getCollection();
        $childrenCollection->addFieldToFilter('parent_id', $lockModel->getId());

        foreach ($childrenCollection->getItems() as $childLockModel) {
            /** @var $childLockModel Ess_M2ePro_Model_LockItem **/
            $childLockModel = Mage::getModel('M2ePro/LockItem')->load($childLockModel->getId());
            $childLockModel->setNick($childLockModel->getData('nick'));
            $childLockModel->getId() && $childLockModel->remove();
        }

        $lockModel->delete();

        return true;
    }

    //-----------------------------------

    public function isExist()
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return false;
        }

        $currentTimestamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $updateTimestamp = strtotime($lockModel->getData('update_date'));

        if ($updateTimestamp < $currentTimestamp - $this->getMaxInactiveTime()) {
            $lockModel->delete();
            return false;
        }

        return true;
    }

    public function activate()
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return false;
        }

        $parentId = $lockModel->getData('parent_id');

        if (!is_null($parentId)) {
            /** @var $parentLockModel Ess_M2ePro_Model_LockItem **/
            $parentLockModel = Mage::getModel('M2ePro/LockItem')->load($parentId);
            $parentLockModel->setNick($parentLockModel->getData('nick'));
            $parentLockModel->getId() && $parentLockModel->activate();
        }

        if ($lockModel->getData('kill_now')) {
            $this->remove();
            exit('kill now.');
        }

        $lockModel->setData('data',$lockModel->getData('data'))->save();

        return true;
    }

    //####################################

    public function getRealId()
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');
        return $lockModel->getId() ? $lockModel->getId() : NULL;
    }

    //-----------------------------------

    public function addContentData($key, $value)
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return false;
        }

        $data = $lockModel->getData('data');
        if (!empty($data)) {
            $data = json_decode($data, true);
        } else {
            $data = array();
        }

        $data[$key] = $value;

        $lockModel->setData('data', json_encode($data));
        $lockModel->save();

        return true;
    }

    public function setContentData(array $data)
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return false;
        }

        $lockModel->setData('data',json_encode($data))->save();

        return true;
    }

    //-----------------------------------

    public function getContentData($key = NULL)
    {
        /** @var $lockModel Ess_M2ePro_Model_LockItem **/
        $lockModel = Mage::getModel('M2ePro/LockItem')->load($this->nick,'nick');

        if (!$lockModel->getId()) {
            return NULL;
        }

        if ($lockModel->getData('data') == '') {
            return NULL;
        }

        $data = json_decode($lockModel->getData('data'),true);

        if (is_null($key)) {
            return $data;
        }

        if (isset($data[$key])) {
            return $data[$key];
        }

        return NULL;
    }

    //####################################

    public function makeShutdownFunction()
    {
        if (!$this->isExist()) {
            return false;
        }

        $functionCode = "\$object = Mage::getModel('M2ePro/LockItem');
                         \$object->setNick('".$this->nick."');
                         \$object->remove();";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}