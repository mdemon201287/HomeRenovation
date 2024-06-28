<?php

class HomeDecorController{
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

  public function fetchPendingReq()
  {
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $cardsHtml = '';

      $getHouseListQuery = $this->conn->prepare("
            SELECT house_list.*, moving_packing_renovation.*
            FROM house_list
            JOIN moving_packing_renovation ON house_list.sl = moving_packing_renovation.sl
            WHERE moving_packing_renovation.status = 'open'
        ");

      $getHouseListQuery->execute();
      $getHouseListQueryResult = $getHouseListQuery->get_result();

      if ($getHouseListQueryResult->num_rows > 0) {
        while ($row = $getHouseListQueryResult->fetch_assoc()) {
          $cardsHtml .= ' <div class="col">
                    <div class="card">
                        <div class="row g-0">
                            <div class="col-md-4">
                                <img src="' . $row['img_link'] . '" class="img-fluid rounded-start" alt="...">
                            </div>
                            <div class="col-md-8">
                                <div class="card-body">
                                    <h5 class="card-title">' . $row['title'] . '</h5>
                                    <p class="card-text">' . $row['address'] . '</p>
                                    <p class="card-text"><small class="text-body-secondary">Service Request :<span class="badge text-bg-primary" id="serviceType">' . ucfirst($row['services']) . '</span> </small></p>
                                    <button class="btn btn-success btn-md getJobDone" data-sl="' . $row['sl'] . '">Take this job</button>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>';
        }
      } else {
        $cardsHtml = '<div class="col">
                <div class="card">
                    <div class="card-body">
                        <p class="card-text">No requests found.</p>
                    </div>
                </div>
            </div>';
      }

      $response = [
        'status' => '0000',
        'msg' => $cardsHtml,
      ];

      echo json_encode($response);
      http_response_code(200);
    } else {
      $this->unAutReq();
      exit();
    }
  }

  public function takeThisJob($accessToken, $email, $sl,$service){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $checkServiceQuery = $this->conn->prepare("SELECT * FROM moving_packing_renovation WHERE sl = ? AND services =? AND status ='open'");
      $checkServiceQuery->bind_param("ss", $sl, $service);
      $checkServiceQuery->execute();
      $checkSaleResult = $checkServiceQuery->get_result();
      $serviceExists = $checkSaleResult->num_rows > 0;
      $checkServiceQuery->close();
      if ($serviceExists) {
        $updateStatusQuery = $this->conn->prepare("
                UPDATE moving_packing_renovation SET done_by = ?, status = 'in-process' WHERE sl = ? AND services = ?
            ");
        $updateStatusQuery->bind_param("sss", $email, $sl, $service);
        $updateStatusQuery->execute();
        $updateStatusQuery->close();
        $msg = 'Successfully took the job';
      } else {
        $msg = 'Job is unavailable';
      }
      $response = [
        'status' => '0000',
        'msg' => $msg,
      ];

      echo json_encode($response);
      http_response_code(200);
    } else {
      $this->unAutReq();
      exit();
    }
  }

  public function jobList(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $table = '<table class="table table-bordered w-f">';
      $table .= '<thead>';
      $table .= '<tr>';
      $table .= '<th scope="col">#</th>';
      $table .= '<th scope="col"></th>';
      $table .= '<th scope="col">Adress</th>';
      $table .= '<th scope="col">Service requested</th>';
      $table .= '<th scope="col">Cost</th>';
      $table .= '<th scope="col">status</th>';
      $table .= '<th scope="col">Action</th>';
      $table .= '</tr>';
      $table .= '</thead>';
      $table .= '<tbody>';
      $rowNumber = 1;
      $getHouseListQuery = $this->conn->prepare("
         SELECT house_list.*, moving_packing_renovation.*
         FROM house_list
         JOIN moving_packing_renovation ON house_list.sl = moving_packing_renovation.sl
         WHERE moving_packing_renovation.done_by = ?");

      $getHouseListQuery->bind_param("s", $this->email);
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
          $table .= '<td>' . ucfirst($row['status']) . '</td>';
          $table .= '<td><button class="btn btn-warning btn-sm makeComplete" data-sl="' . $row['sl'] . '" data-service="' . $row['services'] . '">Make complete</button></td>';
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

  public function submitJobCost($data){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $sl = $data->slValue;
      $service = $data->jobServiceType;
      $cost = $data->jobServiceCost ?? 0;
      $status = 'completed';

      $checkServiceQuery = $this->conn->prepare("SELECT * FROM moving_packing_renovation WHERE sl = ? AND services = ? AND status ='in-process'");
      $checkServiceQuery->bind_param("ss", $sl, $service);
      $checkServiceQuery->execute();
      $checkSaleResult = $checkServiceQuery->get_result();
      $checkServiceQuery->close();

      if ($checkSaleResult->num_rows > 0) {
        $updateStatusQuery = $this->conn->prepare("
                UPDATE moving_packing_renovation SET cost = ?, status = ? WHERE sl = ? AND done_by = ? AND services = ?
            ");
        $updateStatusQuery->bind_param("sssss", $cost, $status, $sl, $this->email, $service);

        if ($updateStatusQuery->execute()) {
          $msg = 'Successfully updated the job status and cost';
        } else {
          $msg = 'Failed to update the job status and cost';
        }

        $updateStatusQuery->close();
      } else {
        $msg = 'Job is unavailable';
      }

      $response = [
        'status' => '0000',
        'msg' => $msg,
      ];

      echo json_encode($response);
      http_response_code(200);
    } else {
      $this->unAutReq();
      exit();
    }
  }
}