<?php

namespace BeeAZ\NapTheAuto;

use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\scheduler\AsyncTask;

class Callback extends AsyncTask{
  
  private $loaithe;
  private $menhgia;
  private $serial;
  private $mathe;
  private $id;
  private $key;
  private $player;
  private $request;
  
  public function __construct($loaithe, $menhgia, $serial, $mathe, $key, $id, $player, $request){
    $this->loaithe = $loaithe;
    $this->menhgia = $menhgia;
    $this->serial = $serial;
    $this->mathe = $mathe;
    $this->key = $key;
    $this->id = $id;
    $this->player = $player;
    $this->request = $request;
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
              "command" => "check"
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
    $player = Server::getInstance()->getPlayerByPrefix($this->player);
    if($player->isOnline()){
    if($this->getResult() !== null){
    if($this->getResult()["status"] == 99){
       Server::getInstance()->getAsyncPool()->submitTask(new Callback($this->loaithe, $this->menhgia, $this->serial, $this->mathe, $this->key, $this->id, $this->player, $this->request));
    }elseif($this->getResult()["status"] == 1){
       NapThe::getInstance()->onSuccess($player, $this->menhgia, 1);
       $player->sendMessage("Th??? ????ng");
    }elseif($this->getResult()["status"] == 2){
       NapThe::getInstance()->onSuccess($player, $this->menhgia, 2);
       $player->sendMessage("Th??? ????ng Nh??ng Sai M???nh Gi?? M???t 50%");
    }elseif($this->getResult()["status"] == 3){
       $player->sendMessage("Th??? L???i");
    }elseif($this->getResult()["status"] == 4){
       $player->sendMessage("H??? Th???ng N???p Th??? B???o Tr??");
    }elseif($this->getResult()["status"] == 100){
       $player->sendMessage("N???p Th??? Th???t B???i. L?? Do: ".$this->getResult()["message"]);
    }
    }else{
      $player->sendMessage("Web N???p Th??? Kh??ng Th??? K???t N???i. Vui L??ng Th??? L???i Sau V??i Gi??y");
    }
   }
  }
 }