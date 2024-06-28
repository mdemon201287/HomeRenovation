<?php
require_once(__DIR__ . '/dbConnect.php');

class RegUserController{
  private $status;
  private $msg;
  private $accessToken;
  private $conn;

  public function __construct($token, $conn){
    $this->conn = $conn;
    $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
    $this->accessToken = $sytemConfig['APP_TOKEN'];
    if (!$this->validateToken($token)) {
      $this->unAutReq();
      exit();
    }
  }

  private function validateToken($token){
    return $token === $this->accessToken;
  }

  private function unAutReq(){
    $this->status = '401';
    $this->msg = 'Unauthorize access';
    $response = [
      'status' => $this->status,
      'msg' => $this->msg
    ];
    echo json_encode($response);
    http_response_code(401);
  }

  public function saveUser($data){
    if (isset($data->data)) {
      $name = $data->data->name ?? null;
      $email = $data->data->email ?? 'didnot@provide.com';
      $password = $data->data->password ?? null;
      $path = $data->data->path ?? 'user';
      if ($password !== null) {
        $final_password = md5($password);
      } else {
        $final_password = md5('dreamNest');
      }

      if ($path === 'home-decor' || $path === 'builder') {
        $user_status = 'Inactive';
      } else {
        $user_status = 'Active';
      }

      $prefix = "SNU";
      $timestamp = time();
      $randomNumber = mt_rand(1000, 9999);
      $uniqueIdentifier = uniqid();
      $serialNumber = $prefix . $timestamp . $randomNumber . $uniqueIdentifier;
      $serialNumber = preg_replace('/[.,\/\'"]/', '', $serialNumber);

      $emailExistsQuery = $this->conn->prepare("SELECT email FROM user WHERE email = ?");
      $emailExistsQuery->bind_param("s", $email);
      $emailExistsQuery->execute();
      $emailExistsResult = $emailExistsQuery->get_result();

      if ($emailExistsResult->num_rows > 0) {
        $this->status = '409';
        $this->msg = 'Email already exists';
        $response = [
          'status' => $this->status,
          'msg' => $this->msg
        ];
        echo json_encode($response);
        http_response_code(200);
      } else {
        $insertQuery = $this->conn->prepare("INSERT INTO user (sl, name, email, password, path, status) VALUES (?, ?, ?, ?, ?, ?)");
        $insertQuery->bind_param("ssssss", $serialNumber, $name, $email, $final_password, $path, $user_status);

        if ($insertQuery->execute()) {
          $this->status = '200';
          $this->msg = 'User saved successfully';
          $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
          $baseUrl = $sytemConfig['Base_Url'];
          $html = $baseUrl.'/frontend/page/login.html';
          $response = [
            'status' => $this->status,
            'msg' => $this->msg,
            'url' => file_get_contents($html),
            'data' => [
              'name' => $name,
              'email' => $email,
              'password' => $final_password,
              'path' => $path,
            ]
          ];
          echo json_encode($response);
          http_response_code(200);
        } else {
          $this->status = '500';
          $this->msg = 'Error saving user';
          $response = [
            'status' => $this->status,
            'msg' => $this->msg
          ];
          echo json_encode($response);
          http_response_code(500);
        }
      }
    } else {
      $this->status = '400';
      $this->msg = 'Invalid request data';
      $response = [
        'status' => $this->status,
        'msg' => $this->msg
      ];

      echo json_encode($response);
      http_response_code(400);
    }
  }

  public function logIn($data){
    if (isset($data->data)) {
      $email = $data->data->email ?? null;
      $password = $data->data->password ?? null;

      if ($email === null || $password === null) {
        $this->status = '409';
        $this->msg = 'Please Provide required data';
        $response = [
          'status' => $this->status,
          'msg' => $this->msg
        ];
        echo json_encode($response);
        http_response_code(200);
      } else {
        $emailExistsQuery = $this->conn->prepare("SELECT sl, password, path FROM user WHERE email = ?");
        $emailExistsQuery->bind_param("s", $email);
        $emailExistsQuery->execute();
        $emailExistsResult = $emailExistsQuery->get_result();

        if ($emailExistsResult->num_rows > 0) {
          $activeExistsQuery = $this->conn->prepare("SELECT sl, password, path FROM user WHERE email = ? AND status = 'Active'");
          $activeExistsQuery->bind_param("s", $email);
          $activeExistsQuery->execute();
          $activeExistsResult = $activeExistsQuery->get_result();
          if ($activeExistsResult->num_rows > 0) {
            $row = $activeExistsResult->fetch_assoc();
            $userPassword = $row['password'];
            $userPath = $row['path'];

            if (md5($password) === $userPassword) {
              $prefix = date('YmdHis');
              $timestamp = time();
              $randomNumber = mt_rand(1000, 9999);
              $uniqueIdentifier = uniqid();
              $serialNumber = $prefix . $timestamp . $randomNumber . $uniqueIdentifier;
              $accessToken = preg_replace('/[.,\/\'"]/', '', $serialNumber);

              $expirationTimestamp = strtotime('+1 year');

              $updateAccessTokenQuery = $this->conn->prepare(
                "UPDATE user SET access_token = ?, time_limit = ? WHERE email = ? AND status = 'Active'"
              );
              $updateAccessTokenQuery->bind_param("sss", $accessToken, $expirationTimestamp, $email);
              $updateAccessTokenQuery->execute();

              $this->status = '200';
              $this->msg = 'Login successful';
              $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
              $baseUrl = $sytemConfig['Base_Url'];
              $admin = $sytemConfig['Admin_Dash_url'];
              $builder = $sytemConfig['Builder_Dash_url'];
              $user = $sytemConfig['User_Dash_url'];
              $homeDecor = $sytemConfig['Home_decor_Dash_url'];
              if ($userPath === 'admin') {
                $html = $baseUrl. $admin;
              } elseif ($userPath === 'builder'){
                $html = $baseUrl. $builder;
              }elseif($userPath === 'user'){
                $html = $baseUrl . $user;
              } elseif ($userPath === 'home-decor') {
                $html = $baseUrl . $homeDecor;
              }
              $response = [
                'status' => $this->status,
                'msg' => $this->msg,
                'url' => $html,
                'accessToken' => $accessToken,
                'email' => $email,
                'path' => $userPath,
              ];
              echo json_encode($response);
              http_response_code(200);
            } else {
              $this->status = '401';
              $this->msg = 'Incorrect password';
              $response = [
                'status' => $this->status,
                'msg' => $this->msg
              ];
              echo json_encode($response);
              http_response_code(200);
            }
          }else{
            $this->status = '404';
            $this->msg = 'You are not allowed to acesse.Wait till admin approval';
            $response = [
              'status' => $this->status,
              'msg' => $this->msg
            ];
            echo json_encode($response);
            http_response_code(200);
          }
        } else {
          $this->status = '404';
          $this->msg = 'User not found';
          $response = [
            'status' => $this->status,
            'msg' => $this->msg
          ];
          echo json_encode($response);
          http_response_code(200);
        }
      }
    } else {
      $this->status = '400';
      $this->msg = 'Invalid request data';
      $response = [
        'status' => $this->status,
        'msg' => $this->msg
      ];

      echo json_encode($response);
      http_response_code(400);
    }
  }

  public function validateUser($accessToken, $email){
    $timeLimit = 365 * 24 * 60 * 60;

    $getUserQuery = $this->conn->prepare("SELECT access_token, time_limit, path FROM user WHERE email = ? AND status = 'Active'");
    $getUserQuery->bind_param("s", $email);
    $getUserQuery->execute();
    $getUserResult = $getUserQuery->get_result();

    if ($getUserResult->num_rows > 0) {
      $user = $getUserResult->fetch_assoc();
      $path = $user['path'];
      $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
      $baseUrl = $sytemConfig['Base_Url'];
      $admin = $sytemConfig['Admin_Dash_url'];
      $builder = $sytemConfig['Builder_Dash_url'];
      $userDashboard = $sytemConfig['User_Dash_url'];
      if ($path === 'admin') {
        $html = $baseUrl . $admin;
      } elseif ($path === 'builder') {
        $html = $baseUrl . $builder;
      } elseif ($path === 'user') {
        $html = $baseUrl . $userDashboard;
      }
      
      if ($accessToken === $user['access_token']) {
        $timeLimitExpiration = $user['time_limit']; 

        $currentTime = time();

        if ($currentTime <= $timeLimitExpiration) {
          $status = true;
        } else {
          $status = false;
        }
      } else {
        $status = false;
      }
    } else {
      $status = false;
      $path = null;
      $html = null;
    }

    $response = [
      'status' => $status,
      'path' => $path,
      'html' => $html
    ];

    echo json_encode($response);
    http_response_code(200);
  }

  public function logOutUser($accessToken, $email){
    $html = 'C:/xampp/htdocs/rent_house_uiu/frontend/index.html';
    $getUserQuery = $this->conn->prepare("SELECT * FROM user WHERE email = ? AND access_token = ?");
    $getUserQuery->bind_param("ss", $email, $accessToken);
    $getUserQuery->execute();
    $getUserResult = $getUserQuery->get_result();

    if ($getUserResult->num_rows > 0) {
      $updateQuery = $this->conn->prepare("UPDATE user SET access_token = NULL, time_limit = NULL WHERE email = ?");
      $updateQuery->bind_param("s", $email);
      $updateQuery->execute();

      $status = true;
    }else{
      $status = true;
    }

    $response = [
      'status' => $status,
      'html' => $html
    ];

    echo json_encode($response);
    http_response_code(200);
  }

}
