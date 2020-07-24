<?php

namespace App\Services;

use PhpImap\Exceptions\ConnectionException;
use PhpImap\Mailbox;
use App\Services\InvoiceService;
use App\Container;
  
class MailboxService {
  
  private $_mailbox;
  private $_invoiceService;
  private $_logger;
  
  public function __construct () {
    
    $this->_mailbox = new Mailbox(
      __CONFIG__["mailbox"]["host"],
      __CONFIG__["mailbox"]["user"],
      __CONFIG__["mailbox"]["password"]
    );
    
    $this->_invoiceService = new InvoiceService();
    $this->_logger = Container::get("logger");    
    
  }
  
  public function processMercadoPagoMessages() {
    
    $list_mail_id = $this->_mailbox->searchMailbox('FROM "info@mercadopago.com" SUBJECT "Recibiste un pago por Factura" UNSEEN');
    
    if(!$list_mail_id) {
      
      $this->_logger->info("No hay correos nuevos de MercadoPago");      
      
    } else {
      
      $this->_logger->info( count($list_mail_id) . " correo(s) nuevos de MercadoPago");
      
      foreach ($list_mail_id as $mail_id) {

        $email = $this->_mailbox->getMail( $mail_id, false );

        $subject =  (string) $email->subject;
        
        $invoiceId = $this->getMercadoPagoInvoice($subject);
        
        $this->_logger->info("FacturaId: " . $invoiceId);
        
        $updateResult = $this->_invoiceService->update($invoiceId);
        
        if($updateResult) {
          $this->_mailbox->markMailAsRead($mail_id);    
        }

      }

    }
    
  }
  
  public function processPaypalMessages() {
    
    $list_mail_id = $this->_mailbox->searchMailbox('FROM "service@paypal.com.ar" SUBJECT "Notificación de pago recibido" UNSEEN');

    if(!$list_mail_id) {
      
      $this->_logger->info("No hay correos nuevos de Paypal");      
      
    } else {
      
      $this->_logger->info( count($list_mail_id) . " correo(s) nuevos de Paypal");
     
      foreach ($list_mail_id as $mail_id) {

        $email = $this->_mailbox->getMail( $mail_id, false );
        
        $invoiceId = $this->getPaypalInvoice($email->textHtml);      

        $this->_logger->info("FacturaId: " . $invoiceId);
        
        $updateResult = $this->_invoiceService->update($invoiceId);
        
        if($updateResult) {
          $this->_mailbox->markMailAsRead($mail_id);    
        }
        

       }
      
      
    }
    
    
  }
  
  
  private function getMercadoPagoInvoice($subject): string {
      
    $subject = str_replace("Recibiste un pago por Factura ", "", $subject);
    
    $subject_parts = explode(" ", $subject);
    
    $invoiceId = $subject_parts[0];
       
    return $invoiceId;
    
  }
  
  private function getPaypalInvoice($messageContent): string {
        
    $posInvoice = strpos($messageContent, "Factura");
      
    $lineInvoice = substr ($messageContent, $posInvoice + 8, 10);
    
    $arrInvoiceLine = explode(" ", $lineInvoice);
    
    return $arrInvoiceLine[0];
    
  } 
 
}