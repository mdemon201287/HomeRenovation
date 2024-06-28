<?php

class DefaultPageRouting{
  public function index(){
    header("Location: /rent_house_uiu/frontend/index.html");
    exit;
  }
}