<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Client extends Mage_Core_Helper_Abstract
{
    const API_APACHE_HANDLER = 'apache2handler';

    //########################################

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/location/', 'domain');
        if (empty($domain)) {
            $domain = $this->getServerDomain();
        }

        if (empty($domain)) {
            throw new Ess_M2ePro_Model_Exception('Server domain is not defined');
        }

        return $domain;
    }

    public function getIp()
    {
        $ip = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/location/', 'ip');
        if (empty($ip)) {
            $ip = $this->getServerIp();
        }

        if (empty($ip)) {
            throw new Ess_M2ePro_Model_Exception('Server IP is not defined');
        }

        return $ip;
    }

    public function getBaseDirectory()
    {
        return Mage::getBaseDir();
    }

    // ---------------------------------------

    public function updateLocationData($forceUpdate = false)
    {
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/location/date_last_check/');

        $dateLastCheck = $registry->getValue();
        if ($dateLastCheck === null) {
            $dateLastCheck = Mage::helper('M2ePro')->getCurrentGmtDate(true)-60*60*365;
        } else {
            $dateLastCheck = strtotime($dateLastCheck);
        }

        if (!$forceUpdate && Mage::helper('M2ePro')->getCurrentGmtDate(true) < $dateLastCheck + 60*60*24) {
            return;
        }

        $registry->setValue($dateLastCheck)->save();

        $domain = $this->getServerDomain();
        if (null === $domain) {
            $domain = '127.0.0.1';
        }

        $ip = $this->getServerIp();
        if (null === $ip) {
            $ip = '127.0.0.1';
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/location/', 'domain', $domain);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/location/', 'ip', $ip);
    }

    protected function getServerDomain()
    {
        $server = Mage::app()->getRequest()->getServer();

        if (!isset($server['HTTP_HOST'])) {
            return null;
        }

        $domain = rtrim($server['HTTP_HOST'], '/');
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }

        return strtolower(trim($domain));
    }

    protected function getServerIp()
    {
        $server = Mage::app()->getRequest()->getServer();

        $ip = null;
        if (isset($server['SERVER_ADDR'])) {
            $ip = $server['SERVER_ADDR'];
        } elseif (isset($server['LOCAL_ADDR'])) {
            $ip = $server['LOCAL_ADDR'];
        }

        return null !== $ip ? strtolower(trim($ip)) : $ip;
    }

    //########################################

    public function getSystem()
    {
        return PHP_OS;
    }

    // ---------------------------------------

    public function getPhpVersion($asArray = false)
    {
        $version = array(
            PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION
        );

        return $asArray ? $version : implode('.', $version);
    }

    public function getPhpApiName()
    {
        return PHP_SAPI;
    }

    public function getPhpIniFileLoaded()
    {
        return @php_ini_loaded_file();
    }

    // ---------------------------------------

    public function isPhpApiApacheHandler()
    {
        return $this->getPhpApiName() == self::API_APACHE_HANDLER;
    }

    public function isPhpApiFastCgi()
    {
        return !$this->isPhpApiApacheHandler();
    }

    // ---------------------------------------

    public function getPhpSettings()
    {
        return array(
            'memory_limit'       => $this->getMemoryLimit(),
            'max_execution_time' => $this->getExecutionTime(),
            'phpinfo'            => $this->getPhpInfoArray()
        );
    }

    public function getPhpInfoArray()
    {
        if (in_array('phpinfo', $this->getDisabledFunctions())) {
            return array();
        }

        try {
            ob_start(); phpinfo(INFO_ALL);

            $pi = preg_replace(
                array(
                '#^.*<body>(.*)</body>.*$#m', '#<h2>PHP License</h2>.*$#ms',
                '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
                "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
                '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
                "# +#", '#<tr>#', '#</tr>#'),
                array(
                '$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
                '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
                "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
                '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
                '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
                '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'
                ), ob_get_clean()
            );

            $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
            unset($sections[0]);

            $pi = array();
            foreach ($sections as $section) {
                $n = substr($section, 0, strpos($section, '</h2>'));
                preg_match_all(
                    '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
                    $section,
                    $askapache,
                    PREG_SET_ORDER
                );
                foreach ($askapache as $m) {
                    if (!isset($m[0]) || !isset($m[1]) || !isset($m[2])) {
                        continue;
                    }

                    $pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m, 2);
                }
            }
        } catch (Exception $exception) {
            return array();
        }

        return $pi;
    }

    // ---------------------------------------

    public function getMysqlVersion()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_read')->getServerVersion();
    }

    public function getMysqlApiName()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read')->getConnection();
        return $connection instanceof PDO ? $connection->getAttribute(PDO::ATTR_CLIENT_VERSION) : 'N/A';
    }

    public function getMysqlSettings()
    {
        $sqlQuery = "SHOW VARIABLES
                     WHERE `Variable_name` IN ('connect_timeout','wait_timeout')";

        $settingsArray = Mage::getSingleton('core/resource')
                            ->getConnection('core_read')
                            ->fetchAll($sqlQuery);

        $settings = array();
        foreach ($settingsArray as $settingItem) {
            $settings[$settingItem['Variable_name']] = $settingItem['Value'];
        }

        $phpInfo = $this->getPhpInfoArray();
        $settings = array_merge($settings, isset($phpInfo['mysql'])?$phpInfo['mysql']:array());

        return $settings;
    }

    public function getMysqlTotals()
    {
        $moduleTables = Mage::helper('M2ePro/Module_Database_Structure')->getModuleTables();
        $magentoTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $totalRecords = 0;
        foreach ($moduleTables as $moduleTable) {
            $moduleTable = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($moduleTable);

            if (!in_array($moduleTable, $magentoTables)) {
                continue;
            };
            $dbSelect = $connRead->select()->from($moduleTable, new Zend_Db_Expr('COUNT(*)'));
            $totalRecords += (int)$connRead->fetchOne($dbSelect);
        }

        return array(
            'magento_tables' => count($magentoTables),
            'module_tables' => count($moduleTables),
            'module_records' => $totalRecords
        );
    }

    //########################################

    public function getMemoryLimit($inMegabytes = true)
    {
        $memoryLimit = trim(ini_get('memory_limit'));

        if ($memoryLimit == '') {
            return 0;
        }

        $lastMemoryLimitLetter = strtolower(substr($memoryLimit, -1));
        $memoryLimit = (int)$memoryLimit;

        switch($lastMemoryLimitLetter) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }

        if ($memoryLimit > 0 && $inMegabytes) {
            $memoryLimit /= 1024 * 1024;
        }

        return $memoryLimit;
    }

    public function setMemoryLimit($maxSize = 512)
    {
        $minSize = 32;
        $currentMemoryLimit = $this->getMemoryLimit();

        if ($maxSize < $minSize || (int)$currentMemoryLimit >= $maxSize || (float)$currentMemoryLimit <= 0) {
            return false;
        }

        // @codingStandardsIgnoreStart
        for ($i=$minSize; $i<=$maxSize; $i*=2) {
            if (@ini_set('memory_limit', "{$i}M") === false) {
                if ($i == $minSize) {
                    return false;
                } else {
                    return $i/2;
                }
            }
        }
        // @codingStandardsIgnoreEnd

        return true;
    }

    public function testMemoryLimit($bytes = null)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/tools/memory-limit/test/');
        $registry->setValue(null)->save();

        $i = 0;
        $array = array();

        // @codingStandardsIgnoreStart
        while (($usage = memory_get_usage(true)) < $bytes || $bytes === null) {
            $array[] = $array;
            if (++$i % 100 === 0) {
                $registry->setValue($usage);
                $registry->save();
            }
        }
        // @codingStandardsIgnoreEnd

        return $usage;
    }

    public function getTestedMemoryLimit()
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/tools/memory-limit/test/');
        return $registry->getValue();
    }

    // ---------------------------------------

    public function getExecutionTime()
    {
        if ($this->isPhpApiFastCgi()) {
            return null;
        }

        // @codingStandardsIgnoreLine
        return @ini_get('max_execution_time');
    }

    public function testExecutionTime($seconds)
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/tools/execution-time/test/');
        $registry->setValue(null)->save();

        $i = 0;

        // @codingStandardsIgnoreStart
        while ($i < $seconds) {
            sleep(1);
            if (++$i % 10 === 0) {
                $registry->setValue($i);
                $registry->save();
            }
        }
        // @codingStandardsIgnoreEnd

        $registry->setValue($seconds);
        $registry->save();

        return $i;
    }

    public function getTestedExecutionTime()
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/tools/execution-time/test/');
        return $registry->getValue();
    }

    // ---------------------------------------

    public function updateMySqlConnection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        try {
            // @codingStandardsIgnoreLine
            $connRead->query('SELECT 1');
        } catch (Exception $exception) {
            $connRead->closeConnection();
        }
    }

    //########################################

    public function getDisabledFunctions()
    {
        return array_filter(explode(',', ini_get('disable_functions')));
    }

    //########################################
}
