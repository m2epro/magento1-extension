<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Widget_Info extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_template = 'M2ePro/widget/info.phtml';

    protected $_info = array();

    //########################################

    public function getInfo()
    {
        return $this->_info;
    }

    public function setInfo(array $steps)
    {
        $this->_info = $steps;
        return $this;
    }

    //########################################

    public function getId()
    {
        if (!$this->hasData('id')) {
            $this->setData('id', 'id-' . Mage::helper('core')->getRandomString(20));
        }

        return $this->getData('id');
    }

    public function getInfoCount()
    {
        return count($this->getInfo());
    }

    public function getInfoPartWidth($index)
    {
        if (count($this->getInfo()) === 1) {
            return '100%';
        }

        return round(99 / $this->getInfoCount(), 2) . '%';
    }

    public function getInfoPartAlign($index)
    {
        if ($index === 0) {
            return 'left';
        }

        if (($this->getInfoCount() - 1) === $index) {
            return 'right';
        }

        return 'left';
    }

    //########################################

    protected function cutLongLines($line)
    {
        if (strlen($line) < 50) {
            return $line;
        }

        return substr($line, 0, 50) . '...';
    }

    //########################################
}
