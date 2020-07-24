<?php

namespace App\Repositories;

use Pdo;
use PDOException;
use App\Database\DbProvider;
use App\Container;

class InvoiceRepository {
  
  private $_db;
  private $_logger;
  
  public function __construct (){
    $this->_db = DbProvider::get();
    $this->_logger = Container::get("logger");
  }
  
  public function update(int $invoiceId, string $account, string $totalCoins): bool { 
    
    $result = false;
    
    try {
      
      $this->_db->beginTransaction();
      
      $stmt = $this->_db->prepare("update site_donations set status = 4, coins_entregues = :totalCoins where protocolo = :invoiceId");
      $stmt->bindValue("invoiceId", $invoiceId, PDO::PARAM_INT);
      $stmt->bindValue("totalCoins", $totalCoins, PDO::PARAM_STR);
      $stmt->execute();
      
      $stmt2 = $this->_db->prepare("update site_balance set saldo = saldo + :totalCoins where account = :account", array(PDO::MYSQL_ATTR_FOUND_ROWS => true));
      $stmt2->bindValue("account", $account, PDO::PARAM_STR);
      $stmt2->bindValue("totalCoins", $totalCoins, PDO::PARAM_STR);
      $stmt2->execute();
      
      if($stmt2->rowCount() == 0) {
        $stmt3 = $this->_db->prepare("insert into site_balance (account, saldo) values (:account, :saldo)");
        $stmt3->bindValue("account", $account, PDO::PARAM_STR);
        $stmt3->bindValue("saldo", $totalCoins, PDO::PARAM_STR);
        $stmt3->execute();    
      }
      
      $this->_db->commit();
      $result = true;
      
    } catch(PDOException $ex) {
      $this->_db->rollBack();
      $this->_logger->error($ex->getMessage());
    }
    
    return $result;
    
  }  
  
  public function get(int $id) {
    
    $stmt = $this->_db->prepare("select protocolo, account, quant_coins + coins_bonus as 'coins_entregues', status from site_donations where protocolo = :id");
    $stmt->bindValue("id", $id, PDO::PARAM_INT);            
    $stmt->execute();
    $results = $stmt->fetch(PDO::FETCH_ASSOC); 
    return $results;
    
  }
  
  
}