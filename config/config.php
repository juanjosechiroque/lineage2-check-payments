<?php

define("__CONFIG__", [
  "db" => [
    "host" => "mysql:host=localhost;dbname=database;charset=UTF8",
    "user" => "root",
    "password" => ""
  ],
  "mailbox" => [
    "host" => "smtp.server.com",
    "user" => "mail@mail.com",
    "password" => "password"    
  ],
  "log" => [
    "path" => "/logs/",
    "channel" => "app"
  ]
]);
