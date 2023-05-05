<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Notification_AbstractNotificationMessage extends Mage_Adminhtml_Block_Template
{
    /** @var string */
    protected $sinceDate;
    /** @var int */
    protected $failOrderCount;
    /** @var string */
    protected $skipUrl;
    /** @var array */
    protected $components;
    /** @var array */
    protected $logLinkFilters;

    public function setSinceDate($date)
    {
        /** @var Mage_Core_Helper_Data $coreHelper */
        $coreHelper = Mage::helper('core');

        $this->sinceDate = $coreHelper->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_LONG);
    }

    public function setFailOrderCount($count)
    {
        $this->failOrderCount = $count;
    }

    public function setSkipUrl($skipUrl)
    {
        $this->skipUrl = $skipUrl;
    }

    public function setComponents(array $components)
    {
        $this->components = $components;
    }

    public function setLogLinkFilters(array $filters)
    {
        $this->logLinkFilters = $filters;
    }

    abstract protected function renderHtml();

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $this->getLayout()
            ->getBlock('head')
            ->addJs('M2ePro/Order/LogNotification.js');

        return $this->renderHtml();
    }

    protected function makeLinksHtmlToComponentLogs()
    {
        /** @var  $adminhtmlHelper */
        $adminhtmlHelper = Mage::helper('adminhtml');

        $componentLinks = array();
        foreach ($this->components as $component) {
            $title = $route = '';
            switch ($component) {
                case Ess_M2ePro_Helper_View_Amazon::NICK:
                    $title = 'Amazon orders logs';
                    $route = 'M2ePro/adminhtml_amazon_log/order';
                    break;
                case Ess_M2ePro_Helper_View_Ebay::NICK:
                    $title = 'eBay orders logs';
                    $route = 'M2ePro/adminhtml_ebay_log/order';
                    break;
                case Ess_M2ePro_Helper_View_Walmart::NICK:
                    $title = 'Walmart orders logs';
                    $route = 'M2ePro/adminhtml_walmart_log/order';
                    break;
            }

            $filterHash = http_build_query($this->logLinkFilters);
            $filterHash = base64_encode($filterHash);

            $url = $adminhtmlHelper->getUrl($route, array('filter' => $filterHash));

            $componentLinks[] = sprintf('<a href="%s" target="_blank">%s</a>', $url, $title);
        }

        return implode(' / ', $componentLinks);
    }

    protected function makeSkipLink()
    {
        return <<<HTML
<script>
if (typeof LogNotificationObj == 'undefined') {
    LogNotificationObj = new LogNotification()
}
</script>
<a href="javascript:void(0);" onclick="LogNotificationObj.skipLogToCurrentDate('{$this->skipUrl}')">
    Skip this message
</a>
HTML;
    }
}
