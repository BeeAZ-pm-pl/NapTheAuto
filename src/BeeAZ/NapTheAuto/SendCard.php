<?php

namespace BeeAZ\NapTheAuto;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

class SendCard extends AsyncTask{
  
  private $loaithe;
  private $menhgia;
  private $serial;
  private $mathe;
  private $id;
  private $key;
  private $player;
  private $request;
  
  public function __construct($loaithe, $menhgia, $serial, $mathe, $key, $id, $player){
    $this->loaithe = $loaithe;
    $this->menhgia = $menhgia;
    $this->serial = $serial;
    $this->mathe = $mathe;
    $this->key = $key;
    $this->id = $id;
    $this->player = $player;
    $this->request = intval(time());
   }
   
  public function onRun(): void{
    $url = 'https://vuathe.net/chargingws/v2';
    $data_sign = md5($this->key . $this->mathe . $this->serial);    
    $arrayPost = array(
              "telco" => $this->loaithe,
              "code" => $this->mathe,
              "serial" => $this->serial,
              "amount" => $this->menhgia,
              "request_id" => $this->request,
              "partner_id" => $this->id,
              "sign" => $data_sign,
              "command" => "charging"
            );
    $curl = curl_init($url);
            curl_setopt_array($curl, array(
              CURLOPT_POST => true,
              CURLOPT_HEADER => false,
              CURLINFO_HEADER_OUT => true,
              CURLOPT_TIMEOUT => 120,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_SSL_VERIFYPEER => false,
              CURLOPT_POSTFIELDS => http_build_query($arrayPost)
            ));
            $data = curl_exec($curl);
            if(isset($data)){
            $this->setResult(json_decode($data, true));
     }
  }
  
  public function onCompletion(): void{
    var_dump($this->getResult());
    $player = Server::getInstance()->getPlayerByPrefix($this->player);
    if($player->isOnline()){
    if($this->getResult() !== null){
    if($this->getResult()["status"] == 99){
       Server::getInstance()->getAsyncPool()->submitTask(new Callback($this->loaithe, $this->menhgia, $this->serial, $this->mathe, $this->key, $this->id, $this->player, $this->request));
       $player->sendMessage($this->getResult()["message"]);
   }else
       $player->sendMessage($this->getResult()["message"]);
   }else
       $player->sendMessage("Web Nạp Thẻ Không Thể Kết Nối. Vui Lòng Thử Lại Sau Vài Giây");
    }
  }
}
