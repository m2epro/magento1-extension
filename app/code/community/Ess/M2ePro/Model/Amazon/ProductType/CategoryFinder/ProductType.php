<?php

class Ess_M2ePro_Model_Amazon_ProductType_CategoryFinder_ProductType
{
    /** @var string */
    private $title;
    /** @var string */
    private $nick;
    /** @var int|null */
    private $templateId;

    public function __construct($title, $nick, $templateId = null)
    {
        $this->title = $title;
        $this->nick = $nick;
        $this->templateId = $templateId;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getNick()
    {
        return $this->nick;
    }

    public function getTemplateId()
    {
        return $this->templateId;
    }
}