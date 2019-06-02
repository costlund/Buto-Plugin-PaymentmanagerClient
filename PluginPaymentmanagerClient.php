<?php
require_once __DIR__.'/mysql/db.php';
class PluginPaymentmanagerClient{
  public $data = null;
  public $settings = null;
  public $user = null;
  public $db = null;
  function __construct($data = array()){
    wfPlugin::includeonce('wf/array');
    wfPlugin::includeonce('wf/yml');
    /**
     * data
     */
    if($data === true){$data = array();} //Buto fix.
    $this->data = new PluginWfArray(array('DIR' => __DIR__));
    foreach ($data as $key => $value) {
      $this->data->set($key, $value);
    }
    $this->settings = wfPlugin::getPluginSettings('paymentmanager/client', true);
    $this->data->set('mysql', $this->settings->get('settings/mysql'));
    $this->db = new db_paymentmanager($this->settings);
    /**
     * 
     */
    wfPlugin::includeonce('i18n/translate_v1');
    $this->i18n = new PluginI18nTranslate_v1();
    /**
     * User
     */
    $this->user = wfUser::getSession();
  }
  public function event_signin(){
    wfUser::setSession('plugin/paymentmanager/client/service', $this->db->service_select_for_session());
  }
  /**
   * Level:
   * 1=Has no service.
   * 2=Has service but soon outdated.
   * 3=Has service and NOT soon outdated.
   */
  public function widget_service($data){
    $data = new PluginWfArray($data);
    $rs = new PluginWfArray();
    if($this->user->get('plugin/paymentmanager/client/service/'.$data->get('data/service'))){
      $rs = new PluginWfArray($this->user->get('plugin/paymentmanager/client/service/'.$data->get('data/service')));
    }
    $rs->set('url_payment', $this->settings->get('settings/url_payment').'?service_id='.$data->get('data/service'));
    if(!$rs->get('id') || $rs->get('days_left')<=$this->settings->get('settings/renew_days')){
      $rs->set('renew', true);
    }else{
      $rs->set('renew', false);
    }
    $rs->set('no_service', $data->get('data/no_service'));
    //wfHelp::yml_dump($rs, true);
    //echo '<pre>'; print_r($rs);
    $element = new PluginWfYml(__DIR__.'/element/service.yml');
    $element->setByTag($rs->get(), 'rs', true);
    wfDocument::renderElement($element->get());
  }
  public function widget_payment($data){
    $data = new PluginWfArray($data);
    /**
     * Update session.
     */
    $this->event_signin();
    /**
     * Session.
     */
    $user = wfUser::getSession();
    $session_data = new PluginWfArray($user->get('plugin/paymentmanager/client/service/'.wfRequest::get('service_id')));
    /**
     * Service.
     */
    $service_now = $this->db->service_select_for_payment($session_data->get('id'));
    $service = $this->db->service_select_one(wfRequest::get('service_id'));
    /**
     * Dates.
     */
    if($service_now->get('days_left') > 0){
      $date_from = date('Y-m-d', strtotime(date('Y-m-d'). " + ".($service_now->get('days_left')+1)." days"));
    }else{
      $date_from = date('Y-m-d');
    }
    $date_to = date('Y-m-d', strtotime($date_from. " + ".$service->get('period')." days"));
    /**
     * Set dates.
     */
    $service->set('date_from', $date_from);
    $service->set('date_to', $date_to);
    /**
     * Renew.
     */
    if(!$service_now->get('id') || $service_now->get('days_left')<=$this->settings->get('settings/renew_days')){
      $service->set('renew', true);
    }else{
      $service->set('renew', false);
    }
    /**
     * If no service.
     */
    if(!$service_now->get('id')){
      $service_now = new PluginWfArray(array('id' => null));
    }
    /**
     * Element.
     */
    $element = new PluginWfYml(__DIR__.'/element/payment.yml');
    $element->setByTag($service_now->get());
    $element->setByTag($service->get(), 'service');
    $element->setByTag($this->settings->get('settings'), 'settings');
    wfDocument::renderElement($element->get());
  }
}
