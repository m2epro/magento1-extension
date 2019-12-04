<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Component extends Mage_Core_Helper_Abstract
{
    const MENU_ROOT_NODE = 'm2epro';
    const MAINTENANCE_MENU_STATE_CACHE_KEY = 'maintenance_menu_state';

    //########################################

    public function getComponents()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Walmart::NICK,
        );
    }

    // ---------------------------------------

    public function getComponentsTitles()
    {
        return array(
            Ess_M2ePro_Helper_Component_Ebay::NICK   => Mage::helper('M2ePro/Component_Ebay')->getTitle(),
            Ess_M2ePro_Helper_Component_Amazon::NICK => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
            Ess_M2ePro_Helper_Component_Walmart::NICK => Mage::helper('M2ePro/Component_Walmart')->getTitle(),
        );
    }

    //########################################

    public function getEnabledComponents()
    {
        $components = array();

        if (Mage::helper('M2ePro/Component_Ebay')->isEnabled()) {
            $components[] = Ess_M2ePro_Helper_Component_Ebay::NICK;
        }

        if (Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            $components[] = Ess_M2ePro_Helper_Component_Amazon::NICK;
        }

        if (Mage::helper('M2ePro/Component_Walmart')->isEnabled()) {
            $components[] = Ess_M2ePro_Helper_Component_Walmart::NICK;
        }

        return $components;
    }

    // ---------------------------------------

    public function getEnabledComponentsTitles()
    {
        $components = array();

        if (Mage::helper('M2ePro/Component_Ebay')->isEnabled()) {
            $components[Ess_M2ePro_Helper_Component_Ebay::NICK] =
                Mage::helper('M2ePro/Component_Ebay')->getTitle();
        }

        if (Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            $components[Ess_M2ePro_Helper_Component_Amazon::NICK] =
                Mage::helper('M2ePro/Component_Amazon')->getTitle();
        }

        if (Mage::helper('M2ePro/Component_Walmart')->isEnabled()) {
            $components[Ess_M2ePro_Helper_Component_Walmart::NICK] =
                Mage::helper('M2ePro/Component_Walmart')->getTitle();
        }

        return $components;
    }

    //########################################

    public function getDisabledComponents()
    {
        $components = array();

        if (!Mage::helper('M2ePro/Component_Ebay')->isEnabled()) {
            $components[] = Ess_M2ePro_Helper_Component_Ebay::NICK;
        }

        if (!Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            $components[] = Ess_M2ePro_Helper_Component_Amazon::NICK;
        }

        if (!Mage::helper('M2ePro/Component_Walmart')->isEnabled()) {
            $components[] = Ess_M2ePro_Helper_Component_Walmart::NICK;
        }

        return $components;
    }

    // ---------------------------------------

    public function getDisabledComponentsTitles()
    {
        $components = array();

        if (!Mage::helper('M2ePro/Component_Ebay')->isEnabled()) {
            $components[Ess_M2ePro_Helper_Component_Ebay::NICK] =
                Mage::helper('M2ePro/Component_Ebay')->getTitle();
        }

        if (!Mage::helper('M2ePro/Component_Amazon')->isEnabled()) {
            $components[Ess_M2ePro_Helper_Component_Amazon::NICK] =
                Mage::helper('M2ePro/Component_Amazon')->getTitle();
        }

        if (!Mage::helper('M2ePro/Component_Walmart')->isEnabled()) {
            $components[Ess_M2ePro_Helper_Component_Walmart::NICK] =
                Mage::helper('M2ePro/Component_Walmart')->getTitle();
        }

        return $components;
    }

    //########################################

    public function isSingleActiveComponent()
    {
        return count($this->getEnabledComponents()) == 1;
    }

    //########################################

    public function getComponentTitle($component)
    {
        $title = null;

        switch ($component) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $title = Mage::helper('M2ePro/Component_Ebay')->getTitle();
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $title = Mage::helper('M2ePro/Component_Amazon')->getTitle();
                break;
            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $title = Mage::helper('M2ePro/Component_Walmart')->getTitle();
                break;
        }

        return $title;
    }

    //########################################

    public function getComponentMode($modelName, $value, $field = null)
    {
        /** @var $model Ess_M2ePro_Model_Component_Parent_Abstract */
        $model = Mage::helper('M2ePro')->getModel($modelName);

        if ($model === null || !($model instanceof Ess_M2ePro_Model_Component_Parent_Abstract)) {
            return null;
        }

        $mode = $model->loadInstance($value, $field)->getData('component_mode');

        if ($mode === null || !in_array($mode, $this->getComponents())) {
            return null;
        }

        return $mode;
    }

    public function getComponentModel($mode, $modelName)
    {
        if ($mode === null || !in_array($mode, $this->getComponents())) {
            return null;
        }

        /** @var $model Ess_M2ePro_Model_Component_Parent_Abstract */
        $model = Mage::helper('M2ePro')->getModel($modelName);

        if ($model === null || !($model instanceof Ess_M2ePro_Model_Component_Parent_Abstract)) {
            return null;
        }

        $model->setChildMode($mode);

        return $model;
    }

    public function getComponentCollection($mode, $modelName)
    {
        return $this->getComponentModel($mode, $modelName)->getCollection();
    }

    // ---------------------------------------

    public function getUnknownObject($modelName, $value, $field = null)
    {
        $mode = $this->getComponentMode($modelName, $value, $field);

        if ($mode === null) {
            return null;
        }

        return $this->getComponentObject($mode, $modelName, $value, $field);
    }

    public function getComponentObject($mode, $modelName, $value, $field = null)
    {
        /** @var $model Ess_M2ePro_Model_Component_Parent_Abstract */
        $model = $this->getComponentModel($mode, $modelName);

        if ($model === null) {
            return null;
        }

        return $model->loadInstance($value, $field);
    }

    // ---------------------------------------

    public function getCachedUnknownObject($modelName, $value, $field = null, array $tags = array())
    {
        $mode = $this->getComponentMode($modelName, $value, $field);

        if ($mode === null) {
            return null;
        }

        return $this->getCachedComponentObject($mode, $modelName, $value, $field, $tags);
    }

    public function getCachedComponentObject($mode, $modelName, $value, $field = null, array $tags = array())
    {
        if (Mage::helper('M2ePro/Module')->isDevelopmentEnvironment()) {
            return $this->getComponentObject($mode, $modelName, $value, $field);
        }

        $cacheKey = strtoupper('component_'.$mode.'_'.$modelName.'_data_'.$field.'_'.$value);
        $cacheData = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue($cacheKey);

        if ($cacheData !== false) {
            return $cacheData;
        }

        $tags[] = $mode;
        $tags[] = $modelName;
        $tags[] = $mode.'_'.$modelName;
        $tags = array_unique($tags);
        $tags = array_map('strtolower', $tags);

        $cacheData = $this->getComponentObject($mode, $modelName, $value, $field);

        if (!empty($cacheData)) {
            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue($cacheKey, $cacheData, $tags, 60*60*24);
        }

        return $cacheData;
    }

    //########################################

    public function prepareMenu(array $menuArray)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed(self::MENU_ROOT_NODE)) {
            return $menuArray;
        }

        $maintenanceMenuState = Mage::helper('M2ePro/Data_Cache_Permanent')->getValue(
            self::MAINTENANCE_MENU_STATE_CACHE_KEY
        );

        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            if ($maintenanceMenuState === null) {
                Mage::helper('M2ePro/Data_Cache_Permanent')->setValue(
                    self::MAINTENANCE_MENU_STATE_CACHE_KEY, true
                );
                Mage::helper('M2ePro/Magento')->clearMenuCache();
            }

            return $this->processMaintenance($menuArray);
        } elseif ($maintenanceMenuState !== null) {
            Mage::helper('M2ePro/Data_Cache_Permanent')->removeValue(
                self::MAINTENANCE_MENU_STATE_CACHE_KEY
            );
            Mage::helper('M2ePro/Magento')->clearMenuCache();
        }

        if (Mage::helper('M2ePro/Module')->isDisabled()) {
            return $this->processModuleDisable($menuArray);
        }

        /** @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');
        $activeComponents = Mage::helper('M2ePro/Component')->getEnabledComponents();

        $menuArray[self::MENU_ROOT_NODE]['label'] = implode(', ', $this->getEnabledComponentsTitles());

        foreach ($menuArray[self::MENU_ROOT_NODE]['children'] as $component => $componentNode) {
            if (empty($componentNode['children'])) {
                continue;
            }

            if (!in_array($component, $activeComponents)) {
                unset($menuArray[self::MENU_ROOT_NODE]['children'][$component]);
                continue;
            }

            if ($activeBlocker = $wizardHelper->getActiveBlockerWizard($component)) {
                $wizardUrl = Mage::helper('adminhtml')->getUrl(
                    'M2ePro/adminhtml_wizard_'.$wizardHelper->getNick($activeBlocker).'/index'
                );

                $menuArray[self::MENU_ROOT_NODE]['children'][$component]['children'] = array();
                $menuArray[self::MENU_ROOT_NODE]['children'][$component]['click'] = true;
                $menuArray[self::MENU_ROOT_NODE]['children'][$component]['url'] = $wizardUrl;
            }
        }

        if (count($activeComponents) == 1) {
            $activeComponent = reset($activeComponents);
            $activeComponentBlocker = $wizardHelper->getActiveBlockerWizard($activeComponent);

            if (!$activeComponentBlocker) {
                $activeComponentNodes = $menuArray[self::MENU_ROOT_NODE]['children'][$activeComponent]['children'];
                foreach ($activeComponentNodes as &$activeComponentNodeData) {
                    $activeComponentNodeData['last']  = false;
                    $activeComponentNodeData['level'] = $activeComponentNodeData['level'] - 1;
                }

                unset($activeComponentNodeData);

                $menuArray[self::MENU_ROOT_NODE]['children'] = array_merge(
                    $activeComponentNodes, $menuArray[self::MENU_ROOT_NODE]['children']
                );
                unset($menuArray[self::MENU_ROOT_NODE]['children'][$activeComponent]);
            } else {
                $menuArray[self::MENU_ROOT_NODE]['click'] = true;
                $menuArray[self::MENU_ROOT_NODE]['url'] =
                    $menuArray[self::MENU_ROOT_NODE]['children'][$activeComponent]['url'];
                $menuArray[self::MENU_ROOT_NODE]['children'] = array();
            }
        }

        return $menuArray;
    }

    //########################################

    protected function processMaintenance(array $menuArray)
    {
        $menu = $menuArray[self::MENU_ROOT_NODE];

        $menuArray[self::MENU_ROOT_NODE] = array(
            'label'      => 'M2E Pro',
            'sort_order' => $menu['sort_order'],
            'url'        => Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_maintenance/index'),
            'active'     => $menu['active'],
            'level'      => $menu['level']
        );

        return $menuArray;
    }

    protected function processModuleDisable(array $menuArray)
    {
        unset($menuArray[self::MENU_ROOT_NODE]);

        return $menuArray;
    }

    //########################################
}
