<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup_Autoloader
{
    static private $isRegistered = false;

    //########################################

    public function register()
    {
        if (self::$isRegistered) {
            return;
        }

        spl_autoload_register(array($this, 'autoload'), true, true);
        self::$isRegistered = true;
    }

    //########################################

    protected function autoload($className)
    {
        preg_match(
            '/Ess_M2ePro_Sql_(Update|Install|Upgrade)(_(v[\d_]+__v[\d_]+|y\d{2}_m\d{2}))?_(.+)/',
            $className, $verMatches
        );

        if (empty($verMatches[1]) || empty($verMatches[4])) {
            return;
        }

        unset($verMatches[0]);
        unset($verMatches[2]);
        $verMatches[4] = str_replace('_', '/', $verMatches[4]);
        $verMatches = array_filter($verMatches);

        $classFile = sprintf('Ess/M2ePro/sql/%s', implode('/', $verMatches));
        if (is_file($classFile)) {
            return;
        }

        require $classFile . '.php';
    }

    //########################################
}