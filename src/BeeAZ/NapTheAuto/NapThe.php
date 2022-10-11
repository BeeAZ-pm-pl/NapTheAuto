<?php

namespace BeeAZ\NapTheAuto;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class NapThe extends PluginBase{
  
  protected $cfg;
  protected $data;
  public static $instance;
  public static $prefix = '§a§lＤｒａｇｏｎ§cＦｌａｍｅ §eＶＮ';
  
  public function onEnable(): void{
     self::$instance = $this;
     $this->saveDefaultConfig();
     $this->cfg = $this->getConfig();
     $this->data = new Config($this->getDataFolder()."dulieu.yml",Config::YAML);
  }

  public static function getInstance(){
      return self::$instance;
  }
  
  public function getMessage($text){
     return $this->cfg->get($text, "");
     
  }
  
  public function addReward(string $name, int $amount){
    if($this->getMessage("economy.use") == "EconomyAPI"){
    return $this->getServer()->getPluginManager()->getPlugin("EconomyAPI")->addMoney($name, $amount);
   }elseif($this->getMessage("economy.use") == "CoinAPI"){
    return $this->getServer()->getPluginManager()->getPlugin("CoinAPI")->addCoin($name, $amount);
   }elseif($this->getMessage("economy.use") == "PointAPI"){
    return $this->getServer()->getPluginManager()->getPlugin("PointAPI")->addPoint($name, $amount);
   }elseif($this->getMessage("economy.use") == "DFRuby"){
    return $this->getServer()->getPluginManager()->getPlugin("DFRuby")->addRuby($name, $amount, 'nap the nhan duoc '.$amount.' ruby');
  }
}

  public function onCommand(CommandSender $player, Command $cmd, string $label, array $args): bool{
    if(strtolower($cmd->getName()) === "napthe"){
    $this->napthe($player, $this->getMessage("description"));
    return true;
}
}
  
  public function napthe($player, $text){
    $form = new SimpleForm(function($player, $data){
    if($data === null) return true;
    match($data){
    0 => $this->open($player, ""),
    1 => $this->reward($player, $this->getMessage("reward"))
    };
    });
    $form->setTitle("§a§lNạp Thẻ");
    $form->setContent($text);
    $form->addButton("§f§l• §0Nạp Thẻ §f•");
    $form->addButton("§f§l• §0Giá Nạp §f•");
    $player->sendForm($form);
    }
    
  public function open($player, $text){
    $form = new CustomForm(function($player, $data){
    if($data === null) return true;
    if(!is_numeric($data[3])){
    $this->open($player, "Mã Thẻ Và Seri Phải Là Số");
    return true;
    }
    if(!is_numeric($data[4])){
    $this->open($player, "Mã Thẻ Và Seri Phải Là Số");
    return true;
    }
    if(strpos($data[3], ".") == true || strpos($data[4], ".") == true){
    $this->open($player, "Mã Thẻ Và Seri Phải Là Số Nguyên");
    return true;
    }
    $telco = ["Viettel", "Vinaphone", "VNMobi", "Zing", "Garena", "Mobifone"];
    $menhgia = ["10000", "20000", "30000", "50000", "100000", "200000", "300000", "500000"];
    $this->getServer()->getAsyncPool()->submitTask(new SendCard(strtoupper($telco[$data[1]]), (int)$menhgia[$data[2]], (int)$data[3], (int)$data[4], $this->getMessage("key"), $this->getMessage("id"), $player->getName()));
    });
    $form->setTitle("§a§lNạp Thẻ");
    $form->addLabel($text);
    $form->addDropdown("§e§lLoại Thẻ ", ["Viettel", "Vinaphone", "VNMobi", "Zing", "Garena", "Mobifone"]);
    $form->addDropdown("§e§lMệnh Giá ", ["10000", "20000", "30000", "50000", "100000", "200000", "300000", "500000"]);
    $form->addInput("§e§lSerial", "Nhập Serial");
    $form->addInput("§e§lMã Thẻ", "Nhập Mã Thẻ");
    $player->sendForm($form);
  }
  
  public function reward($player, $text){
    $form = new SimpleForm(function($player, $data){
    if($data === null) return true;
    });
    $form->setTitle("§a§lGiá Nạp");
    $form->setContent($text);
    $player->sendForm($form);
  }
  
  public function onSuccess($player, int $giatri, int $saimenhgia){
    $this->getServer()->broadcastMessage(str_replace("{PLAYER}", $player->getName(), $this->getMessage("broadcastsuccess")));
    if($this->getMessage("commands") !== ""){
       $this->getServer()->dispatchCommand(new ConsoleCommandSender($this->getServer(), $this->getServer()->getLanguage()), str_replace(["{PLAYER}","{AMOUNT}"], [$player->getName(), (($giatri / $this->getMessage("rate")) / $saimenhgia) * $this->getMessage("bonus")],$this->getMessage("commands")));
    }elseif($this->getMessage("economy") && $this->getMessage("commands") == ""){
       $this->addReward($player->getName(), (($giatri / $this->getMessage("rate")) / $saimenhgia) * $this->getMessage("bonus"));
    }
    $this->data->setNested("{$player->getName()}.amount", $giatri);
    $this->data->save();  
  }
}
