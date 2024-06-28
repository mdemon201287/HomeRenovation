<?php

class UserController{
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

  public function fetchBalance(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $getBalanceQuery = $this->conn->prepare("SELECT * FROM balance where author = ?");
      $getBalanceQuery->bind_param("s", $this->email);
      $getBalanceQuery->execute();
      $getBalanceResult = $getBalanceQuery->get_result();
      if ($getBalanceResult->num_rows > 0) {
        $row = $getBalanceResult->fetch_assoc();
        $balance = $row['balance'];
      }else{
        $balance = 0;
      }
      $response = [
        'status' => true,
        'data' => $balance
      ];

      echo json_encode($response);
      http_response_code(200);
    }else{
      $this->unAutReq();
      exit();
    }
  }

  public function submitBlanceUser($data){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $author = $data->author;
      $balance = $data->balance ?? 0;
      $trxID = $data->trxID ?? null;
      $ref = 'top-up';
      $insertQuery = $this->conn->prepare(
        "INSERT INTO amount_transaction (author, trxID, amount, ref) 
       VALUES (?, ?, ?, ?)"
      );
      $insertQuery->bind_param("ssss", $author, $trxID, $balance , $ref);
      if ($insertQuery->execute()) {
        $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
        $baseUrl = $sytemConfig['Base_Url'];
        $userDashboard = $sytemConfig['User_Dash_url'];
        $html = $baseUrl . $userDashboard;
        $response = [
          'status' => true,
          'msg' => 'Balance Added successfully',
          'url' => $html
        ];

        echo json_encode($response);
        http_response_code(200);
      }

    }else{
      $this->unAutReq();
      exit();
    }
  }

  public function fetchListHouse(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $table = '<table class="table table-bordered w-f">';
      $table .= '<thead>';
      $table .= '<tr>';
      $table .= '<th scope="col">#</th>';
      $table .= '<th scope="col"></th>';
      $table .= '<th scope="col">Title</th>';
      $table .= '<th scope="col">Adress</th>';
      $table .= '<th scope="col">Details</th>';
      $table .= '<th scope="col">Price</th>';
      $table .= '<th scope="col">Type</th>';
      $table .= '<th scope="col">Action</th>';
      $table .= '</tr>';
      $table .= '</thead>';
      $table .= '<tbody>';
      $rowNumber = 1;
      $getHouseListQuery = $this->conn->prepare("SELECT * FROM house_list WHERE author = ? OR currently_owned_by = ?");
      $getHouseListQuery->bind_param("ss", $this->email, $this->email);
      $getHouseListQuery->execute();
      $getHouseListQueryResult = $getHouseListQuery->get_result();

      if ($getHouseListQueryResult->num_rows > 0) {
        while ($row = $getHouseListQueryResult->fetch_assoc()) {
          $table .= '<tr>';
          $table .= '<th scope="row">' . $rowNumber . '</th>';
          $table .= '<td> <img src="' . $row['img_link'] . '" class="img-thumbnail" alt="' . $row['title'] . '" width="100" height="100" /></td>';
          $table .= '<td>' . $row['title'] . '</td>';
          $table .= '<td>' . $row['address'] . '</td>';
          $table .= '<td>' . $row['details'] . '</td>';
          $table .= '<td>' . $row['price'] . '</td>';
          $table .= '<td>' . ucfirst($row['type']) . '</td>';
          $table .= '<td>' . ($row['type'] == 'rent' && $row['status'] != 'booked' ? '<button class="btn btn-primary sale-button" data-sl="' . $row['sl'] . '">Submit for sale</button>' : '') . '</td>';
          $table .= '</tr>';

          $rowNumber++;
        }
        $table .= '</tbody>';
        $table .= '</table>';
      }else {
        $table .= '<tr>';
        $table .= '<th scope="row">' . $rowNumber . '</th>';
        $table .= '<td colspan ="5">No data found</td>';
        $table .= '</tr>';

        $rowNumber++;
        $table .= '</tbody>';
        $table .= '</table>';
      }
      $getHouseListQueryResult->close();
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

  public function fetchListRequest()
  {
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $table = '<table class="table table-bordered w-f">';
      $table .= '<thead>';
      $table .= '<tr>';
      $table .= '<th scope="col">#</th>';
      $table .= '<th scope="col"></th>';
      $table .= '<th scope="col">Adress</th>';
      $table .= '<th scope="col">Service requested</th>';
      $table .= '<th scope="col">Cost</th>';
      $table .= '<th scope="col">Done by</th>';
      $table .= '<th scope="col">status</th>';
      $table .= '</tr>';
      $table .= '</thead>';
      $table .= '<tbody>';
      $rowNumber = 1;
      $getHouseListQuery = $this->conn->prepare("
         SELECT house_list.*, moving_packing_renovation.*
         FROM house_list
         JOIN moving_packing_renovation ON house_list.sl = moving_packing_renovation.sl
         WHERE house_list.author = ? OR house_list.currently_owned_by = ?
       ");

      $getHouseListQuery->bind_param("ss", $this->email, $this->email);
      $getHouseListQuery->execute();
      $getHouseListQueryResult = $getHouseListQuery->get_result();

      if ($getHouseListQueryResult->num_rows > 0) {
        while ($row = $getHouseListQueryResult->fetch_assoc()) {
          $table .= '<tr>';
          $table .= '<th scope="row">' . $rowNumber . '</th>';
          $table .= '<td> <img src="' . $row['img_link'] . '" class="img-thumbnail" alt="' . $row['title'] . '" width="100" height="100" /></td>';
          
          $table .= '<td>' . $row['address'] . '</td>';
          $table .= '<td>' . ucfirst($row['services']) . '</td>';
          $table .= '<td>' . $row['cost'] . '</td>';
          $table .= '<td>' . $row['done_by'] . '</td>';
          $table .= '<td>' . ucfirst($row['status']) . '</td>';
          $table .= '</tr>';

          $rowNumber++;
        }
        $table .= '</tbody>';
        $table .= '</table>';
      } else {
        $table .= '<tr>';
        $table .= '<th scope="row">' . $rowNumber . '</th>';
        $table .= '<td colspan ="5">No data found</td>';
        $table .= '</tr>';

        $rowNumber++;
        $table .= '</tbody>';
        $table .= '</table>';
      }
      $getHouseListQueryResult->close();
      $response = [
        'status' => true,
        'html' => $table
      ];

      echo json_encode($response);
      http_response_code(200);
    } else {
      $this->unAutReq();
      exit();
    }
  }

  public function setHouseForSale($accessToken, $email, $sl){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $checkSaleQuery = $this->conn->prepare("SELECT * FROM house_list WHERE sl = ? AND status='booked'");
      $checkSaleQuery->bind_param("s", $sl);
      $checkSaleQuery->execute();
      $checkSaleResult = $checkSaleQuery->get_result();
      $saleExists = $checkSaleResult->num_rows > 0;
      $checkSaleQuery->close();
      if ($saleExists) {
        $updateStatusQuery = $this->conn->prepare("
                UPDATE house_list SET status = 'live'  WHERE sl = ?
            ");
        $updateStatusQuery->bind_param("s", $sl);
        $updateStatusQuery->execute();
        $updateStatusQuery->close();
        $msg = 'Property submitted to sale';
      }else{
        $msg = 'Property not existed';
      }
      $response = [
        'status' => true,
        'msg' => $msg
      ];

      echo json_encode($response);
      http_response_code(200);
    } else {
      $this->unAutReq();
      exit();
    }
  }
  
  public function fetchHouseSelect(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $getHouseListQuery = $this->conn->prepare("SELECT * FROM house_list WHERE author = ? OR currently_owned_by = ?");
      $getHouseListQuery->bind_param("ss", $this->email, $this->email);
      $getHouseListQuery->execute();
      $getHouseListQueryResult = $getHouseListQuery->get_result();
      $options = '<option selected="" value="">Choose your address / House</option>';
      while ($row = $getHouseListQueryResult->fetch_assoc()) {
        $options .= '<option value="' . $row['sl'] . '">' . $row['address'] . '</option>';
      }
      $getHouseListQueryResult->close();
      $response = [
        'status' => true,
        'html' => $options
      ];

      echo json_encode($response);
      http_response_code(200);
    }else{
      $this->unAutReq();
      exit();
    }
  }

  public function submitMovingRequest($data){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $selectedHouse = $data->selectedHouse;
      $selectedServiceType = $data->selectedServiceType;
      $insertQuery = $this->conn->prepare(
        "INSERT INTO moving_packing_renovation (sl, services) 
       VALUES (?, ?)"
      );
      $insertQuery->bind_param("ss", $selectedHouse, $selectedServiceType);
      if ($insertQuery->execute()) {
        $response = [
          'status' => true,
          'msg' => "Successfully submitted request"
        ];

        echo json_encode($response);
        http_response_code(200);
      }
    }else{
      $this->unAutReq();
      exit();
    }
  }

  public function submitRenovationRequest($data){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $selectedHouse = $data->selectedHouse;
      $selectedServiceType = "renovation";
      $insertQuery = $this->conn->prepare(
        "INSERT INTO moving_packing_renovation (sl, services) 
       VALUES (?, ?)"
      );
      $insertQuery->bind_param("ss", $selectedHouse, $selectedServiceType);
      if ($insertQuery->execute()) {
        $response = [
          'status' => true,
          'msg' => "Successfully submitted request"
        ];

        echo json_encode($response);
        http_response_code(200);
      }
    } else {
      $this->unAutReq();
      exit();
    }
  }
}