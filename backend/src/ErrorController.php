<?php 

class ErrorController{
  public function notFound(){
    $response = [
      'status' => 4004,
      'msg' => 'end point not found'
    ];
    echo json_encode($response);
    http_response_code(404);
  }
}