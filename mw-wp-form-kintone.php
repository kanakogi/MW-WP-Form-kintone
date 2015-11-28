<?php

/*
Plugin Name: MW WP Form kintone
Plugin URI: http://www.kigurumi.asia
Description: MW WP Formからkintoneに情報と登録します。
Author: Nakashima Masahiro
Version: 1.0.0
Author URI: http://www.kigurumi.asia
License: GPLv2 or later
Text Domain: mwfk
 */
define('MWFK_VERSION', '1.0.0');
define('MWFK_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MWFK_PLUGIN_NAME', trim(dirname(MWFK_PLUGIN_BASENAME), '/'));
define('MWFK_PLUGIN_DIR', untrailingslashit(dirname(__FILE__)));
define('MWFK_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));
define('MWFK_TEXT_DOMAIN', 'mwfk');

class MW_WP_Form_kintone
{
    /**
     * __construct.
     */
    public function __construct()
    {
        //actions
      add_action('init', array($this, 'load_files'));
    }

    /**
     * load_files
     */
    public function load_files()
    {
        // Classes
        include_once MWFK_PLUGIN_DIR.'/classes/class.admin.php';
        include_once MWFK_PLUGIN_DIR.'/classes/class.api.php';
    }

    // プラグイン有効化時のデフォルトオプション
    private $default_options = array(
      'subdomain'       => null,
      'user_ID'         => null,
      'user_password'   => null,
      'api_token'       => null,
      'app_ID'          => null,
      'mwform_formkey ' => null,
    );

    /**
     * 翻訳用
     */
    public function e($text)
    {
        _e($text, MWFK_TEXT_DOMAIN);
    }

    public function _($text)
    {
        return __($text, MWFK_TEXT_DOMAIN);
    }

    /**
     * プラグインが有効化されたときに実行
     */
    public function activation_hook()
    {
        if (!get_option(MWFK_TEXT_NAME)) {
            update_option(MWFK_TEXT_NAME, $this->$default_options);
        }
    }

    /**
     * 無効化ときに実行
     */
    public function deactivation_hook()
    {
        delete_option(MWFK_TEXT_NAME);
    }

    /**
     * アンインストール時に実行
     */
    public function uninstall_hook()
    {
        delete_option(MWFK_TEXT_NAME);
    }
}
new MW_WP_Form_kintone();
