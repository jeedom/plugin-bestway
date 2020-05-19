<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class bestway extends eqLogic {
  /*     * *************************Attributs****************************** */
  
  
  
  /*     * ***********************Methode static*************************** */
  
  public static function getBaseApi(){
    switch (config::byKey('location','bestway')) {
      case 'eu':
      return 'https://euapi.gizwits.com';
      break;
      case 'us':
      return 'https://usapi.gizwits.com';
      break;
      case 'cn':
      return 'https://api.gizwits.com';
      break;
    }
  }
  
  public static function getUserToken(){
    $cache = is_json(cache::byKey('bestway::token')->getValue(),array());
    if(isset($cache['expire_at']) && $cache['expire_at'] > strtotime('now')){
      return $cache['token'];
    }
    if(config::byKey('username','bestway') == '' || config::byKey('password','bestway') == ''){
      throw new \Exception(__('Nom d\'utilisateur ou mot de passe vide'));
    }
    $request_http = new com_http(self::getBaseApi().'/app/login');
    $request_http->setHeader(array(
      'Content-Type: text/plain',
      'X-Gizwits-Application-Id: '.config::byKey('gizwitsappid','bestway')
    ));
    $request_http->setPost(json_encode(array('username' => config::byKey('username','bestway'),'password' => config::byKey('password','bestway'))));
    $result = json_decode($request_http->exec(30),true);
    if(isset($result['error_message'])){
      throw new \Exception($result['error_message']);
    }
    cache::set('bestway::token',json_encode($result));
    return $result['token'];
  }
  
  public static function requestApi($_url,$_post = null){
    $request_http = new com_http(self::getBaseApi().$_url);
    $request_http->setHeader(array(
      'X-Gizwits-Application-Id: '.config::byKey('gizwitsappid','bestway'),
      'X-Gizwits-User-token: '.self::getUserToken()
    ));
    if($_post != null){
      $request_http->setPost($_post);
    }
    $result = json_decode($request_http->exec(30),true);
    return $result;
  }
  
  public static function sync(){
    $devices = self::requestApi('/app/bindings');
    foreach ($devices['devices'] as $device) {
      $eqLogic = self::byLogicalId($device['did'], 'bestway');
      if (!is_object($eqLogic)) {
        $eqLogic = new self();
        $eqLogic->setLogicalId($device['did']);
        $eqLogic->setName($device['product_name']);
        $eqLogic->setEqType_name('bestway');
        $eqLogic->setIsVisible(1);
        $eqLogic->setIsEnable(1);
      }
      $eqLogic->setConfiguration('wifi_soft_version', $device['wifi_soft_version']);
      $eqLogic->setConfiguration('mac', $device['mac']);
      $eqLogic->save();
    }
  }
  
  public static function devicesParameters($_device = '') {
    $return = array();
    foreach (ls(dirname(__FILE__) . '/../config/devices/', '*.json') as $file) {
      try {
        $content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $file);
        $return += is_json($content, array());
      } catch (Exception $e) {
        
      }
    }
    if (isset($_device) && $_device != '') {
      if (isset($return[$_device])) {
        return $return[$_device];
      }
      return array();
    }
    return $return;
  }
  
  public function cron15(){
    foreach (eqLogic::byType('bestway',true) as $bestway) {
      try {
        $bestway->refresh();
      } catch (\Exception $e) {
        log::add('dyson','error',$dyson->getHumanName().' '.$e->getMessage());
      }
    }
  }
  
  /*     * *********************Méthodes d'instance************************* */
  
  public function postSave() {
    if ($this->getConfiguration('applyDevice') != $this->getConfiguration('device')) {
      $this->applyModuleConfiguration();
    }
    $refresh = $this->getCmd(null, 'refresh');
    if (!is_object($refresh)) {
      $refresh = new bestwayCmd();
    }
    $refresh->setName(__('Rafraîchir', __FILE__));
    $refresh->setEqLogic_id($this->getId());
    $refresh->setLogicalId('refresh');
    $refresh->setType('action');
    $refresh->setSubType('other');
    $refresh->save();
  }
  
  public function applyModuleConfiguration() {
    $this->setConfiguration('applyDevice', $this->getConfiguration('device'));
    $this->save();
    if ($this->getConfiguration('device') == '') {
      return true;
    }
    $device = self::devicesParameters($this->getConfiguration('device'));
    if (!is_array($device)) {
      return true;
    }
    $this->import($device);
  }
  
  public function getImgFilePath() {
    if (file_exists(dirname(__FILE__) . '/../../core/config/devices/' . $this->getConfiguration('device') . '.png')) {
      return $this->getConfiguration('device') . '.png';
    }
    return false;
  }
  
  public function getImage() {
    $imgpath = $this->getImgFilePath();
    if ($imgpath === false) {
      return 'plugins/bestway/plugin_info/bestway_icon.png';
    }
    return 'plugins/bestway/core/config/devices/' . $imgpath;
  }
  
  public function refresh(){
    $data = self::requestApi('/app/devdata/'.$this->getLogicalId().'/latest');
    $date = date('Y-m-d H:i:s',$data['updated_at']);
    foreach ($this->getCmd('info') as $cmd){
      if(isset($data['attr'][$cmd->getLogicalId()])){
        $value = $data['attr'][$cmd->getLogicalId()];
        $this->checkAndUpdateCmd($cmd, $value,$date);
      }
    }
  }
  
  /*     * **********************Getteur Setteur*************************** */
}

class bestwayCmd extends cmd {
  /*     * *************************Attributs****************************** */
  
  
  /*     * ***********************Methode static*************************** */
  
  
  /*     * *********************Methode d'instance************************* */
  
  
  public function execute($_options = array()) {
    $eqLogic = $this->getEqLogic();
    if($this->getLogicalId() != 'refresh'){
      
    }
    return $eqLogic->refresh();
  }
  
  /*     * **********************Getteur Setteur*************************** */
}