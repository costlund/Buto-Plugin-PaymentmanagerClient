<?php
class db_paymentmanager{
  public $mysql = null;
  public $conn = null;
  public $settings = null;
  //public $registration_from_days = null;
  //public $registration_to_hours = null;
  private $i18n = null;
  function __construct($settings = null){
    $this->settings = $settings;
    $this->conn = $this->settings->get('settings/mysql');
    wfPlugin::includeonce('wf/mysql');
    $this->mysql = new PluginWfMysql();
    wfPlugin::includeonce('i18n/translate_v1');
    $this->i18n = new PluginI18nTranslate_v1();
  }
  public function db_open(){
    $this->mysql->open($this->conn);
  }
  public function sql_get($key){
    $sql = new PluginWfYml(__DIR__.'/sql.yml', $key);
    $replace = new PluginWfYml(__DIR__.'/sql.yml', 'replace');
    $replace->set('customer_session_tag', $this->settings->get('settings/customer_session_tag'));
    /**
     * Replace sql.
     */
    if($replace->get()){
      foreach ($replace->get() as $key => $value) {
        if(!is_array($value)){
          $temp = $sql->get('sql');
          $temp = str_replace('['.$key.']', $value, $temp);
          $sql->set('sql', $temp);
        }
      }
    }
    /**
     * Replace select.
     */
    if($replace->get()){
      if($sql->get('select') && !is_array($sql->get('select'))){
        $sql->set('select',    $replace->get($sql->get('select')) );
      }
    }
    //return new PluginWfYml(__DIR__.'/sql.yml', $key);
    return $sql;
  }
  private function getSelect($sql){
    $rs = array();
    foreach ($sql->get('select') as $key => $value) {
      $rs[$value] = null;
    }
    return new PluginWfArray($rs);
  }
  public function service_select_for_session(){
    $this->db_open();
    $sql = $this->sql_get('service_select_for_session');
    $this->mysql->execute($sql->get());
    $rs2 = array();
    foreach ($this->mysql->getStmtAsArray() as $key => $value) {
      $rs2[$value['service_id']] = $value;
    }
    return $rs2;
  }
  public function service_select_for_payment($id){
    $this->db_open();
    $sql = $this->sql_get('service_select_for_payment');
    $sql->setByTag(array('id' => $id));
    $this->mysql->execute($sql->get());
    return new PluginWfArray($this->mysql->getStmtAsArrayOne());
  }
  public function service_select_one($id){
    $this->db_open();
    $sql = $this->sql_get('service_select_one');
    $sql->setByTag(array('id' => $id));
    $this->mysql->execute($sql->get());
    return new PluginWfArray($this->mysql->getStmtAsArrayOne());
  }
  public function payment_insert_one($data){
    $this->db_open();
    $sql = $this->sql_get('payment_insert_one');
    $sql->setByTag($data);
    $this->mysql->execute($sql->get());
    return null;
  }
}
