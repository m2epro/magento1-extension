<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Template_Category_Main
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $categoryTemplate = false;
    protected $messages = array();

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/walmart/listing/template_category/main.phtml');
    }

    //########################################

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    //########################################

    /**
     * @return boolean
     */
    public function isCategoryTemplate()
    {
        return $this->categoryTemplate;
    }

    /**
     * @param boolean $categoryTemplate
     */
    public function setCategoryTemplate($categoryTemplate)
    {
        $this->categoryTemplate = $categoryTemplate;
    }

    //########################################

    public function getWarnings()
    {
        $warnings = '';
        foreach ($this->getMessages() as $message) {
            $warnings .= <<<HTML
<ul class="messages">
    <li class="{$message['type']}-msg">
        <ul>
            <li>{$message['text']}</li>
        </ul>
    </li>
</ul>
HTML;
        }
        return $warnings;
    }

    //########################################
}