<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Definition
{
    /** @var string */
    private $nick;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var string */
    private $group;

    /** @var string */
    private $executionSpeedGroup;

    /** @var string */
    private $handler;

    /**
     * @param array $args
     */
    public function __construct($args)
    {
        $this->nick = $args['nick'];
        $this->title = $args['title'];
        $this->description = $args['description'];
        $this->group = $args['group'];
        $this->executionSpeedGroup = $args['executionSpeedGroup'];
        $this->handler = $args['handler'];
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getExecutionSpeedGroup()
    {
        return $this->executionSpeedGroup;
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
