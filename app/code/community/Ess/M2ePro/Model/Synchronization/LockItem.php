<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_LockItem extends Ess_M2ePro_Model_LockItem
{
    //####################################

    public function __construct()
    {
        $args = func_get_args();
        empty($args[0]) && $args[0] = array();
        $params = $args[0];

        $this->setNick('synchronization');

        parent::__construct($params);
    }

    //####################################

    public function setTitle($title)
    {
        $this->addContentData('info_title',$title);
    }

    public function setPercents($percents)
    {
        (int)$percents < 0 && $percents = 0;
        (int)$percents > 100 && $percents = 100;
        $this->addContentData('info_percents',floor($percents));
    }

    public function setStatus($status)
    {
        $this->addContentData('info_status',$status);
    }

    //-----------------------------------

    public function getTitle()
    {
        return $this->getContentData('info_title');
    }

    public function getPercents()
    {
        return (int)$this->getContentData('info_percents');
    }

    public function getStatus()
    {
        return $this->getContentData('info_status');
    }

    //####################################
}