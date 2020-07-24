<?php declare (strict_types = 1);

require __DIR__ . "/../vendor/autoload.php";
require "../config/config.php";

use App\Services\MailboxService;
use App\Container;

Container::set("logger", function () {
  $logger = new \Monolog\Logger(__CONFIG__["log"]["channel"]);
  $file_handler = new \Monolog\Handler\StreamHandler(__DIR__ . "/../" . __CONFIG__["log"]["path"] . date("Y-m-d") . ".log");  
  $dateFormat = "Y-m-d H:i:s";  
  $output = "[%datetime%] %level_name% - %message% \n";  
  $formatter = new \Monolog\Formatter\LineFormatter($output, $dateFormat);  
  $file_handler->setFormatter($formatter);   
  $logger->pushHandler($file_handler);
  return $logger;
});

$mailboxService = new MailboxService();
$mailboxService->processMercadoPagoMessages();
$mailboxService->processPaypalMessages();