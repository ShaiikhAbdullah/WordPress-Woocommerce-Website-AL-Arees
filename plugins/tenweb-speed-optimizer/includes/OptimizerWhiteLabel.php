<?php

namespace TenWebOptimizer;

class OptimizerWhiteLabel
{
    private static $instance = null;

    private $white_labeled_plugins = [
        'tenweb-speed-optimizer/tenweb_speed_optimizer.php',
    ];

    private $whitelabeled_menus = [
        '10web Booster'
    ];

    private $top_bar_menus = [
        'two_options'
    ];

    private $company_name = TWO_SO_ORGANIZATION_NAME;

    public function register_hooks()
    {
        if (!defined('TWO_SO_ORGANIZATION_NAME')) {
            return;
        }

        add_action('pre_current_active_plugins', [$this, 'whiteLabelPluginsList'], 999999);
        add_action('admin_menu', [$this, 'whiteLabelTopMenus'], 999999);
        add_action('admin_bar_menu', [$this, 'whiteLabelAdminBarMenus'], 999999);
    }

    public function whiteLabelPluginsList()
    {
        global $wp_list_table;

        $plugins = $wp_list_table->items;

        $plugin = $plugins[$this->white_labeled_plugins[0]];
        $slug = $this->white_labeled_plugins[0];
        $main_php = explode('/', $slug);

        if (is_array($main_php)) {
            $main_php = end($main_php);
        }
        $name = str_ireplace('10web', $this->company_name, $plugin['Name']);
        $Description = str_ireplace('10web', $this->company_name, $plugin['Description']);

        $wp_list_table->items[$slug]['Name'] = $name;
        $wp_list_table->items[$slug]['Description'] = $Description;
        unset($wp_list_table->items[$slug]['Author']);
        unset($wp_list_table->items[$slug]['AuthorURI']);
        unset($wp_list_table->items[$slug]['AuthorName']);
        unset($wp_list_table->items[$slug]['PluginURI']);

        if (isset($wp_list_table->items[$slug]['slug'])) {
            unset($wp_list_table->items[$slug]['slug']);
        }
    }

    public function whiteLabelTopMenus()
    {
        global $menu;

        foreach ($menu as $key => $item) {
            if (str_ireplace($this->whitelabeled_menus, '', $item[0]) !== $item[0]) {
                $name = str_ireplace('10web', $this->company_name, $item[0]);
                $title = str_ireplace('10web', $this->company_name, $item[0]);
                $menu[$key][0] = $name; // phpcs:ignore
                $menu[$key][3] = $title; // phpcs:ignore
            }
        }
    }

    public function whiteLabelAdminBarMenus($admin_menu)
    {
        $wl_menu = $admin_menu->get_node($this->top_bar_menus[0]);

        if (!empty($wl_menu)) {
            $wl_menu->title = str_ireplace('10web', $this->company_name, $wl_menu->title);
            $admin_menu->add_node($wl_menu);
        }
    }

    public static function get_instance()
    {
        if (null == self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
