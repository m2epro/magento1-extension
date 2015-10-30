<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Support extends Mage_Adminhtml_Block_Widget_Form_Container
{
    private $referrer = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('supportContainer');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';

        $this->_mode = 'support';
        $this->referrer = $this->getRequest()->getParam('referrer');
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $m2eProVersion = '<span style="color: #777; font-size: small; font-weight: normal">' .
                            '(M2E Pro ver. '.Mage::helper('M2ePro/Module')->getVersion().')' .
                         '</span>';
        $this->_headerText = Mage::helper('M2ePro')->__('Support') . " {$m2eProVersion}";
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        // ---------------------------------------
        $url = Mage::helper('M2ePro/View_Development')->getPageUrl();
        $this->_addButton('goto_development', array(
            'label'     => 'Control Panel',
            'onclick'   => 'window.location = \''.$url.'\'',
            'class'     => 'button_link development',
            'style'     => 'display: none;'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $migrationData = Mage::getModel('M2ePro/Registry')->load('/wizard/migrationToV6_notes_html/', 'key');
        $html = $migrationData->getData('value');
        $createDate = Mage::helper('M2ePro')->getDate($migrationData->getData('create_date'), true);
        $threeMonths = 3 * 30 * 24 * 60 * 60;

        if (!empty($html) && $this->referrer == Ess_M2ePro_Helper_View_Ebay::NICK &&
            (Mage::helper('M2ePro')->getCurrentGmtDate(true) < ($createDate + $threeMonths))) {
            $url = $this->getUrl('*/adminhtml_support/migrationNotes');
            $this->_addButton('migration_notes', array(
                'label'     => Mage::helper('M2ePro')->__('Migration Notes'),
                'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
            ));
        }
        // ---------------------------------------

        // ---------------------------------------
        if (is_null($this->referrer)) {

            $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl();

            $this->_addButton('goto_docs', array(
                'label' => Mage::helper('M2ePro')->__('Documentation'),
                'onclick' => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                'class' => 'button_link'
            ));

        } else {

            if ($this->referrer == Ess_M2ePro_Helper_View_Ebay::NICK) {

                $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(Ess_M2ePro_Helper_View_Ebay::NICK);

                $this->_addButton('goto_docs', array(
                    'label'     => Mage::helper('M2ePro')->__('Documentation'),
                    'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                    'class'     => 'button_link'
                ));

                $url = Mage::helper('M2ePro/Module_Support')->getVideoTutorialsUrl(Ess_M2ePro_Helper_View_Ebay::NICK);

                $this->_addButton('goto_video_tutorials', array(
                    'label'     => Mage::helper('M2ePro')->__('Video Tutorials'),
                    'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                    'class'     => 'button_link'
                ));

            } else {

                $activeComponents = Mage::helper('M2ePro/View_Common_Component')->getActiveComponents();

                if (count($activeComponents) == 1) {

                    $component = array_shift($activeComponents);
                    $url = Mage::helper('M2ePro/Module_Support')->getDocumentationUrl($component);

                    $this->_addButton('goto_docs', array(
                        'label'     => Mage::helper('M2ePro')->__('Documentation'),
                        'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                        'class'     => 'button_link'
                    ));

                    $url = Mage::helper('M2ePro/Module_Support')->getVideoTutorialsUrl($component);

                    $this->_addButton('goto_video_tutorials', array(
                        'label'     => Mage::helper('M2ePro')->__('Video Tutorials'),
                        'onclick'   => 'window.open(\''.$url.'\', \'_blank\'); return false;',
                        'class'     => 'button_link'
                    ));

                } else {

                    $this->_addButton('goto_docs', array(
                        'label' => Mage::helper('M2ePro')->__('Documentation'),
                        'class' => 'button_link drop_down button_documentation'
                    ));

                    $this->_addButton('goto_video_tutorials', array(
                        'label' => Mage::helper('M2ePro')->__('Video Tutorials'),
                        'class' => 'button_link drop_down button_video_tutorial'
                    ));
                }
            }
        }
        // ---------------------------------------
    }

    // ---------------------------------------

    public function getHeaderHtml()
    {
        if ($this->referrer != Ess_M2ePro_Helper_View_Common::NICK ||
            ($this->referrer == Ess_M2ePro_Helper_View_Common::NICK &&
             count(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents()) == 1)) {

            return parent::getHeaderHtml();
        }

        $data = array(
            'target_css_class' => 'button_documentation',
            'style' => 'max-height: 120px; overflow: auto; width: 150px;',
            'items' => $this->getDocumentationDropDownItems()
        );

        $dropDownBlockDocumentation = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_widget_button_dropDown', '', $data);

        $data = array(
            'target_css_class' => 'button_video_tutorial',
            'style' => 'max-height: 120px; overflow: auto; width: 150px;',
            'items' => $this->getVideoTutorialDropDownItems()
        );

        $dropDownBlockVideoTutorial = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_widget_button_dropDown', '', $data);

        return parent::getHeaderHtml()
        .$dropDownBlockDocumentation->toHtml()
        .$dropDownBlockVideoTutorial->toHtml();
    }

    // ---------------------------------------

    private function getVideoTutorialDropDownItems()
    {
        $items = array();

        // ---------------------------------------
        $items[] = array(
            'url'    => Mage::helper('M2ePro/Module_Support')->getVideoTutorialsUrl(
                    Ess_M2ePro_Helper_Component_Amazon::NICK
                ),
            'label'  => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $items[] = array(
            'url'    => Mage::helper('M2ePro/Module_Support')->getVideoTutorialsUrl(
                    Ess_M2ePro_Helper_Component_Buy::NICK
                ),
            'label'  => Mage::helper('M2ePro/Component_Buy')->getTitle(),
            'target' =>'_blank'
        );
        // ---------------------------------------

        return $items;
    }

    private function getDocumentationDropDownItems()
    {
        $items = array();

        // ---------------------------------------
        $items[] = array(
            'url'    => Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(
                    Ess_M2ePro_Helper_Component_Amazon::NICK
                ),
            'label'  => Mage::helper('M2ePro/Component_Amazon')->getTitle(),
            'target' => '_blank'
        );
        // ---------------------------------------

        // ---------------------------------------
        $items[] = array(
            'url'    => Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(
                    Ess_M2ePro_Helper_Component_Buy::NICK
                ),
            'label'  => Mage::helper('M2ePro/Component_Buy')->getTitle(),
            'target' =>'_blank'
        );
        // ---------------------------------------

        return $items;
    }

    //########################################
}