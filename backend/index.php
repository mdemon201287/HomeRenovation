<?php
require_once(__DIR__ . '/src/dbConnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
  header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
  header("Access-Control-Allow-Credentials: true");
  http_response_code(200);
  exit;
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');
header("Access-Control-Allow-Credentials: true");

$part = explode("/", $_SERVER['REQUEST_URI']);
$route_path = $part[5] ?? null;
$token = null;
if (isset($part[6])) {
  $token = $part[6];
} elseif (isset($part[5])) {
  $token = $part[5];
}

spl_autoload_register(function ($class) {
  require __DIR__ . "/src/$class.php";
});

$token = $part[6] ?? null;
$userAccessToken = $part[7] ?? null;
$email = $part[8] ?? null;

$pageController = new PageController($token);
$regUserController = new RegUserController($token, $conn);
$adminController = new AdminController($token, $userAccessToken, $email, $conn);
$builderController = new BuilderController($token, $userAccessToken, $email, $conn);
$userController = new UserController($token, $userAccessToken, $email, $conn);
$homeDecorController = new HomeDecorController($token, $userAccessToken, $email, $conn);
$defaultPageController = new DefaultPageRouting();
$errorController = new ErrorController();

if ($route_path === '' || $route_path === null || is_numeric($route_path)) {
  $defaultPageController->index();
} elseif ($route_path === 'page-info') {
  $query = $part[7] ?? null;
  $pageController->pageInfo($query);
}elseif($route_path === 'fetch_hous_list'){
  $query = $part[7]?? 'all';
  $pageController->fetchHouse($query);
} elseif ($route_path === 'fetch_hous_list_price_sort') {
  $min = $part[7]??100;
  $max = $part[8]??100;
  $pageController->fetchHousePriceSort($min,$max);
} elseif ($route_path === 'fetch_hous_details') {
  $query = $part[7] ?? 'all';
  $pageController->fetchHouseDetails($query);
} elseif ($route_path === 'get_rent') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $pageController->saveDataRent($data);
  }
} elseif ($route_path === 'get_sale') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $pageController->saveDataSale($data);
  }
} elseif ($route_path === 'submit_signup') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $regUserController->saveUser($data);
  }
} elseif ($route_path === 'submit_login') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $regUserController->logIn($data);
  }
} elseif ($route_path === 'validate_user') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $regUserController->validateUser($accessToken, $email);
} elseif ($route_path === 'log_out_user') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $regUserController->logOutUser($accessToken, $email);
} elseif ($route_path === 'user_list_admin') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $adminController->fetchUserList($accessToken, $email);
} elseif ($route_path === 'trx_list_admin') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $adminController->fetchTrxTable($accessToken, $email);
}elseif($route_path === 'change_user_status_admin'){
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $sl = $part[9] ?? null;
  $adminController->updateUserList($accessToken, $email,$sl);
} elseif ($route_path === 'sattlement_user_admin') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $sl = $part[9] ?? null;
  $adminController->updateTrxSattalement($accessToken, $email, $sl);
}elseif($route_path === 'submit_house_builder'){
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  $builderController->submitHouseDetails($data);
}elseif($route_path === 'house_list_user'){
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $builderController->fetchHouseList($accessToken, $email);
}elseif($route_path === 'user_balance'){
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $userController->fetchBalance($accessToken, $email);
} elseif ($route_path === 'user_house_list_dashboard') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $userController->fetchListHouse($accessToken, $email);
} elseif ($route_path === 'user_request_list_dashboard') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $userController->fetchListRequest($accessToken, $email);
} elseif ($route_path === 'user_house_select_dashboard') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $userController->fetchHouseSelect($accessToken, $email);
} elseif ($route_path === 'submit_moving_request') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $userController->submitMovingRequest($data);
  }
} elseif ($route_path === 'submit_renovation_request') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $userController->submitRenovationRequest($data);
  }
} elseif ($route_path === 'user_house_sale_dashboard') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $sl = $part[9] ?? null;
  $userController->setHouseForSale($accessToken, $email, $sl);
}elseif($route_path === 'submit_balance_user'){
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  $userController->submitBlanceUser($data);
}elseif($route_path === 'home_decor_fetch_pending_req'){
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $homeDecorController->fetchPendingReq($accessToken, $email);
} elseif ($route_path === 'home_decor_job_list') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $homeDecorController->jobList($accessToken, $email);
} elseif ($route_path === 'home_decor_take_this_job') {
  $accessToken = $part[7] ?? null;
  $email = $part[8] ?? null;
  $sl = $part[9] ?? null;
  $status = $part[10] ?? null;
  $homeDecorController->takeThisJob($accessToken, $email, $sl, $status);
} elseif ($route_path === 'home_decor_job_cost_submit') {
  $json = file_get_contents('php://input');
  $data = json_decode($json);
  if ($data === null) {
    $errorController->notFound();
  } else {
    $homeDecorController->submitJobCost($data);
  }
} else {
  $errorController->notFound();
}
