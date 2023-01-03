<?php

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}
class Mdn_Topline extends Module implements WidgetInterface {
    const ENABLED = "TOPLINE_ENABLED";
    const MESSAGE = "TOPLINE_MESSAGE";
    const END     = "TOPLINE_END";
    const START   = "TOPLINE_START";
    const UNIQ_ID   = "TOPLINE_UNIQ_ID";


    public function __construct()
    {
        $this->name = 'mdn_topline';
        $this->author = 'Maison du Net - Loris';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7.2.0', 'max' => _PS_VERSION_);
        $this->displayName = "Bandeau d'information haut de page";
        $this->description = "Show a banner before the header to show information";
        parent::__construct();
    }

    public function install()
    {
        $hooks = $this->registerHook(['displayAfterBodyOpeningTag', 'actionFrontControllerSetMedia']);
        return parent::install();
    }

    public function hookDisplayAfterBodyOpeningTag($params)
    {
        $this->smarty->assign($this->getTopLine());
        return $this->display(__FILE__, 'views/templates/widget/topline.tpl');
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet(
            'module-'.$this->name.'-style',
            'modules/'.$this->name.'/views/css/topline.css',
            [
                'media' => 'all',
                'priority' => 200,
            ]
        );

        $this->context->controller->registerJavascript(
            'module-'.$this->name.'-js',
            'modules/'.$this->name.'/views/js/topline.js',
            [
                'priority' => 200,
                'attribute' => 'async',
            ]
        );
    }

    public function getTopLine(){
        $enabled = Configuration::get(self::ENABLED);

        if($enabled) {
            // If date out of bounds + check if fields are emptied
            $start = Configuration::get(self::START) != "" ? (strtotime(Configuration::get(self::START))) : 0;
            $end = Configuration::get(self::END) != "" ? (strtotime(Configuration::get(self::END))) : PHP_INT_MAX;

            if($start > time() || $end < time())
                $enabled = false;

            // if message empty
            if(trim(Configuration::get(self::MESSAGE)) == "")
                $enabled = false;
        }

        return [
            'topline' => [
                'enabled' => $enabled,
                'cookie' => "topline-" . Configuration::get(self::UNIQ_ID),
                'message' => Configuration::get(self::MESSAGE),
                'uniq_id' => Configuration::get(self::UNIQ_ID),
            ]
        ];
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if (!$this->active) {
            return;
        }
        $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->display(__FILE__, 'views/templates/widget/topline.tpl');
    }


    public function getWidgetVariables($hookName, array $configuration)
    {
        return $this->getTopLine();
    }

    public function getContent() {
        $output = null;
        if (Tools::isSubmit('mdn_topline')) {
            // quand on enregistre on met à jour un ID UNIQUE pour pouvoir la réafficher
            Configuration::updateValue(self::UNIQ_ID, uniqid());

            // Sauvegarde des champs dans la DB
            Configuration::updateValue(self::ENABLED, Tools::getValue(self::ENABLED) );
            Configuration::updateValue(self::MESSAGE, Tools::getValue(self::MESSAGE) );

            Configuration::updateValue(self::START, (Tools::getValue(self::START)) );
            Configuration::updateValue(self::END, (Tools::getValue(self::END)) );

            // Message
            $output .= $this->displayConfirmation($this->l("Top Line mise à jour"));
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => ('Topline'),
            ],
            'input' => [
                [
                    'type' => 'select',
                    'options' => [
                        'query' => [
                            ['id_option' => 0, "name" => "Non"],
                            ['id_option' => 1, "name" => "Oui"],
                        ],
                      'id' => 'id_option',
                      'name' => 'name'
                    ],
                    'label' => ('Activé :'),
                    'name' => self::ENABLED,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => ('Message :'),
                    'name' => self::MESSAGE,
                    'required' => false
                ],
                [
                    'type' => 'date',
                    'label' => ('Début :'),
                    'name' => self::START,
                    'required' => false
                ],
                [
                    'type' => 'date',
                    'label' => ('Fin :'),
                    'name' => self::END,
                    'required' => false
                ]
            ],
            'submit' => [
                'title' => ('Save'),
                "name" => 'mdn_topline',
                'class' => 'btn btn-default pull-right'
            ]
        ];


        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => ('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                    '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => ('Back to list')
            ]
        ];


        $helper->fields_value[self::ENABLED] = Configuration::get(self::ENABLED);
        $helper->fields_value[self::MESSAGE] = Configuration::get(self::MESSAGE);
        $helper->fields_value[self::START] = Configuration::get(self::START);
        $helper->fields_value[self::END] = Configuration::get(self::END);

        return $helper->generateForm($fieldsForm);
    }

}