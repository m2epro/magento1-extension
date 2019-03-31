<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Development_Tools_MagentoController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //########################################

    /**
     * @title "Show Overwritten Models"
     * @description "Show Overwritten Models"
     */
    public function showOverwrittenModelsAction()
    {
        $overwrittenModels = Mage::helper('M2ePro/Magento')->getRewrites();

        if (count ($overwrittenModels) <= 0) {
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('No Overwritten Models'));
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Overwritten Models
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 600px">From</th>
        <th style="width: 600px">To</th>
    </tr>
HTML;
        foreach ($overwrittenModels as $item) {

            $html .= <<<HTML
<tr>
    <td>{$item['from']}</td>
    <td>{$item['to']}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        return $this->getResponse()->setBody(str_replace('%count%',count($overwrittenModels),$html));
    }

    /**
     * @title "Show Local Pool Overwrites"
     * @description "Show Local Pool Overwrites"
     * @new_line
     */
    public function showLocalPoolOverwritesAction()
    {
        $localPoolOverwrites = Mage::helper('M2ePro/Magento')->getLocalPoolOverwrites();

        if (count($localPoolOverwrites) <= 0) {
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('No Local Pool Overwrites'));
        }

        $html = $this->getStyleHtml();

        $html .= <<<HTML
<h2 style="margin: 20px 0 0 10px">Local Pool Overwrites
    <span style="color: #808080; font-size: 15px;">(%count% entries)</span>
</h2>
<br/>

<table class="grid" cellpadding="0" cellspacing="0">
    <tr>
        <th style="width: 800px">Path</th>
        <th style="width: 40px"></th>
    </tr>
HTML;
        foreach ($localPoolOverwrites as $item) {

            $diffHtml = '';
            if (strpos(strtolower($item), 'm2epro') !== false) {

                $originalPath = str_replace('local', 'community', $item);
                $url = Mage::helper('adminhtml')->getUrl(
                    '*/adminhtml_development_tools_m2ePro_install/filesDiff',
                    array('filePath' => base64_encode($item), 'originalPath' => base64_encode($originalPath))
                );

                $diffHtml = '<a href="'.$url.'" target="_blank">Diff</a>';
            }

            $html .= <<<HTML
<tr>
    <td>{$item}</td>
    <td>{$diffHtml}</td>
</tr>
HTML;
        }

        $html .= '</table>';
        return $this->getResponse()->setBody(str_replace('%count%',count($localPoolOverwrites),$html));
    }

    /**
     * @title "Show Event Observers"
     * @description "Show Event Observers"
     * @new_line
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
     * @title "Show M2ePro Loggers"
     * @description "M2ePro/Module_Logger in magento files"
     * @new_line
     */
    public function showM2eProLoggersAction()
    {
        $recursiveIteratorIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(Mage::getBaseDir(), FilesystemIterator::FOLLOW_SYMLINKS)
        );

        $loggers = array();
        foreach ($recursiveIteratorIterator as $splFileInfo) {
            /**@var \SplFileInfo $splFileInfo */

            if (!$splFileInfo->isFile() ||
                !in_array($splFileInfo->getExtension(), array('php', 'phtml'))) {
                continue;
            }

            if (strpos($splFileInfo->getRealPath(), 'Ess'.DIRECTORY_SEPARATOR.'M2ePro') !== false ||
                strpos($splFileInfo->getRealPath(), 'Ess_M2ePro') !== false) {
                continue;
            }

            $splFileObject = $splFileInfo->openFile();
            if (!$splFileObject->getSize()) {
                continue;
            }

            $content = $splFileObject->fread($splFileObject->getSize());
            if (strpos($content, 'M2ePro/Module_Logger') === false) {
                continue;
            }

            $content = explode("\n", $content);
            foreach ($content as $line => $contentRow) {

                if (strpos($contentRow, 'M2ePro/Module_Logger') === false) {
                    continue;
                }

                $loggers[] = array(
                    'path' => $splFileObject->getRealPath(),
                    'line' => $line + 1,
                    'code' => implode("\n", array_slice($content, $line - 2, 7)),
                );
            }
        }

        if (count($loggers) <= 0) {
            return $this->getResponse()->setBody($this->getEmptyResultsHtml('No M2ePro Loggers'));
        }

        $cdnURL = '//cdnjs.cloudflare.com/ajax/libs/prism/1.6.0';
        $html = <<<HTML
<link type="text/css" href="{$cdnURL}/themes/prism-tomorrow.min.css" rel="stylesheet"/>
<script type="text/javascript" src="{$cdnURL}/prism.min.js"></script>
<script type="text/javascript" src="{$cdnURL}/components/prism-php.min.js"></script>
<script type="text/javascript" src="{$cdnURL}/components/prism-php-extras.min.js"></script>

<div style="max-width: 1280px; margin: 0 auto;">
    <h2 style="text-align: center; margin-bottom: 0; padding-top: 25px">M2ePro Loggers in Magento files
        <span style="color: #808080; font-size: 15px">(%count% entries)</span>
    </h2>
<br/>
HTML;
        foreach ($loggers as $logger) {
            $html .= <<<HTML
<figure>
    <figcaption>{$logger['path']}:{$logger['line']}</figcaption>
    <pre><code class="language-php">{$logger['code']}</code></pre>
</figure>
HTML;
        }

        return $this->getResponse()->setBody(str_replace('%count%', count($loggers), $html . '</div>'));
    }

    /**
     * @title "Show Installed Modules"
     * @description "Show Installed Modules"
     * @new_line
     */
    public function showInstalledModulesAction()
    {
        $installedModules = Mage::getConfig()->getNode('modules')->asArray();

        if (count($installedModules) <= 0) {
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
        return $this->getResponse()->setBody(str_replace('%count%',count($installedModules),$html));
    }

    /**
     * @title "Refresh Compilation"
     * @description "Refresh Compilation"
     * @confirm "Are you sure?"
     */
    public function refreshCompilationAction()
    {
        if (!defined('COMPILER_INCLUDE_PATH')) {
            $this->_getSession()->addError('Compilation is not enabled');
            $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
        } else {
            $this->_redirect('*/*/runCompilation');
            Mage::getModel('compiler/process')->clear();
            $this->getResponse()->sendHeaders();
        }
    }

    /**
     * @title "Run Compilation"
     * @description "Run Compilation"
     * @hidden
     */
    public function runCompilationAction()
    {
        try {

            Mage::getModel('compiler/process')->run();
            $this->_getSession()->addSuccess('The compilation has completed.');

        } catch (Exception $e) {
            $this->_getSession()->addError('Compilation error');
        }

        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    /**
     * @title "Clear Cache"
     * @description "Clear magento cache"
     * @confirm "Are you sure?"
     */
    public function clearMagentoCacheAction()
    {
        Mage::helper('M2ePro/Magento')->clearCache();
        $this->_getSession()->addSuccess('Magento cache was successfully cleared.');
        $this->_redirectUrl(Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl());
    }

    //########################################

    private function getEmptyResultsHtml($messageText)
    {
        $backUrl = Mage::helper('M2ePro/View_Development')->getPageToolsTabUrl();

        return <<<HTML
<h2 style="margin: 20px 0 0 10px">
    {$messageText} <span style="color: grey; font-size: 10px;">
    <a href="{$backUrl}">[back]</a>
</h2>
HTML;
    }

    //########################################
}