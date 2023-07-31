<?php
/*
Plugin Name: ProdaLet
Plugin URI: https://prodalet.ru/cms-wordpress/plugin-wt-yandex-metrika-for-cms-wordpress
Description: Подключение сервиса повышения конверсии ProdaLet.ru
Version: 1
Author: ProdaLet
Author URI: https://prodalet.ru
*/

require('admin_panel.php');

class Prodalet
{
	var $admin;
	var $options;
	var $options_default = array(
		'script' => '',
		'position' => 'header',
		'mode' => 'all',
        'activate_admin_panel' => null
    );

	function __construct(){	
		add_action('init',array( $this, 'initial' ) );
	}

	public function initial(){
		$this->options = array_merge(
			$this->options_default, 
			(array) get_option('prodalet', array()) 
		);

		if (defined('ABSPATH') && is_admin()) $this->admin = new ProdaletAdmin();

		// Проверяем режим работы плагина
		if (is_user_logged_in() && current_user_can('administrator') && ($this->options['mode'] == 'admin_not_display'))
			return;

		// Выводим счетчик в панели администратора
        if (defined('ABSPATH') &&
            is_admin() &&
            !empty($this->options['activate_admin_panel']) &&
            $this->options['activate_admin_panel'] == 'on'){
            add_action('admin_footer', array(&$this, 'action_admin_footer'));
        }

		// Определяем расположение кода счетчика
		if ($this->options['position'] == 'header') add_action('wp_head', array($this, 'wp_head'), 4);
		else add_action('wp_footer', array($this, 'action_wp_footer'), 99);
	}

	public static function basename() {
        return plugin_basename(__FILE__);
    }

    // Подготавливаем код для вывода в шапке
    function wp_head() {
		if (!empty( $this->options['script'])) echo $this->options['script'];
	}

    // Подготавливаем код для вывода в подвале
    public function action_wp_footer() {
		if (!empty( $this->options['script'])) echo $this->options['script'];
	}

	// Подготавливаем код для вывода в панели администратора
    public function action_admin_footer(){
        if (!empty( $this->options['script'])) echo $this->options['script'];
    }

}

$prodalet = new Prodalet();