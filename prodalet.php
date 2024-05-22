<?php

/*
  Plugin Name: ProdaLet
  Description: Подключение сервиса повышения конверсии ProdaLet.ru
  Version: 3.0.0
  Author: ProdaLet
  Author URI: https://prodalet.ru
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require('admin_panel.php');

class Prodalet {

    var $admin;
    var $options;
    var $options_default = array(
        'script' => '',
        'position' => 'header',
        'mode' => 'all',
        'lazy_load_desctop' => 0,
        'lazy_load_mobile' => 0,
        'activate_admin_panel' => null
    );

    function __construct() {
        add_action('init', array($this, 'initial'));
    }

    public function initial() {
        $this->options = array_merge(
                $this->options_default,
                (array) get_option('prodalet', array())
        );

        if (defined('ABSPATH') && is_admin())
            $this->admin = new ProdaletAdmin();

        // Проверяем режим работы плагина
        if (is_user_logged_in() && current_user_can('administrator') && ($this->options['mode'] == 'admin_not_display'))
            return;

        // Выводим счетчик в панели администратора
        if (defined('ABSPATH') &&
                is_admin() &&
                !empty($this->options['activate_admin_panel']) &&
                $this->options['activate_admin_panel'] == 'on') {
            add_action('admin_footer', array(&$this, 'action_admin_footer'));
        }

        // Определяем расположение кода счетчика
        if ($this->options['position'] == 'header')
            add_action('wp_head', array($this, 'wp_head'), 4);
        else
            add_action('wp_footer', array($this, 'action_wp_footer'), 99);
    }

    public static function basename() {
        return plugin_basename(__FILE__);
    }

    // Подготавливаем код для вывода в шапке
    function wp_head() {
        $this->renderCode();
    }

    // Подготавливаем код для вывода в подвале
    public function action_wp_footer() {
        $this->renderCode();
    }

    // Подготавливаем код для вывода в панели администратора
    public function action_admin_footer() {
        $this->renderCode();
    }

    public function renderCode() {
        if (preg_match('~\.int$~', $_SERVER['HTTP_HOST'])) {
            $dbg = true;
            $host = 'http://prodalet.int';
        } else {
            $host = 'https://prodalet.ru';
        }

        $api_key = $this->options['script_api_key'];
        $lazy_load_time = 0;

        if ($user = wp_get_current_user()) {
            $js_client_info = array(
                'first_name' => '',
                'last_name' => '',
                'phone' => '',
                'email' => $user->data->user_email,
                'client_id' => $user->data->ID,
                'guest_id' => ""
            );
        } else {
            //Если пользователь неизвестен
            $js_client_info = array(
                'first_name' => "",
                'last_name' => "",
                'phone' => "",
                'email' => "",
                'client_id' => "",
                'guest_id' => ""
            );
        }

        $js_client_info = json_encode($js_client_info);
        $js_client_info_query = "PLTQ.push(['api', 'setClientInfo', " . $js_client_info . "]);";

        //Формируем код для встраивания в страницу
        $ret = "
<!--Start Prodalet code WordPress plugin vers 3.0 {literal}-->
<script>
    lazy=0;lM=" . $this->options['lazy_load_mobile'] * 1000 . "; lD=" . $this->options['lazy_load_desctop'] * 1000 . "; if( window.matchMedia(\"only screen and (max-width: 760px)\").matches ) {lazy = lM;} else {lazy = lD;}
    setTimeout(function (){ 
        var PLTQ = []; {$js_client_info_query} var d = new Date().getTime();
        
        (function (d,w) { 
            var n = d.getElementsByTagName(\"script\")[0], 
            s = d.createElement(\"script\"), 
            f = function () { n.parentNode.insertBefore(s, n); }; 
            var dt = new Date().getTime(); 
            s.type = \"text/javascript\"; 
            s.id=\"plt_main\"; 
            s.src=\"{$host}/bundles/api/main.js?h=\"+dt; 
            s.setAttribute(\"data-plugin\", \"wordpress\"); 
            s.setAttribute(\"data-pltkey\", \"{$api_key}\"); 
            s.setAttribute(\"data-plthost\",\"{$host}\"); 
            s.async = true; 
            if (w.opera == \"[object Opera]\") { 
                d.addEventListener(\"DOMContentLoaded\", f, false); 
            } else { 
                f(); 
            } })(document,window);{$js_ready}
        }
        
    ,lazy);</script>
<!--End Prodalet code {/literal}-->";

        print $ret;
    }
}

$prodalet = new Prodalet();
