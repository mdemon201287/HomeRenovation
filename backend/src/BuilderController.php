<?php

class BuilderController
{
  private $status;
  private $msg;
  private $accessToken;
  private $conn;
  private $userAccessToken;
  private $email;

  public function __construct($token, $userAccessToken, $email, $conn)
  {
    $this->conn = $conn;
    $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
    $this->accessToken = $sytemConfig['APP_TOKEN'];
    if (!$this->validateToken($token)) {
      $this->unAutReq();
      exit();
    }

    $this->userAccessToken = $userAccessToken;
    $this->email = $email;
  }

  private function validateToken($token)
  {
    return $token === $this->accessToken;
  }

  private function userValidation($userAccessToken, $email)
  {
    $timeLimit = 365 * 24 * 60 * 60;

    $getUserQuery = $this->conn->prepare("SELECT access_token, time_limit, path FROM user WHERE email = ? AND access_token = ?");
    $getUserQuery->bind_param("ss", $email, $userAccessToken);
    $getUserQuery->execute();
    $getUserResult = $getUserQuery->get_result();
    if ($getUserResult->num_rows > 0) {
      return true;
    } else {
      return false;
    }
  }

  private function unAutReq()
  {
    $this->status = '401';
    $this->msg = 'Unauthorize access';
    $response = [
      'status' => $this->status,
      'msg' => $this->msg
    ];
    echo json_encode($response);
    http_response_code(401);
  }

  public function submitHouseDetails($data)
  {
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $author = $data->author;
      $title = $data->title ?? null;
      $price = $data->price ?? null;
      $address = $data->address ?? null;
      $detail = $data->detail ?? null;
      $addType = $data->addType ?? null;
      $prefix = "DRN";
      $timestamp = time();
      $randomNumber = mt_rand(1000, 9999);
      $uniqueIdentifier = uniqid();
      $serialNumber = $prefix . $timestamp . $randomNumber . $uniqueIdentifier;
      $serialNumber = preg_replace('/[.,\/\'"]/', '', $serialNumber);

      $insertQuery = $this->conn->prepare(
        "INSERT INTO house_list (sl, author	, title, price, address,details,type) 
       VALUES (?, ?, ?, ?, ?, ?,?)");
      $insertQuery->bind_param("sssssss", $serialNumber, $author, $title, $price, $address, $detail, $addType);
      if ($insertQuery->execute()) {
        $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
        $baseUrl = $sytemConfig['Base_Ur_backend'];
        $html = $baseUrl. '/pages/save-file.php?sl='. $serialNumber;
        $response = [
          'status' => true,
          'msg' => 'House details submitted successfully',
          'url' => $html
        ];

        echo json_encode($response);
        http_response_code(200);
      }
    } else {
      $this->unAutReq();
      exit();
    }
  }

  public function fetchHouseList(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $table = '<table class="table table-bordered w-f">';
      $table .= '<thead>';
      $table .= '<tr>';
      $table .= '<th scope="col">#</th>';
      $table .= '<th scope="col">ID</th>';
      $table .= '<th scope="col">IMG</th>';
      $table .= '<th scope="col">Title</th>';
      $table .= '<th scope="col">Address</th>';
      $table .= '<th scope="col">Details</th>';
      $table .= '<th scope="col">Price</th>';
      $table .= '<th scope="col">Currently rented by</th>';
      $table .= '<th scope="col">Type</th>';
      $table .= '</tr>';
      $table .= '</thead>';
      $table .= '<tbody>';
      $rowNumber = 1;

      $getHouseQuery = $this->conn->prepare("SELECT * FROM house_list where author	= ? ");
      $getHouseQuery->bind_param("s", $this->email);
      $getHouseQuery->execute();
      $getHouseResult = $getHouseQuery->get_result();
      while ($row = $getHouseResult->fetch_assoc()) {
        $table .= '<tr>';
        $table .= '<th scope="row">' . $rowNumber . '</th>';
        $table .= '<td>' . $row['sl'] . '</td>';
        $table .= '<td> <img src="' . $row['img_link'] . '" class="img-thumbnail" alt="' . $row['title'] . '" width="100" height="100" /></td>';
        $table .= '<td>' . $row['title'] . '</td>';
        $table .= '<td>' . $row['address'] . '</td>';
        $table .= '<td>' . $row['details'] . '</td>';
        $table .= '<td>' . $row['price'] . '</td>';
        $table .= '<td>' . (!empty($row['currently_owned_by']) ? $row['currently_owned_by'] : '') . '</td>';
        $table .= '<td>' . ucfirst($row['type']) . '</td>';
        $table .= '</tr>';

        $rowNumber++;
      }
      $table .= '</tbody>';
      $table .= '</table>';

      $getHouseResult->close();

      $response = [
        'status' => true,
        'html' => $table
      ];

      echo json_encode($response);
      http_response_code(200);
    }else{
      $this->unAutReq();
      exit();
    }
  }
}
