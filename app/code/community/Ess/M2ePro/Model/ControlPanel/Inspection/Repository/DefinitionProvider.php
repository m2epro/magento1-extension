<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Repository_DefinitionProvider
{
    const GROUP_ORDERS    = 'orders';
    const GROUP_PRODUCTS  = 'products';
    const GROUP_STRUCTURE = 'structure';
    const GROUP_GENERAL   = 'general';

    const EXECUTION_SPEED_SLOW = 'slow';
    const EXECUTION_SPEED_FAST = 'fast';

    private $inspectionsData = array(
        array(
            'nick' => 'AmazonProductsWithoutVariations',
            'title' => 'Amazon products without variations',
            'description' => '',
            'group' => self::GROUP_PRODUCTS,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_AmazonProductsWithoutVariations'
        ),
        array(
            'nick' => 'BrokenTables',
            'title' => 'Broken tables',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_BrokenTables'
        ),
        array(
            'nick' => 'ConfigsValidity',
            'title' => 'Configs validity',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_ConfigsValidity'
        ),
        array(
            'nick' => 'EbayItemIdStructure',
            'title' => 'Ebay item id N_A',
            'description' => '',
            'group' => self::GROUP_PRODUCTS,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_EbayItemIdStructure'
        ),
        array(
            'nick' => 'ExtensionCron',
            'title' => 'Extension Cron',
            'description' => '
            - Cron array(runner) does not work<br>
            - Cron array(runner) is not working more than 30 min<br>
            - Cron array(runner) is disabled by developer
            ',
            'group' => self::GROUP_GENERAL,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_ExtensionCron'
        ),
        array(
            'nick' => 'FilesPermissions',
            'title' => 'Files and Folders permissions',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_SLOW,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_FilesPermissions'
        ),
        array(
            'nick' => 'FilesValidity',
            'title' => 'Files validity',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_FilesValidity',
        ),
        array(
            'nick' => 'ListingProductStructure',
            'title' => 'Listing product structure',
            'description' => '',
            'group' => self::GROUP_PRODUCTS,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_ListingProductStructure',
        ),
        array(
            'nick' => 'LocalPoolOverwrites',
            'title' => 'Show Local Pool Overwrites',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_LocalPoolOverwrites',
        ),
        array(
            'nick' => 'MagentoSettings',
            'title' => 'Magento settings',
            'description' => '
            - Non-default Magento timezone set<br>
            - GD library is installed<br>
            - Has conflicted modules<br>
            - Compilation is enabled<br>
            - Wrong cache configuration<br>
            - [APC|Memchached|Redis] Cache is enabled<br>
            ',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_MagentoSettings',
        ),
        array(
            'nick' => 'NonexistentTemplates',
            'title' => 'Nonexistent template',
            'description' => '',
            'group' => self::GROUP_PRODUCTS,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_NonexistentTemplates',
        ),
        array(
            'nick' => 'OrderItemStructure',
            'title' => 'Order item structure',
            'description' => '',
            'group' => self::GROUP_ORDERS,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_OrderItemStructure',
        ),
        array(
            'nick' => 'OverwrittenModel',
            'title' => 'Show overwritten models',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_OverwrittenModel',
        ),
        array(
            'nick' => 'RemovedStores',
            'title' => 'Removed stores',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_RemovedStores',
        ),
        array(
            'nick' => 'ServerConnection',
            'title' => 'Connection with server',
            'description' => '',
            'group' => self::GROUP_GENERAL,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_ServerConnection',
        ),
        array(
            'nick' => 'ShowM2eProLoggers',
            'title' => 'Show M2ePro loggers',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_SLOW,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_ShowM2eProLoggers',
        ),
        array(
            'nick' => 'SystemRequirements',
            'title' => 'System Requirements',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_SystemRequirements',
        ),
        array(
            'nick' => 'TablesStructureValidity',
            'title' => 'Tables structure validity',
            'description' => '',
            'group' => self::GROUP_STRUCTURE,
            'execution_speed_group' => self::EXECUTION_SPEED_FAST,
            'handler' => 'M2ePro/ControlPanel_Inspection_Inspector_TablesStructureValidity',
        ),
    );

    /** @var Ess_M2ePro_Model_Requirements_Manager */
    private $requirementsManager;

    public function __construct() {
        $this->requirementsManager = Mage::getModel('M2ePro/Requirements_Manager');
    }

    /**
     * @return Ess_M2ePro_Model_ControlPanel_Inspection_Definition[]
     */
    public function getDefinitions()
    {
        $definitions = array();

        foreach ($this->inspectionsData as $inspectionData) {
            if ($inspectionData['nick'] == 'SystemRequirements') {
                foreach ($this->requirementsManager->getChecks() as $check) {
                    $inspectionData['description'] .= "- {$check->getRenderer()->getTitle()}: 
                    {$check->getRenderer()->getMin()}<br>";
                }
            }

            $definitions[] = Mage::getModel('M2ePro/ControlPanel_Inspection_Definition', array(
                'nick'                => $inspectionData['nick'],
                'title'               => $inspectionData['title'],
                'description'         => $inspectionData['description'],
                'group'               => $inspectionData['group'],
                'executionSpeedGroup' => $inspectionData['execution_speed_group'],
                'handler'             => $inspectionData['handler'],
            ));
        }

        return $definitions;
    }
}
