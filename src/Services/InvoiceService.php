<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;
use App\Container;
use PDOException;

class InvoiceService {
  
  private $_invoiceRepository;
  private $_logger;
    
  public function __construct() {        
    $this->_invoiceRepository = new InvoiceRepository();  
    $this->_logger = Container::get("logger");
  }
  
  public function update($invoiceId) : bool {
    
    $result = false;
    
    try {
      
      $invoice = $this->_invoiceRepository->get($invoiceId);
      
      if($invoice) {
          
        $status = $invoice["status"];
        
        if($status != 4) {
            
          $account = $invoice["account"];
          $totalCoins = $invoice["coins_entregues"];
          
          $this->_logger->info(sprintf("Cuenta: %s | TotalCoins: %s", $account, $totalCoins));
          
          if($this->_invoiceRepository->update($invoiceId, $account, $totalCoins)) {
            $this->_logger->info("Se actualizó la factura");    
          } 
          
        } else {
          $this->_logger->info("La factura ya estaba pagada");
        }
        
      } else {
        $this->_logger->info("No se encontró la factura");
      }
      
      $result = true;
      
    } catch (PDOException $ex) {
      $this->_logger->error(sprintf("%s %s(%s)", $ex->getMessage(), $ex->getFile(), $ex->getLine()));
      $result = false;
    }
    
    return $result;
    
  }
  
}