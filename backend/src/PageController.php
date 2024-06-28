<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
require_once 'dbConnect.php';

class PageController{

  private $status;
  private $msg;
  private $accessToken;
  private $conn;

  public function __construct($token){
    global $conn;
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

  /* =============== Token Validateion end =============== */

  public function pageInfo($query){
    if($query === null){
      $this->status= "4000";
      $this->msg= "query required";
      $response = [
        'status' => $this->status,
        'msg' => $this->msg
      ];
      echo json_encode($response);
      http_response_code(200);
    }else{
      $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
      $baseUrl = $sytemConfig['Base_Url'];
      if($query === 'nav'){
        $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
        $appName = $sytemConfig['APP_Name'];
        $html = $baseUrl.'/frontend/component/navbar.html';
        $response = [
          'status' => '0000',
          'msg' => file_get_contents($html),
          'adi_data' => [
            'appName' => $appName
          ]
        ];
        echo json_encode($response);
        http_response_code(200);
      }elseif($query === 'welcome'){
        $html = $baseUrl . '/frontend/page/welcome.html';
        $response = [
          'status' => '0000',
          'msg' => file_get_contents($html)
        ];
        echo json_encode($response);
        http_response_code(200);
      } elseif ($query === 'property_list') {
        $html = $baseUrl . '/frontend/page/property-list.html';
        $response = [
          'status' => '0000',
          'msg' => file_get_contents($html)
        ];
        echo json_encode($response);
        http_response_code(200);
      }elseif($query === 'check_login'){
        if ($query === 'check_login') {
          if (isset($_SESSION['access_token'])) {
            $access = true;
            $html = $baseUrl.'/frontend/dashboard/dashboard.html';
            $response = [
              'status' => '0000',
              'msg' => [
                'access' => $access,
                'url' => $html
              ]
            ];
            echo json_encode($response);
            http_response_code(200);
          } else {
            $access = false;
            $html = $baseUrl . '/frontend/page/login.html';
            $response = [
              'status' => '0000',
              'msg' => [
                'access' => $access,
                'url' => file_get_contents($html)
              ]
            ];
            echo json_encode($response);
          }
        }
      }elseif($query === 'sign_up'){
        $html = $baseUrl . '/frontend/page/sign-up.html';
        $response = [
          'status' => '0000',
          'msg' => file_get_contents($html)
        ];
        echo json_encode($response);
        http_response_code(200);
      } elseif ($query === 'submit_signup') {
        $html = $baseUrl . '/frontend/page/sign-up.html';
        $response = [
          'status' => '0000',
          'msg' => file_get_contents($html)
        ];
        echo json_encode($response);
        http_response_code(200);
      } elseif ($query === 'primay_data') {
        $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
        $appName = $sytemConfig['APP_Name'];
        $response = [
          'status' => '0000',
          'adi_data' => [
            'appName' => $appName
          ]
        ];
        echo json_encode($response);
        http_response_code(200);
      }elseif($query === 'product_details'){
        $html = $baseUrl . '/frontend/property-single.html';
        $response = [
          'status' => '0000',
          'msg' => $html
        ];
        echo json_encode($response);
        http_response_code(200);
      }else{
        $response = [
          'status' => '0000',
          'msg' => 'your query is : ' . $query
        ];
        echo json_encode($response);
        http_response_code(200);
      }
    }
  }

  public function fetchHouse($query){
    if($query === 'all'){
      $housesHtml = '';
      $getHouseQuery = $this->conn->prepare("SELECT * FROM house_list where status = 'live'");
      $getHouseQuery->execute();
      $getHouseResult = $getHouseQuery->get_result();
      while ($row = $getHouseResult->fetch_assoc()) {
        $housesHtml .= '
        <a href="" class="view" data-sl="' . $row['sl'] . '">
          <div class="card-box-a card-shadow">
            <div class="img-box-a">
              <img src="'.$row['img_link'].'" alt="" class="img-a img-fluid">
            </div>
            <div class="card-overlay">
              <div class="card-overlay-a-content">
                <div class="card-header-a">
                  <h2 class="card-title-a">
                    <a href="#">'.$row['title'].'
                      <br />'.$row['address'].'</a>
                  </h2>
                </div>
                <div class="card-body-a">
                  <div class="price-box d-flex">
                  <a href="" data-sl="' . $row['sl'] . '"
                   class="price-a" >'.$row['type']. ' | &#2547; '.$row['price']. '
                  </div>
                  <span  class="pt-4 link-a">Click here to view
                    <span class="bi bi-chevron-right"></span>
                  </span>
                  </a>
                </div>
                <div class="card-footer-a">
                  <ul class="card-info d-flex justify-content-around">
                    <l>
                      <h4 class="card-info-title">Details</h4>
                      <span class="text-white">'.$row['details']. '
                      </span>
                    </l
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </a>
        ';
      }
    }elseif($query === 'rent'){
      $housesHtml = '';
      $getHouseQuery = $this->conn->prepare(
      "SELECT * FROM house_list where type = ? and status = 'live'");
      $getHouseQuery->bind_param("s", $query);
      $getHouseQuery->execute();
      $getHouseResult = $getHouseQuery->get_result();
      while ($row = $getHouseResult->fetch_assoc()) {
        $housesHtml .= '
          <div class="card-box-a card-shadow">
            <div class="img-box-a">
              <img src="' . $row['img_link'] . '" alt="" class="img-a img-fluid">
            </div>
            <div class="card-overlay">
              <div class="card-overlay-a-content">
                <div class="card-header-a">
                  <h2 class="card-title-a">
                    <a href="#">' . $row['title'] . '
                      <br />' . $row['address'] . '</a>
                  </h2>
                </div>
                <div class="card-body-a">
                  <div class="price-box d-flex">
                    <a href="" data-sl="' . $row['sl'] . '"
                   class="price-a" >' . $row['type'] . ' | &#2547; ' . $row['price'] . '
                  </div>
                  <span  class="pt-4 link-a">Click here to view
                    <span class="bi bi-chevron-right"></span>
                  </span>
                  </a>
                </div>
                <div class="card-footer-a">
                  <ul class="card-info d-flex justify-content-around">
                    <l>
                      <h4 class="card-info-title">Details</h4>
                      <span class="text-white">' . $row['details'] . '
                      </span>
                    </l
                  </ul>
                </div>
              </div>
            </div>
          </div>
        
        ';
      }
    } elseif ($query === 'sale') {
      $housesHtml = '';
      $getHouseQuery = $this->conn->prepare(
      "SELECT * FROM house_list where type = ?  and status = 'live'");
      $getHouseQuery->bind_param("s", $query);
      $getHouseQuery->execute();
      $getHouseResult = $getHouseQuery->get_result();
      while ($row = $getHouseResult->fetch_assoc()) {
        $housesHtml .= '
          <div class="card-box-a card-shadow">
            <div class="img-box-a">
              <img src="' . $row['img_link'] . '" alt="" class="img-a img-fluid">
            </div>
            <div class="card-overlay">
              <div class="card-overlay-a-content">
                <div class="card-header-a">
                  <h2 class="card-title-a">
                    <a href="#">' . $row['title'] . '
                      <br />' . $row['address'] . '</a>
                  </h2>
                </div>
                <div class="card-body-a">
                  <div class="price-box d-flex">
                    <a href="" data-sl="' . $row['sl'] . '"
                   class="price-a" >' . $row['type'] . ' | &#2547; ' . $row['price'] . '
                  </div>
                  <span  class="pt-4 link-a">Click here to view
                    <span class="bi bi-chevron-right"></span>
                  </span>
                  </a>
                </div>
                <div class="card-footer-a">
                  <ul class="card-info d-flex justify-content-around">
                    <l>
                      <h4 class="card-info-title">Details</h4>
                      <span class="text-white">' . $row['details'] . '
                      </span>
                    </l
                  </ul>
                </div>
              </div>
            </div>
          </div>
        
        ';
      }
    }
    $response = [
      'status' => '0000',
      'msg' => $housesHtml 
    ];
    echo json_encode($response);
    http_response_code(200);
  }

  public function fetchHousePriceSort($min, $max){
    $housesHtml = '';
    $min = filter_var($min, FILTER_VALIDATE_FLOAT);
    $max = filter_var($max, FILTER_VALIDATE_FLOAT);

    if ($min === false || $max === false || $min > $max) {
      $response = [
        'status' => '4004',
        'msg' => 'Invalid input parameters.',
      ];
      echo json_encode($response);
      http_response_code(400);
    }else{
      $getHouseQuery = $this->conn->prepare(
        "SELECT * FROM house_list WHERE status = 'live' AND price BETWEEN ? AND ? ORDER BY price ASC"
      );
      $getHouseQuery->bind_param("dd", $min, $max);
      $getHouseQuery->execute();
      $getHouseResult = $getHouseQuery->get_result();
      while ($row = $getHouseResult->fetch_assoc()) {
        $housesHtml .= '
        <div class="card-box-a card-shadow">
          <div class="img-box-a">
            <img src="' . $row['img_link'] . '" alt="" class="img-a img-fluid">
          </div>
          <div class="card-overlay">
            <div class="card-overlay-a-content">
              <div class="card-header-a">
                <h2 class="card-title-a">
                  <a href="#">' . $row['title'] . '
                    <br />' . $row['address'] . '</a>
                </h2>
              </div>
              <div class="card-body-a">
                <div class="price-box d-flex">
                  <a href="" data-sl="' . $row['sl'] . '"
                 class="price-a" >' . $row['type'] . ' | &#2547; ' . $row['price'] . '
                </div>
                <span  class="pt-4 link-a">Click here to view
                  <span class="bi bi-chevron-right"></span>
                </span>
                </a>
              </div>
              <div class="card-footer-a">
                <ul class="card-info d-flex justify-content-around">
                  <l>
                    <h4 class="card-info-title">Details</h4>
                    <span class="text-white">' . $row['details'] . '
                    </span>
                  </l
                </ul>
              </div>
            </div>
          </div>
        </div>
      
      ';
      }

      $response = [
        'status' => '0000',
        'msg' => $housesHtml
      ];
      echo json_encode($response);
      http_response_code(200);
    }
    
  }

  public function fetchHouseDetails($query){
    $reviewHtml= '';
    $getHouseQuery = $this->conn->prepare("SELECT * FROM house_list where sl = ?");
    $getHouseQuery->bind_param("s", $query);
    $getHouseQuery->execute();
    $getHouseResult = $getHouseQuery->get_result();
    while ($row = $getHouseResult->fetch_assoc()) {
      $imageHtml = $row['img_link'] ;
      $price = $row['price'] ;
      $location = $row['address'] ;
      $details = $row['details'] ;
      $type = $row['type'] ;
    }
    $getHouseResult->close();
    $getHouseReviewQuery = $this->conn->prepare("SELECT * FROM rent_review where sl = ?");
    $getHouseReviewQuery->bind_param("s", $query);
    $getHouseReviewQuery->execute();
    $getHouseReviewResult = $getHouseReviewQuery->get_result();
    if ($getHouseReviewResult->num_rows > 0) {
      while ($review = $getHouseReviewResult->fetch_assoc()) {
        $reviewHtml .= '<div class="card">
                  <div class="card-body">
                    <h2 class="card-title">' . $review['client'] . '</h2>
                    <p>' . $review['comment'] . '</p>
                  </div>
                 </div>';
      }
      $getHouseReviewResult->close();
    }else{
      $reviewHtml = '<div class="card">
        <div class="card-body">
            <p>No reviews available.</p>
        </div>
    </div>';
    }
    $response = [
      'status' => '0000',
      'data' => [
        'main_img' => $imageHtml,
        'price' => $price,
        'location' => $location,
        'details' => $details,
        'reviewHtml' => $reviewHtml,
        'sl' => $query,
        'type' => $type
      ]
    ];
    echo json_encode($response);
    http_response_code(200);
  }

  public function saveDataRent($data){
    $author = $data->author;
    $price = $data->price ?? 0;
    $slValue = $data->slValue?? null;
    if($price == 0 || $slValue === null){
      $response = [
        'status' => '0000',
        'exception' => 'yes',
        'msg' => 'Invalid input. Check submitted data'
      ];
      echo json_encode($response);
      http_response_code(200);
    }else{
      $getBalanceQuery = $this->conn->prepare("SELECT * FROM balance where author = ?");
      $getBalanceQuery->bind_param("s", $author);
      $getBalanceQuery->execute();
      $getBalanceResult = $getBalanceQuery->get_result();
      if ($getBalanceResult->num_rows > 0) {
        $row = $getBalanceResult->fetch_assoc();
        $balance = $row['balance'];
      } else {
        $balance = 0;
      }
      if($balance >= $price){
        $updateHouseQuery = $this->conn->prepare("UPDATE house_list SET currently_owned_by = ?, status = 'booked' WHERE sl = ?");
        $updateHouseQuery->bind_param("ss", $author, $slValue);
        $updateHouseQuery->execute();

        $newBalance = $balance - $price;
        $updateBalanceQuery = $this->conn->prepare("UPDATE balance SET balance = ? WHERE author = ?");
        $updateBalanceQuery->bind_param("ss", $newBalance, $author);
        $updateBalanceQuery->execute();

        $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
        $baseUrl = $sytemConfig['Base_Url'];
        $userDashboard = $sytemConfig['User_Dash_url'];
        $html = $baseUrl . $userDashboard;
        $response = [
          'status' => '0000',
          'exception' => 'yes',
          'msg' => 'Rental request completed',
          'url' => $html
        ];
        echo json_encode($response);
        http_response_code(200);
      }else{
        $response = [
          'status' => '0000',
          'exception' => 'yes',
          'msg' => 'Insufficient balance rechange and try again'
        ];
        echo json_encode($response);
        http_response_code(200);
      }
    }

  }

  public function saveDataSale($data)
  {
    $author = $data->author;
    $price = $data->price ?? 0;
    $slValue = $data->slValue ?? null;
    if ($price == 0 || $slValue === null) {
      $response = [
        'status' => '0000',
        'exception' => 'yes',
        'msg' => 'Invalid input. Check submitted data'
      ];
      echo json_encode($response);
      http_response_code(200);
    } else {
      $getBalanceQuery = $this->conn->prepare("SELECT * FROM balance where author = ?");
      $getBalanceQuery->bind_param("s", $author);
      $getBalanceQuery->execute();
      $getBalanceResult = $getBalanceQuery->get_result();
      if ($getBalanceResult->num_rows > 0) {
        $row = $getBalanceResult->fetch_assoc();
        $balance = $row['balance'];
      } else {
        $balance = 0;
      }
      if ($balance >= $price) {
        $updateHouseQuery = $this->conn->prepare("UPDATE house_list SET author = ?, status = 'booked' WHERE sl = ?");
        $updateHouseQuery->bind_param("ss", $author, $slValue);
        $updateHouseQuery->execute();

        $newBalance = $balance - $price;
        $updateBalanceQuery = $this->conn->prepare("UPDATE balance SET balance = ? WHERE author = ?");
        $updateBalanceQuery->bind_param("ss", $newBalance, $author);
        $updateBalanceQuery->execute();

        $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
        $baseUrl = $sytemConfig['Base_Url'];
        $userDashboard = $sytemConfig['User_Dash_url'];
        $html = $baseUrl . $userDashboard;
        $response = [
          'status' => '0000',
          'exception' => 'yes',
          'msg' => 'Sale request completed',
          'url' => $html
        ];
        echo json_encode($response);
        http_response_code(200);
      } else {
        $response = [
          'status' => '0000',
          'exception' => 'yes',
          'msg' => 'Insufficient balance rechange and try again'
        ];
        echo json_encode($response);
        http_response_code(200);
      }
    }
  }
}