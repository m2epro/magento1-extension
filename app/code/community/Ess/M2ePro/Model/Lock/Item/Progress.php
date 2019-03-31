<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Lock_Item_Progress
{
    const CONTENT_DATA_KEY = 'progress_data';

    private $lockItemManager = NULL;

    private $progressNick = NULL;

    //########################################

    public function __construct(array $args)
    {
        if (empty($args['lock_item_manager'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Lock item manager does not specified.');
        }

        if (empty($args['progress_nick'])) {
            throw new Ess_M2ePro_Model_Exception_Logic('Progress nick does not specified.');
        }

        if (!($args['lock_item_manager'] instanceof Ess_M2ePro_Model_Lock_Item_Manager)) {
            throw new Ess_M2ePro_Model_Exception_Logic(sprintf(
                'Lock item manager must be instance of "Ess_M2ePro_Model_Lock_Item_Manager", but got "%s"',
                get_class($args['lock_item_manager'])
            ));
        }

        $this->lockItemManager = $args['lock_item_manager'];
        $this->progressNick    = str_replace('/', '-', $args['progress_nick']);
    }

    //########################################

    public function isInProgress()
    {
        $contentData = $this->lockItemManager->getContentData();
        return isset($contentData[self::CONTENT_DATA_KEY][$this->progressNick]);
    }

    // ---------------------------------------

    public function start()
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick] = array(
            'percentage' => 0,
        );

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    public function setPercentage($percentage)
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick]['percentage'] = $percentage;

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    public function setDetails($args = array())
    {
        $contentData = $this->lockItemManager->getContentData();

        $contentData[self::CONTENT_DATA_KEY][$this->progressNick] = $args;

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    public function stop()
    {
        $contentData = $this->lockItemManager->getContentData();

        unset($contentData[self::CONTENT_DATA_KEY][$this->progressNick]);

        $this->lockItemManager->setContentData($contentData);

        return $this;
    }

    //########################################
}