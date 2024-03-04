<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class ProdaletAdmin {

    var $script_mode_array = array(
        'admin_not_display' => 'Отключать во время авторизации администратора сайта'
    );
 
    var $script_position_array = array(
        'footer' => 'Подключается в конце страницы',
        'header' => 'Подключается в начале странице',
    );

    function __construct() {

        // Добавляем страницу настроек в панель администратора
        add_action('admin_menu', array(&$this, 'admin_menu'));

        //Добавляем в описание плагина ссылку на справку.
        add_filter('plugin_row_meta', 'ProdaletAdmin::plugin_row_meta', 10, 2);

        add_action('admin_init', array(&$this, 'plugin_settings'));
    }

    function admin_menu() {
        // Добавляем в меню "Настройки" страницу настроек плагина
        add_options_page(
                'Подключение сервиса  ProdaLet',
                'Prodalet',
                'manage_options',
                'prodalet/setting.php',
                array(&$this, 'options_page_output')
        );
    }

    // Добавление ссылок к описанию плагина
    public static function plugin_row_meta($meta, $file) {
        if ($file == Prodalet::basename()) {
            // Ссылка на страницу настроек
            $meta[] = '<a href="options-general.php?page=prodalet/setting.php">Настройки и код установки</a>';
        }
        return $meta;
    }

    /**
     * Создаем страницу настроек плагина
     */
    function options_page_output() {
        ?>
        <div class="wrap">
            <h2><?php echo get_admin_page_title() ?></h2>

            <form action="options.php" method="POST">
        <?php
        settings_fields('prodalet_group');
        do_settings_sections('prodalet_page');
        submit_button();
        ?>
            </form>
        </div>
        <?php
    }

    /**
     * Регистрируем настройки.
     * Настройки будут храниться в массиве, а не одна настройка = одна опция.
     */
    function plugin_settings() {

        $domain = parse_url(site_url())['host'];

        // параметры: $option_group, $option_name, $sanitize_callback
        register_setting('prodalet_group', 'prodalet', array(&$this, 'sanitize_callback'));

        // параметры: $id, $title, $callback, $page
        add_settings_section('prodalet', '', array(&$this, 'display_setting_info'), 'prodalet_page');

        /* $field_params = array(
          'type'      => 'textarea',
          'id'        => 'script',
          'label_for' => 'script'
          );
          add_settings_field( 'script', 'Код установки ProdaLet', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params );
         */

        $field_params = array(
            'type' => 'text',
            'id' => 'script_api_key',
            'label_for' => 'script_api_key'
        );
        add_settings_field('script_api_key', 'API KEY ProdaLet', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);

        $field_params = array(
            'type' => 'text',
            'id' => 'script_api_secret',
            'label_for' => 'script_api_secret'
        );
        add_settings_field('script_api_secret', 'API SECRET ProdaLet', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);

        $field_params = array(
            'type' => 'select',
            'id' => 'position',
            'label_for' => 'position',
            'desc' => 'Выберите место расположения сервиса. Если установить наш код в начале странице - то виджеты будут показывать быстрее.', // описание
            'vals' => $this->script_position_array,
            'defautl' => 'footer',
        );
        add_settings_field('position', 'Подключение кода установки', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);

        $field_params = array(
            'type' => 'text',
            'id' => 'lazy_load_desctop',
            'desc' => 'Через сколько секунд после загрузки сайта будет подгружаться сервис Prodalet на компьютерах. 0 - сразу после загрузки. ', // описание
            'label_for' => 'lazy_load_desctop',
            'default' => 0,
        );
        add_settings_field('lazy_load_desctop', 'Отложенная загрузка для КОМПЬЮТЕРОВ, секунд ', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);

        $field_params = array(
            'type' => 'text',
            'id' => 'lazy_load_mobile',
            'desc' => 'Через сколько секунд после загрузки сайта на мобильных телефонах будет подгружаться сервис Prodalet . 0 - сразу после загрузки. ', // описание
            'label_for' => 'lazy_load_mobile',
            'default' => 0,
        );
        add_settings_field('lazy_load_mobile', 'Отложенная загрузка для МОБИЛЬНЫХ, секунд ', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);

        $field_params = array(
            'type' => 'checkbox-group',
            'id' => 'mode',
            'label_for' => 'mode',
            'desc' => 'Настройки для администратора',
            'vals' => $this->script_mode_array
        );
        add_settings_field('mode', 'Настройки для администратора', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);

        $field_params = array(
            'type' => 'checkbox',
            'id' => 'activate_admin_panel',
            'label_for' => 'activate_admin_panel',
            'desc' => 'Активировать виджеты в панели администратора.',
        );
        add_settings_field('activate_admin_panel', '', array(&$this, 'display_settings'), 'prodalet_page', 'prodalet', $field_params);
    }

    // Поясняющее сообщение для секции тестирования и отладки
    function display_setting_info() {
        $domain = parse_url(site_url())['host'];
        print "
    <p>Для работы виджетов вам необходимо получить API KEY и API SECRET на странице проекта</p> 
    <p>Кликните на название проекта (<b>\"{$domain}\"</b>) -> раздел 'API' или 'Код установки'</p>
    <p><b><a href='https://prodalet.ru/projects/api/" . $domain . "/'  target='_blank'>Перейдите в свой аккаунт ProdaLet.ru</a></b>
    </p>
    ";
    }

    /**
     * Функция отображения полей ввода
     * Здесь задаётся HTML и PHP, выводящий поля
     */
    function display_settings($args) {
        extract($args);

        $option_name = 'prodalet';

        $o = get_option($option_name);

        switch ($type) {
            case 'text':
                $o[$id] = esc_attr(stripslashes($o[$id]));
                if (isset($o[$id]) && $o[$id] !== '') {
                    $val = $o[$id];
                } elseif (isset($args['default'])) {
                    $val = $args['default'];
                }
                echo "<input class='regular-text' type='text' id='$id' name='" . $option_name . "[$id]' value='$val' />";
                echo (isset($args['desc'])) ? '<br /><span class="description">' . $args['desc'] . '</span>' : "";
                break;
            case 'textarea':
                $o[$id] = esc_attr(stripslashes($o[$id]));
                echo "<textarea class='code large-text' cols='30' rows='10' type='text' id='$id' name='" . $option_name . "[$id]'>$o[$id]</textarea>";
                echo (isset($args['desc'])) ? '<br /><span class="description">' . $args['desc'] . '</span>' : "";
                break;
            case 'checkbox':
                $checked = (!empty($o[$id]) && $o[$id] == 'on') ? " checked='checked'" : '';
                echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' $checked /> ";
                echo (isset($args['desc'])) ? $args['desc'] : "";
                echo "</label>";
                break;
            case 'checkbox-group':
                echo '<ul style="margin-top: 10px;">';
                foreach ($vals as $v => $l) {
                    echo '<li>';
                    $checked = (isset($o[$id]) && $o[$id] == $v) ? " checked='checked'" : '';
                    echo "<label><input type='checkbox' id='$id' name='" . $option_name . "[$id]' value='$v' $checked /> ";
                    echo ($l != '') ? $l : "";
                    echo "</label>";
                    echo '</li>';
                }
                echo '<ul>';

                break;
            case 'select':
                echo "<select id='$id' name='" . $option_name . "[$id]'>";
                foreach ($vals as $v => $l) {
                    $selected = ($o[$id] == $v) ? "selected='selected'" : '';
                    echo "<option value='$v' $selected>$l</option>";
                }
                echo "</select>";
                echo (isset($args['desc'])) ? '<br /><span class="description">' . $args['desc'] . '</span>' : "";
                break;
            case 'radio':
                echo "<fieldset>";
                foreach ($vals as $v => $l) {
                    $checked = ($o[$id] == $v) ? "checked='checked'" : '';
                    echo "<label><input type='radio' name='" . $option_name . "[$id]' value='$v' $checked />$l</label><br />";
                }
                echo "</fieldset>";
                break;
            case 'info':
                echo '<p>' . $text . '</p>';
                break;
        }
    }

    ## Очистка данных
    function sanitize_callback($options) {
        // очищаем
        foreach ($options as $name => & $val) {
            if ($name == 'input')
                $val = strip_tags($val);

            if ($name == 'checkbox')
                $val = (int) $val;
        }

        //die(print_r( $options ));

        return $options;
    }
}
