<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Client extends Mage_Core_Helper_Abstract
{
    const API_APACHE_HANDLER = 'apache2handler';

    //########################################

    public function getHost()
    {
        $domain = $this->getDomain();
        return $domain == '' ? $this->getIp() : $domain;
    }

    // ---------------------------------------

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue('/location_info/', 'domain');
        if (is_null($domain) && isset($_SERVER['HTTP_HOST'])) {
            $domain = rtrim($_SERVER['HTTP_HOST'], '/');
        }

        if (!is_null($domain)) {
            strpos($domain,'www.') === 0 && $domain = substr($domain,4);
            return strtolower(trim($domain));
        }

        throw new Ess_M2ePro_Model_Exception('Server domain is not defined');
    }

    public function getIp()
    {
        $backupIp = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue('/location_info/', 'ip');

        if (!is_null($backupIp)) {
            return strtolower(trim($backupIp));
        }

        $serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : NULL;
        is_null($serverIp) && $serverIp = isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : NULL;

        if (!is_null($serverIp)) {
            return strtolower(trim($serverIp));
        }

        throw new Ess_M2ePro_Model_Exception('Server IP is not defined');
    }

    public function getBaseDirectory()
    {
        $backupDirectory = Mage::helper('M2ePro/Module')->getCacheConfig()
                                    ->getGroupValue('/location_info/', 'directory');

        if (!is_null($backupDirectory)) {
            return $backupDirectory;
        }

        return Mage::getBaseDir();
    }

    public function isBrowserIE()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
            return true;
        }
        return false;
    }

    // ---------------------------------------

    public function updateBackupConnectionData($forceUpdate = false)
    {
        $dateLastCheck = Mage::helper('M2ePro/Module')->getCacheConfig()
                                ->getGroupValue('/location_info/', 'date_last_check');

        if (is_null($dateLastCheck)) {
            $dateLastCheck = Mage::helper('M2ePro')->getCurrentGmtDate(true)-60*60*365;
        } else {
            $dateLastCheck = strtotime($dateLastCheck);
        }

        if (!$forceUpdate && Mage::helper('M2ePro')->getCurrentGmtDate(true) < $dateLastCheck + 60*60*24) {
            return;
        }

        $domainBackup = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '127.0.0.1';
        strpos($domainBackup,'www.') === 0 && $domainBackup = substr($domainBackup,4);
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/location_info/', 'domain', $domainBackup);

        $ipBackup = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : NULL;
        is_null($ipBackup) && $ipBackup = isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : '127.0.0.1';
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/location_info/', 'ip', $ipBackup);

        $directoryBackup = Mage::getBaseDir();
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/location_info/', 'directory', $directoryBackup);

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            '/location_info/', 'date_last_check', Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    //########################################

    public function getSystem()
    {
        return php_uname();
    }

    // ---------------------------------------

    public function getPhpVersion()
    {
        return @phpversion();
    }

    public function getPhpApiName()
    {
        return @php_sapi_name();
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
            'memory_limit' => $this->getMemoryLimit(),
            'max_execution_time' => $this->isPhpApiApacheHandler() ? @ini_get('max_execution_time') : null,
            'phpinfo' => $this->getPhpInfoArray()
        );
    }

    public function getPhpInfoArray()
    {
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
                    $pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
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
        $settings = array_merge($settings,isset($phpInfo['mysql'])?$phpInfo['mysql']:array());

        return $settings;
    }

    public function getMysqlTotals()
    {
        $moduleTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();
        $magentoTables = Mage::helper('M2ePro/Magento')->getMySqlTables();

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $totalRecords = 0;
        foreach ($moduleTables as $moduleTable) {
            $moduleTable = Mage::getSingleton('core/resource')->getTableName($moduleTable);

            if (!in_array($moduleTable, $magentoTables)) {
                continue;
            };
            $dbSelect = $connRead->select()->from($moduleTable,new Zend_Db_Expr('COUNT(*)'));
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
        switch($lastMemoryLimitLetter) {
            case 'g':
                $memoryLimit *= 1024;
            case 'm':
                $memoryLimit *= 1024;
            case 'k':
                $memoryLimit *= 1024;
        }

        if ($inMegabytes) {
            $memoryLimit /= 1024 * 1024;
        }

        return $memoryLimit;
    }

    public function setMemoryLimit($maxSize = 512)
    {
        $minSize = 32;
        $currentMemoryLimit = $this->getMemoryLimit();

        if ($maxSize < $minSize || (int)$currentMemoryLimit >= $maxSize) {
            return false;
        }

        for ($i=$minSize; $i<=$maxSize; $i*=2) {

            if (@ini_set('memory_limit',"{$i}M") === false) {
                if ($i == $minSize) {
                    return false;
                } else {
                    return $i/2;
                }
            }
        }

        return true;
    }

    // ---------------------------------------

    public function updateMySqlConnection()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        try {
            $connRead->query('SELECT 1');
        } catch (Exception $exception) {
            $connRead->closeConnection();
        }
    }

    //########################################
}