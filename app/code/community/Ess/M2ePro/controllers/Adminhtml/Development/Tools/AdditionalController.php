<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Tools_AdditionalController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Memory Limit Test"
     * @description "Memory Limit Test"
     * @confirm "Are you sure?"
     */
    public function testMemoryLimitAction()
    {
        ini_set('display_errors', 1);

        $dir = Mage::getBaseDir('var') . DS . 'log' . DS;
        $file = 'm2epro_memory_limit.log';

        is_file($dir . $file) && unlink($dir . $file);

        $i = 0;
        $array = array();

        while (1) {
            ($array[] = $array) && ((++$i % 100) == 0) && Mage::log(memory_get_usage(true) / 1000000 ,null,$file,1);
        }
    }

    /**
     * @title "Execution Time Test"
     * @description "Execution Time Test"
     * @new_line
     */
    public function testExecutionTimeAction()
    {
        ini_set('display_errors', 1);

        $seconds = (int)$this->getRequest()->getParam('seconds', null);

        $logDir = Mage::getBaseDir('var').DS.'log'.DS;
        $fileName = 'm2epro_execution_time.log';

        $isLogFileExists = is_file($logDir . $fileName);

        if ($seconds) {

            $isLogFileExists && unlink($logDir . $fileName);

            $i = 0;
            while ($i < $seconds) {
                sleep(1);
                ((++$i % 10) == 0) && Mage::log("{$i} seconds passed",null,$fileName,1);
            }

            echo "<div>{$seconds} seconds passed successfully!</div><br/>";
        }

        if ($isLogFileExists) {

            $contentsRows = explode("\n",file_get_contents($logDir . $fileName));

            if (count($contentsRows) >= 2) {
                $lastRecord = trim($contentsRows[count($contentsRows)-2], "\r\n");
                echo "<button onclick=\"alert('{$lastRecord}')\">show prev. log</button>";
            }
        }

        $url = Mage::helper('adminhtml')->getUrl('*/*/*');

        return print <<<HTML
<form action="{$url}" method="get">
    <input type="text" name="seconds" class="input-text" value="180" style="text-align: right; width: 100px" />
    <button type="submit">Test</button>
</form>
HTML;
    }

    /**
     * @title "Clear Opcode"
     * @description "Clear Opcode (APC and Zend Optcache Extension)"
     */
    public function clearOpcodeAction()
    {
        $messages = array();

        if (!Mage::helper('M2ePro/Client_Cache')->isApcAvailable() &&
            !Mage::helper('M2ePro/Client_Cache')->isZendOpcacheAvailable()) {

            $this->_getSession()->addError('Opcode extensions are not installed.');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
            return;
        }

        if (Mage::helper('M2ePro/Client_Cache')->isApcAvailable()) {
            $messages[] = 'APC opcode';
            apc_clear_cache('system');
        }

        if (Mage::helper('M2ePro/Client_Cache')->isZendOpcacheAvailable()) {
            $messages[] = 'Zend Optcache';
            opcache_reset();
        }

        $this->_getSession()->addSuccess(implode(' and ', $messages) . ' caches are cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear COOKIES"
     * @description "Clear all current cookies"
     * @confirm "Are you sure?"
     */
    public function clearCookiesAction()
    {
        foreach ($_COOKIE as $name => $value) {
            setcookie($name, '', 0, '/');
        }
        $this->_getSession()->addSuccess('Cookies was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //########################################
}