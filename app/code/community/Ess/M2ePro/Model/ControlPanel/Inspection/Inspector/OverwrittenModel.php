<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_ControlPanel_Inspection_Inspector_OverwrittenModel
    extends Ess_M2ePro_Model_ControlPanel_Inspection_AbstractInspection
    implements Ess_M2ePro_Model_ControlPanel_Inspection_InspectorInterface
{
    /**@var array */
    protected $_overwrittenFiles;

    /**@var bool */
    protected $_extensionFilesOverwritten = false;

    //########################################

    public function getTitle()
    {
        return 'Show overwritten models';
    }

    public function getGroup()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::GROUP_STRUCTURE;
    }

    public function getExecutionSpeed()
    {
        return Ess_M2ePro_Model_ControlPanel_Inspection_Manager::EXECUTION_SPEED_FAST;
    }

    //########################################

    public function process()
    {
        $issues = array();
        $this->getRewrites();

        if (empty($this->_overwrittenFiles)) {
            return $issues;
        }

        if ($this->_extensionFilesOverwritten) {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createError(
                $this,
                'Overwritten extension models',
                $this->renderMetadata($this->_overwrittenFiles)
            );
        } else {
            $issues[] = Mage::getSingleton('M2ePro/ControlPanel_Inspection_Result_Factory')->createNotice(
                $this,
                'Overwritten models',
                $this->renderMetadata($this->_overwrittenFiles)
            );
        }

        return $issues;
    }

    //########################################

    protected function getRewrites()
    {
        $config = Mage::getConfig()->getNode('global/models')->children();

        foreach ($config as $node) {
            foreach ($node->rewrite as $rewriteNode) {
                foreach ($rewriteNode->children() as $rewrite) {
                    if (!$node->class) {
                        continue;
                    }

                    $classNameParts = explode('_', $rewrite->getName());

                    foreach ($classNameParts as &$part) {
                        $part[0] = strtoupper($part[0]);
                    }

                    $classNameParts = array_merge(array($node->class), $classNameParts);
                    $originalClass = implode('_', $classNameParts);

                    if (strpos(strtoupper($rewrite), strtoupper('ess_m2epro')) !== false) {
                        continue;
                    }

                    if (strpos(strtoupper($originalClass), strtoupper('ess_m2epro')) !== false) {
                        $this->_extensionFilesOverwritten = true;
                    }

                    $this->_overwrittenFiles[] = array(
                        'from' => implode('_', $classNameParts),
                        'to'   => $rewrite
                    );
                }
            }
        }
    }

    //########################################

    protected function renderMetadata($data)
    {
        $html = <<<HTML
<table>
    <tr>
        <th style="width: 600px">From</th>
        <th style="width: 600px">To</th>
    </tr>
HTML;
        foreach ($data as $item) {
            $color = '#333';
            if (strpos(strtoupper($item['from']), strtoupper('ess_m2epro')) !== false) {
                $color = '#FF0000';
            }

            $html .= <<<HTML
<tr>
    <td style="color: {$color}">{$item['from']}</td>
    <td>{$item['to']}</td>
</tr>
HTML;
        }

        $html .='</table>';
        return $html;
    }

    //########################################
}