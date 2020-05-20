<?php
/**
*          Beautiful Theme for Prestashop
*
*          @author         Lathanao <welcome@lathanao.com>
*          @copyright      2019 Lathanao
*          @license        Commercial license see README.md
*          @version        1.0
**/

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AoGift extends Module implements WidgetInterface
{
    public $templateFile = array('displayShoppingCartFooter' => 'module:aogift/views/templates/front/hook/aogift.tpl');

    public $setup = array(  'AO_GIFT_ID_RULE' => '1',
                            'AO_GIFT_ID_PRODUCT' => '1',
                            'AO_GIFT_TITLE' => 'Cadeau de bienvenue',
                            'AO_GIFT_CONTENT' => 'Offre de bienvenue',
                            'AO_GIFT_BTN_1' => 'Ajouter',
                            'AO_GIFT_BTN_2' => 'Non Merci');

    public function __construct()
    {
        $this->name      = 'aogift';
        $this->author    = 'Lathanao';
        $this->version   = '1.1.1';
        $this->module_key = '507058b20c4e63da25824a309a10d674';
        $this->tab       = 'front_office_features';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Free gift on first order', array(), $this->name . '.Admin');
        $this->description = $this->trans(
            'Add a free gift an cart page on first order.',
            array(),
            $this->name . '.Admin'
        );
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return (parent::install()
            && $this->registerHook('displayShoppingCartFooter')
            && $this->registerHook('ShoppingCartFooter')
            && $this->registerHook('displayHeader')
            && $this->checkSetup()
        );
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $this->checkSetup();
        $this->setSetup();
        $this->postprocess();

        $this->smarty->assign(array(
            'default_url' => Context::getContext()->link->getAdminLink('AdminModules') . '&configure='.$this->name,
            'refresh_url' => $this->context->link->getModuleLink($this->name, 'ajax', array(), null, null, null, true),
            'form_url' => Tools::safeOutput($_SERVER['REQUEST_URI']),
            'setup' => $this->getSetup(),
        ));

        return $this->renderForm();
    }

    public function postprocess()
    {
        $oldCartRule = new CartRule(Configuration::get('AO_GIFT_ID_RULE'));
        $oldCartRule->delete();
        
        $newCartRule = new CartRule();
        foreach (Language::getLanguages() as $language) {
            $newCartRule->name[$language['id_lang']] = 'Free gift for first order - module';
        }
        $newCartRule->id_customer = '0';
        $newCartRule->date_from =  date('Y-m-d H:i:s');
        $newCartRule->date_to =  date('Y-m-d', strtotime(date('Y-m-d', time()) . ' + 365 day'));
//        foreach (Language::getLanguages() as $language) {
//            $newCartRule->name[$language['id_lang']] = 'Add a gift an cart page - module';
//        }
        $newCartRule->quantity =  '10000';
        $newCartRule->quantity_per_user =  '1';
        $newCartRule->priority =  '1';
        $newCartRule->partial_use =  '1';
        $newCartRule->code =  Tools::passwdGen();
        $newCartRule->code =  Tools::passwdGen();
        $newCartRule->minimum_amount =  '0.00';
        $newCartRule->minimum_amount_tax =  '0';
        $newCartRule->minimum_amount_currency =  '1';
        $newCartRule->minimum_amount_shipping =  '0';
        $newCartRule->country_restriction =  '0';
        $newCartRule->carrier_restriction =  '0';
        $newCartRule->group_restriction =  '0';
        $newCartRule->cart_rule_restriction =  '0';
        $newCartRule->product_restriction =  '0';
        $newCartRule->shop_restriction =  '0';
        $newCartRule->free_shipping =  '0';
        $newCartRule->reduction_percent =  '0.00';
        $newCartRule->reduction_amount =  '0.00';
        $newCartRule->reduction_tax =  '0';
        $newCartRule->reduction_currency =  '1';
        $newCartRule->reduction_product =  '0';
        $newCartRule->reduction_exclude_special =  '0';
        $newCartRule->gift_product =  Configuration::get('AO_GIFT_ID_PRODUCT');
        $newCartRule->gift_product_attribute =  '0';
        $newCartRule->highlight =  '0';
        $newCartRule->active =  '1';
        $newCartRule->save();

        Configuration::updateValue('AO_GIFT_ID_RULE', $newCartRule->id);

        foreach ($this->templateFile as $template) {
            parent::_clearCache($template);
        }
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array('title' => $this->trans('Settings', array(), 'Admin.Global'), 'icon' => 'icon-cogs'),
                'input' => array(

                    array(
                        'type'     => 'select',
                        'label'    => $this->trans('Product to offer', array(), 'Modules.' . $this->name . '.Admin'),
                        'name'     => 'AO_GIFT_ID_PRODUCT',
                        'required' => true,
                        'options' => [
                            'query' => $this->getAllProducts(),
                            'id'    => 'id',
                            'name'  => 'name',
                        ],
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->trans('Modal title', array(), $this->name . '.Admin'),
                        'name'     => 'AO_GIFT_TITLE',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'textarea',
                        'label'    => $this->trans('Modal content', array(), $this->name . '.Admin'),
                        'name'     => 'AO_GIFT_CONTENT',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->trans('Text button add discount', array(), $this->name . '.Admin'),
                        'name'     => 'AO_GIFT_BTN_1',
                        'required' => true,
                    ),
                    array(
                        'type'     => 'text',
                        'label'    => $this->trans('Text button close modal', array(), $this->name . '.Admin'),
                        'name'     => 'AO_GIFT_BTN_2',
                        
                        'required' => true,
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'HIDDEN_REFRESH_URL',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => 'HIDDEN_AJAX_URL',
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Global'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->submit_action = 'submitForm';
        $helper->tpl_vars = array(
            'fields_value' => $this->getSetup(true),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'refresh_url' => 'sqd',
        );

        foreach ($this->templateFile as $template) {
            parent::_clearCache($template, 'displayShoppingCartFooter');
        }
        return $helper->generateForm(array($fields_form));
    }

    public function hookdisplayHeader()
    {
        $this->context->controller->registerJavascript(
            'module-js-' . $this->name,
            '/modules/' . $this->name . '/views/js/' . $this->name . '.js',
            ['position' => 'bottom', 'priority' => 100]
        );

        $this->context->controller->registerStylesheet(
            'module-css-' . $this->name,
            'modules/' . $this->name . '/views/css/' . $this->name . '.css',
            ['media' => 'all', 'priority' => 600]
        );
    }

    public function getAllProducts()
    {
        $result = [];
        $allProducts =  Product::getProducts($this->context->language->id, 0, 0, 'id_product', 'ASC', false, false);

        foreach ($allProducts as $allProduct) {
            $result[] = array(
                'id' => $allProduct['id_product'],
                'name' => $allProduct['id_product'] . ' | ' . $allProduct['name']);
        }
        return $result;
    }

    public function getSetup($Multilang = null /*Need multilang for admin, no need for front */)
    {
        $id_lang = $this->context->language->id;
        $languages = Language::getLanguages(false);

        foreach ($this->setup as $key => $value) {
            if ($Multilang && is_array($value)) {
                foreach ($languages as $lang) {
                    $this->setup[$key][$lang['id_lang']] = Configuration::get($key, $lang['id_lang']);
                }
            } else {
                $this->setup[$key] = Configuration::get($key, $id_lang);
            }
        }

        $this->setup['HIDDEN_REFRESH_URL'] = $this->context->link->getModuleLink(
            $this->name,
            'ajax',
            array(),
            null,
            null,
            null,
            true
        );
        $this->setup['HIDDEN_AJAX_URL']    = $this->context->link->getAdminLink(
            $this->name,
            true,
            null,
            array('ajax' => true, 'action' => 'reload')
        );

        return $this->setup;
    }

    public function setSetup(array $newSetup = null /*from backup*/)
    {
        $updated = [];
        $newSetup || $newSetup = $_POST;

        foreach ($newSetup as $key => $value) {
            if (array_key_exists($key, $this->setup)) {
                Configuration::updateValue($key, $value, true /*HTML*/) && $updated[$key] = $value;
            } elseif (array_key_exists(Tools::substr($key, 0, -2), $this->setup)) {
                Configuration::updateValue(
                    Tools::substr($key, 0, -2),
                    array(Tools::substr($key, -1) => $value),
                    true
                );
                $updated[$key] = $value;
            }
        }

        foreach ($newSetup as $key => $value) {
            if (isset(array_keys($this->setup)[0])) {
                if ($key === array_keys($this->setup)[0]) {
                    $value ? $this->enable() : $this->disable();
                }
            }
        }

        if (isset($this->_html)) {
            $this->_html .= $this->displayConfirmation(
                $this->trans('Settings updated.', array(), 'Admin.Notifications.Success')
            );
        }

        if (is_array($this->templateFile)) {
            foreach ($this->templateFile as $template) {
                parent::_clearCache($template);
            }
        } else {
            parent::_clearCache($this->templateFile);
        }

        return $updated;
    }

    public function checkSetup($html = null)
    {
        foreach ($this->setup as $key => $value) {
            if (!Configuration::hasKey($key) &&
                !Configuration::hasKey($key, null, null, $this->context->shop->id) &&
                !Configuration::hasKey($key, $this->context->language->id) &&
                !Configuration::hasKey($key, $this->context->language->id, null, $this->context->shop->id) &&
                !Configuration::updateValue($key, $value, $html)
            ) {
                throw new \RuntimeException(" Value : $key updated, just reload the page for checking next values");
            }
        }

        return $this->setup;
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {

        if (!isset($this->templateFile[$hookName])) {
            $hookName = 'displayShoppingCartFooter';
        }

        if (isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        if ($this->context->controller->php_self !== 'cart') {
            return false;
        }

        $isRuleAllReadyUsed = false;
        foreach ($this->context->cart->getCartRules() as $ruleUsed) {
            if ($ruleUsed['id_cart_rule'] === Configuration::get('AO_GIFT_ID_RULE')) {
                $isRuleAllReadyUsed = true;
            }
        }

        $isFirstOrder = !(new Customer((int)$this->context->customer->id))->getStats()['nb_orders'];
        $isCartEmpty  = empty($this->context->cart->getProducts());
        $isModalAuthorised = ($isFirstOrder && !$isRuleAllReadyUsed && !$isCartEmpty);

        if (!$isModalAuthorised) {
            return false;
        }

        if (!$this->isCached($this->templateFile[$hookName], $hookName)) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $configuration));
        }

        return $this->fetch($this->templateFile[$hookName], $hookName);
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $request = array(
            'action' => 'show'
        );
        $urlAddRule = (new Link())->getPageLink('cart', null, null, $request, false, null, false);


        $context = $this->context;
        $assembler = new ProductAssembler($context);
        $presenterFactory = new ProductPresenterFactory($context);
        $presentationSettings = $presenterFactory->getPresentationSettings();

        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $context->link
            ),
            $context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $context->getTranslator()
        );

        $product_for_template = $presenter->present(
            $presentationSettings,
            $assembler->assembleProduct(array('id_product' => (int)Configuration::get('AO_GIFT_ID_PRODUCT'))),
            $context->language
        );

        return array(
            'urlCartAddRule' => $urlAddRule,
            'addDiscount' => 1,
            'discount_name' => (new CartRule((int)Configuration::get('AO_GIFT_ID_RULE')))->code,
            'token' => Tools::getToken(false),
            'setup_'.$this->name => $this->getSetup(),
            'product' => $product_for_template
        );
    }
}
