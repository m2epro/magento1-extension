<?php

    /*
    * @copyright  Copyright (c) 2013 by  ESS-UA.
    */

class Ess_M2ePro_Block_Adminhtml_Grid_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
{
    protected $_groups      = array();

    // #######################################

    public function isAvailable()
    {
        // also return available if need to display advanced filters, but hide massactions
        return ($this->getCount() > 0 && $this->getParentBlock()->getMassactionIdField())
        || (isset($this->getParentBlock()->hideMassactionColumn) && $this->getParentBlock()->hideMassactionColumn);
    }

    public function setGroups(array $groups)
    {
        foreach ($groups as $groupName => $label) {
            $this->_groups[$groupName] = array(
                'label' => $label,
                'items' => array()
            );
        }

        return $this;
    }

    public function addItem($itemId, array $item, $group = NULL)
    {
        if (!empty($group) && isset($this->_groups[$group])) {
            $this->_groups[$group]['items'][] = $itemId;
        }

        return parent::addItem($itemId, $item);
    }

    // ---------------------------------------

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        return $this->injectOptGroupsIfNeed($html);
    }

    public function getJavaScript()
    {
        // checking if need to remove massactions, but need to display advanced filters
        if(!isset($this->getParentBlock()->hideMassactionColumn) || !$this->getParentBlock()->hideMassactionColumn) {
            $javascript = parent::getJavaScript();

            return $javascript . <<<JAVASCRIPT
window['{$this->getJsObjectName()}'] = {$this->getJsObjectName()};
JAVASCRIPT;
        }
        return '';
    }

    // #######################################

    protected function injectOptGroupsIfNeed($html)
    {
        if (empty($this->_groups)) {
            return $html;
        }

        $selectId    = $this->getHtmlId() . '-select';
        $selectClass = 'required-entry select absolute-advice local-validation';
        $pattern     = '/(<select id="'.$selectId.'" class="'.$selectClass.'"[^<]*>.*<\/select>)/si';

        if (!preg_match($pattern, $html, $matches)) {
            return $html;
        }

        return preg_replace($pattern, $this->wrapOptionsInOptGroups($matches[1]), $html);
    }

    // ---------------------------------------

    public function wrapOptionsInOptGroups($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $xpathObj = new DOMXPath($dom);
        $select = $dom->getElementsByTagName('select')->item(0);

        foreach ($this->_groups as $groupName => $groupData) {

            if (count($groupData['items']) == 0) {
                continue;
            }

            $optgroup = $dom->createElement('optgroup');
            $optgroup->setAttribute('label', $groupData['label']);

            foreach ($groupData['items'] as $itemId) {
                $option = $xpathObj->query("//select/option[@value='{$itemId}']", $select)
                                   ->item(0);
                $option = $select->removeChild($option);
                $optgroup->appendChild($option);
            }

            $select->appendChild($optgroup);
        }

        // Moving remaining options in end of list
        foreach ($xpathObj->query('//select/option', $select) as $option) {

            if (empty($option->nodeValue)) {
                continue;
            }

            try {

                $option = $select->removeChild($option);
                $select->appendChild($option);

            } catch(DOMException $e) {}
        }

        // Removing doctype, html, body
        $dom->removeChild($dom->doctype);
        $dom->replaceChild($dom->firstChild->firstChild->firstChild, $dom->firstChild);

        return $dom->saveHTML();
    }

    // #######################################
}