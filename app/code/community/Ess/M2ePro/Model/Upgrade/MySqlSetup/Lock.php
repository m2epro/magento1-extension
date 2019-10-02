<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup_Lock
{
    const LOCK_FILE_LIFETIME = 300;

    protected $_lockId;

    //########################################

    public function __construct()
    {
        $this->_lockId = sha1(microtime(1));
    }

    //########################################

    public function getLock()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->lock();

        // double running protection
        usleep(1000000); // 1 sec

        if ($this->isLocked() && !$this->isLockOwner()) {
            return false;
        }

        return true;
    }

    public function activateLock()
    {
        if (!$this->getLock()) {
            return;
        }

        $this->lock();
    }

    public function releaseLock()
    {
        $this->unlock();
        return true;
    }

    //########################################

    protected function isLocked()
    {
        if (!$this->isLockFileExists()) {
            return false;
        }

        if (@filemtime($this->getLockFilePath()) > ((int)gmdate('U') - self::LOCK_FILE_LIFETIME)) {
            return true;
        }

        $this->unlock();
        return false;
    }

    protected function lock()
    {
        if (!@is_dir($this->getLocksDirPath())) {
            @mkdir($this->getLocksDirPath(), 0777, true);
        }

        @file_put_contents($this->getLockFilePath(), $this->_lockId);

        register_shutdown_function(
            function () {
            @unlink(Mage::getBaseDir('var').DS.'locks'.DS.'m2epro_setup.lock');
            }
        );
    }

    protected function unlock()
    {
        $this->isLockFileExists() && @unlink($this->getLockFilePath());
    }

    protected function isLockOwner()
    {
        if (!$this->isLockFileExists()) {
            return false;
        }

        return $this->_lockId == @file_get_contents($this->getLockFilePath());
    }

    //########################################

    protected function getLocksDirPath()
    {
        return Mage::getBaseDir('var') . DS . 'locks';
    }

    protected function getLockFilePath()
    {
        return rtrim($this->getLocksDirPath(), DS) . DS . 'm2epro_setup.lock';
    }

    protected function isLockFileExists()
    {
        return @file_exists($this->getLockFilePath());
    }

    //########################################
}
