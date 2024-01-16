<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_ControlPanel_Tools_MagentoController
    extends Ess_M2ePro_Controller_Adminhtml_ControlPanel_CommandController
{
    //########################################

    /**
     * @title "Show Event Observers"
     * @description "Show Event Observers"
     */
    public function showEventObserversAction()
    {
        $eventObservers = Mage::helper('M2ePro/Magento')->getAllEventObservers();

        $html = $this->getStyleHtml();

        $html .= <<<HTML

<h2 style="margin: 20px 0 0 10px">Event Observers</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 50px">Area</th>
        <th style="width: 500px">Event</th>
        <th style="width: 500px">Observer</th>
    </tr>

HTML;

        foreach ($eventObservers as $area => $areaEvents) {
            if (empty($areaEvents)) {
                continue;
            }

            $areaRowSpan = count($areaEvents, COUNT_RECURSIVE) - count($areaEvents);

            $html .= '<tr>';
            $html .= '<td valign="top" rowspan="'.$areaRowSpan.'">'.$area.'</td>';

            foreach ($areaEvents as $eventName => $eventData) {
                if (empty($eventData)) {
                    continue;
                }

                $eventRowSpan = count($eventData);

                $html .= '<td rowspan="'.$eventRowSpan.'">'.$eventName.'</td>';

                $isFirstObserver = true;
                foreach ($eventData as $observer) {
                    if (!$isFirstObserver) {
                        $html .= '<tr>';
                    }

                    $html .= '<td>'.$observer.'</td>';
                    $html .= '</tr>';

                    $isFirstObserver = false;
                }
            }
        }

        $html .= '</table>';

        return $this->getResponse()->setBody($html);
    }

    /**
     * @title "Show Installed Modules"
     * @description "Show Installed Modules"
     */
    public function showInstalledModulesAction()
    {
        $installedModules = Mage::getConfig()->getNode('modules')->asArray();

        if (empty($installedModules)) {
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('No Installed Modules.'));
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Installed Modules
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 500px">Module</th>
        <th style="width: 100px">Status</th>
        <th style="width: 100px">Code Pool</th>
        <th style="width: 100px">Version</th>
    </tr>
HTML;
        foreach ($installedModules as $module => $data) {
            $status = isset($data['active']) && $data['active'] === 'true'
                ? '<span style="color: forestgreen">Enabled</span>'
                : '<span style="color: orangered">Disabled</span>';

            $codePool = !empty($data['codePool']) ? $data['codePool'] : '&nbsp;';
            $version  = !empty($data['version']) ? $data['version'] : '&nbsp;';

            $html .= <<<HTML
<tr>
    <td>{$module}</td>
    <td>{$status}</td>
    <td>{$codePool}</td>
    <td>{$version}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        return $this->getResponse()->setBody(str_replace('%count%', count($installedModules), $html));
    }

    /**
     * @title "Clear Cache"
     * @description "Clear magento cache"
     * @confirm "Are you sure?"
     */
    public function clearMagentoCacheAction()
    {
        Mage::helper('M2ePro/Magento')->clearCache();
        $this->_getSession()->addSuccess('Magento cache was cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
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
            $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
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
        $this->_redirectUrl(Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl());
    }

    //########################################

    protected function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_ControlPanel')->getPageToolsTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }

    //########################################
}
