<?php

class MW_WP_Form_kintone_API
{
    private $user_ID = '';
    private $user_password = '';
    private $subdomain = '';
    private $host_name = '';
    private $api_record_url = '';
    private $api_records_url = '';
    private $api_token = '';
    private $app_ID = '';
    private $mwform_formkey = '';

    /**
     * __construct.
     */
    public function __construct()
    {
      //保存されているデータを取得
      $options = get_option(MWFK_PLUGIN_NAME);
      $this->subdomain = $options['subdomain'];
      $this->user_ID = $options['user_ID'];
      $this->user_password = $options['user_password'];
      $this->api_token = $options['api_token'];
      $this->app_ID = $options['app_ID'];
      $this->mwform_formkey = $options['mwform_formkey'];

      //API URLs
      $this->host_name = $this->subdomain.'.cybozu.com';
      $this->api_record_url = 'https://'.$this->host_name.'/k/v1/record.json';
      $this->api_records_url = 'https://'.$this->host_name.'/k/v1/records.json';

      //filters
      add_filter( 'mwform_admin_mail_mw-wp-form-'.$this->mwform_formkey,  array($this, 'mwform_auto_mail'), 10 ,3 );
    }

    /**
     * MW FormのフィルターでKintoneにデータを投げる
     */
    public function mwform_auto_mail( $Mail, $values, $Data ){
      $this->post($values);

      return $Mail;
    }

    /**
     * 投稿用のheader情報を取得.
     */
    private function get_headers()
    {
        $headers = array(
            'X-Cybozu-Authorization:'.base64_encode($this->user_ID.':'.$this->user_password),
            'Authorization: Basic '.base64_encode($this->user_ID.':'.$this->user_password),
            'X-Cybozu-API-Token: '.$this->api_token,
            'Host: '.$this->host_name.':443',
            'Content-Type: application/json',
        );

        return $headers;
    }

    /**
     * kintoneからデータを取得.
     */
    private function request_api($url, $fields, $request = 'post')
    {
        //headerを取得
        $headers = $this->get_headers();

        $curl = curl_init();
        if ($request == 'post') {
            //追加時
            curl_setopt($curl, CURLOPT_POST, true);
        } elseif ($request == 'put') {
            //更新時
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        } elseif ($request == 'get') {
            //取得時
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function post($values)
    {
        // kintoneに投げるデータを作成
        $record = array();
        foreach ($values as $key => $value) {
          //チェックボックス対策で値が配列かどうかチェックする
          if( !is_array($value) ){
            //テキスト等はそのままでOK
            $record[$key] = array('value' => $value);
          }else{
            //配列はチェックボックスなのでデータを整形
            foreach ( $value as $key2 => $items ) {
              if( $key2 == 'data'){
                $checkboxes = array();
                foreach ($items as $item) {
                  $checkboxes[] = $item;
                }
                $record[$key] = array('value' => $checkboxes);
              }
            }
          }
        }

        $fields = array(
            'app' => $this->app_ID,
            'record' => $record,
        );

        $response = $this->request_api($this->api_record_url, $fields);
        return $response;
    }
}
new MW_WP_Form_kintone_API();
