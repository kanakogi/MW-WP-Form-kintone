<?php
class MW_WP_Form_kintone_Admin
{

    /**
     * __construct
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_pages'));

      // プラグインページのみに制限
      if (isset($_REQUEST['page']) && $_REQUEST['page'] == MWFK_PLUGIN_NAME) {
          add_action('admin_notices', array($this, 'admin_notices'));
          add_action('admin_init', array($this, 'admin_post'));
      }
    }

    /**
     * 管理画面に設定ページを追加
     */
    public function add_pages()
    {
        if (class_exists('MW_WP_Form')) {
            add_submenu_page(
                  'edit.php?post_type=mw-wp-form',
                  'kintone',
                  'kintone',
                  'edit_pages',
                  'mw-wp-form-kintone',
                  array($this, 'options_page')
              );
        }
    }

    /**
     * POST時
     */
    public function admin_post()
    {
        if (isset($_POST['_wpnonce']) && $_POST['_wpnonce']) {
            $errors = new WP_Error();
            $updates = new WP_Error();

            if (check_admin_referer('mw-form-kintone', '_wpnonce')) {
                $options = get_option(MWFK_PLUGIN_NAME);
                $options['subdomain'] = esc_html($_POST['subdomain']);
                $options['user_ID'] = esc_html($_POST['user_ID']);
                $options['user_password'] = esc_html($_POST['user_password']);
                $options['api_token'] = esc_html($_POST['api_token']);
                $options['app_ID'] = esc_html($_POST['app_ID']);
                $options['mwform_formkey'] = esc_html($_POST['mwform_formkey']);
                update_option(MWFK_PLUGIN_NAME, $options);

                $updates->add('update', '保存しました');
                set_transient('mwfk-updates', $updates->get_error_messages(), 1);
            } else {
                $errors->add('error', 'エラーです');
                set_transient('mwfk-errors', $errors->get_error_messages(), 1);
            }
        }
    }

    /**
     * アップデート表示
     */
    public function admin_notices()
    {
        if ($messages = get_transient('mwfk-updates')): ?>
    <div class="updated">
        <ul>
            <?php foreach ($messages as $key => $message) : ?>
            <li><?php echo esc_html($message) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
    <?php endif ?>

 <?php if ($messages = get_transient('mwfk-errors')): ?>
    <div class="error">
        <ul>
            <?php foreach ($messages as $key => $message) : ?>
            <li><?php echo esc_html($message);
        ?></li>
            <?php endforeach;
        ?>
        </ul>
    </div>
    <?php endif;
    }

    /**
     * options_page
     */
    public function options_page()
    {
      $options = get_option(MWFK_PLUGIN_NAME);
        ?>

<div class="wrap">
<h2>kintone設定</h2>
<p>
kintoneの「フィールドコード」と、MW WP Formの各フォームタグの「name」が一致したものが保存されます。
</p>

<hr>

<p>
  kintone APIを使うための情報を設定します。
</p>
<form method="post" action="">
<?php wp_nonce_field('mw-form-kintone', '_wpnonce') ?>

<h3>kintone ユーザー認証</h3>
<table class="form-table">

<tr>
<th class="row">サブドメイン名</th>
<td>
<label><input type="text" name="subdomain" value="<?php echo $options['subdomain'] ?>" class="regular-text" placeholder="サブドメインの文字列"></label>
<br>https://サブドメイン名.cybozu.com
</td>
</tr>

<tr>
<th class="row">ログイン名</th>
<td>
<label><input type="text" name="user_ID" value="<?php echo $options['user_ID'] ?>" class="regular-text"></label>
</td>
</tr>

<tr>
<th class="row">パスワード</th>
<td>
<label><input type="password" name="user_password" value="<?php echo $options['user_password'] ?>" class="regular-text"></label>
</td>
</tr>

<tr>
<th class="row">APIトークン</th>
<td>
<label><input type="text" name="api_token" value="<?php echo $options['api_token'] ?>" class="regular-text"  placeholder="例: 4xXAhPtB4BbP2gcMMyd1NtlUCgdabjafja"></label>
</td>
</tr>

<tr>
<th class="row">APP ID</th>
<td>
<label><input type="text" name="app_ID" value="<?php echo $options['app_ID'] ?>" class="regular-text"  placeholder="例: 12"></label>
</td>
</tr>

</table>

<hr>

<h3>MW WP Form情報</h3>
<table class="form-table">

<tr>
<th class="row">フォーム識別子</th>
<td>
<label><input type="text" name="mwform_formkey" value="<?php echo $options['mwform_formkey'] ?>" class="regular-text" placeholder="例: 156"></label>
</td>
</tr>

</table>


<p class="submit"><input type="submit" name="submit" value="保存" class="button-primary" /></p>
</form>
</div><!-- /.wrap -->
<?php

    }
}
new MW_WP_Form_kintone_Admin();
