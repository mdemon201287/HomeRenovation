<?php
require_once(__DIR__ . '/dbConnect.php');

class AdminController
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

  public function fetchUserList(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $table = '<table class="table table-bordered">';
      $table .= '<thead>';
      $table .= '<tr>';
      $table .= '<th scope="col">#</th>';
      $table .= '<th scope="col">Name</th>';
      $table .= '<th scope="col">Email</th>';
      $table .= '<th scope="col">Path</th>';
      $table .= '<th scope="col">Status</th>';
      $table .= '<th scope="col">Action</th>';
      $table .= '</tr>';
      $table .= '</thead>';
      $table .= '<tbody>';
      $rowNumber = 1;

      $getUserQuery = $this->conn->prepare("SELECT *, path FROM user ");
      $getUserQuery->execute();
      $getUserResult = $getUserQuery->get_result();
      while ($row = $getUserResult->fetch_assoc()) {
        $table .= '<tr>';
        $table .= '<th scope="row">' . $rowNumber . '</th>';
        $table .= '<td>' . $row['name'] . '</td>';
        $table .= '<td>' . $row['email'] . '</td>';
        $table .= '<td>' . ucfirst($row['path']) . '</td>';
        $table .='<td>' . ($row['status'] == 'Inactive' ? '<span class="badge text-bg-danger">' : '<span class="badge text-bg-success">') . $row['status'] . '</span></td>';
        $table .= '<td>' . ($row['path'] == 'admin' ? '' : '<button class="btn btn-primary edit-button" data-sl="' . $row['sl'] . '">Change Status</button>') . '</td>';
        $table .= '</tr>';

        $rowNumber++;
      }
      $table .= '</tbody>';
      $table .= '</table>';

      $getUserResult->close();

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

  public function updateUserList($accessToken, $email, $sl)
  {
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $getUserQuery = $this->conn->prepare("SELECT * FROM user WHERE sl = ?");
      $getUserQuery->bind_param("s", $sl);
      $getUserQuery->execute();
      $getUserResult = $getUserQuery->get_result();
      if ($getUserResult->num_rows > 0) {
        while ($user = $getUserResult->fetch_assoc()) {
          $previousStatus = $user['status'];
        }
        $newStatus = $previousStatus == 'Inactive' ? 'Active' : 'Inactive';
        $updateUserQuery = $this->conn->prepare("UPDATE user SET status = ? WHERE sl = ?");
        $updateUserQuery->bind_param("ss", $newStatus, $sl);
        $updateUserQuery->execute();

        $getUserResult->close();

        $response = [
          'status' => true,
          'msg' => 'Status updated'
        ];
        echo json_encode($response);
        http_response_code(200);
      } else {
        $response = [
          'status' => true,
          'msg' => 'User not found'
        ];
        echo json_encode($response);
        http_response_code(200);
      }
    }else{
      $this->unAutReq();
      exit();
    }
  }

  public function fetchTrxTable(){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $table = '<table class="table table-bordered">';
      $table .= '<thead>';
      $table .= '<tr>';
      $table .= '<th scope="col">#</th>';
      $table .= '<th scope="col">Email</th>';
      $table .= '<th scope="col">Transaction ID</th>';
      $table .= '<th scope="col">Amount</th>';
      $table .= '<th scope="col">Reference</th>';
      $table .= '<th scope="col">Action</th>';
      $table .= '</tr>';
      $table .= '</thead>';
      $table .= '<tbody>';
      $rowNumber = 1;

      $getTrxQuery = $this->conn->prepare("SELECT * FROM amount_transaction where status = 'hold'");
      $getTrxQuery->execute();
      $getTrxQueryResult = $getTrxQuery->get_result();
      if ($getTrxQueryResult->num_rows > 0) {
        while ($row = $getTrxQueryResult->fetch_assoc()) {
          $table .= '<tr>';
          $table .= '<th scope="row">' . $rowNumber . '</th>';
          $table .= '<td>' . $row['author'] . '</td>';
          $table .= '<td>' . $row['trxID'] . '</td>';
          $table .= '<td>' . $row['amount'] . '</td>';
          $table .= '<td>' . $row['ref'] . '</td>';
          $table .= '<td>' . ($row['status'] == 'hold' ? '<button class="btn btn-success make-sattlement" data-sl="' . $row['author'] . '">Complete sattlement</button>' : '') . '</td>';
          $table .= '</tr>';

          $rowNumber++;
        }
        $table .= '</tbody>';
        $table .= '</table>';
      }else{
        $table .= '<tr>';
        $table .= '<th scope="row">' . $rowNumber . '</th>';
        $table .= '<td colspan="5">No transaction request available</td>';
        $table .= '</tr>';

        $rowNumber++;
        $table .= '</tbody>';
        $table .= '</table>';
      }

      $getTrxQueryResult->close();

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

  public function updateTrxSattalement($accessToken, $email, $sl){
    if ($this->userValidation($this->userAccessToken, $this->email)) {
      $getUserTrxQuery = $this->conn->prepare(
        "SELECT * FROM amount_transaction WHERE author = ? and status = 'hold' and ref = 'top-up'"
      );
      $getUserTrxQuery->bind_param("s", $sl);
      $getUserTrxQuery->execute();
      $getUserResult = $getUserTrxQuery->get_result();
      $finalBalance = 0;
      while ($row = $getUserResult->fetch_assoc()) {
        $finalBalance += $row['amount'];
      }
      $getUserTrxQuery->close();
      $checkAuthorQuery = $this->conn->prepare("SELECT author FROM balance WHERE author = ?");
      $checkAuthorQuery->bind_param("s", $sl);
      $checkAuthorQuery->execute();
      $checkAuthorResult = $checkAuthorQuery->get_result();
      $authorExists = $checkAuthorResult->num_rows > 0;
      $checkAuthorQuery->close();
      if ($authorExists) {
        $updateBalanceQuery = $this->conn->prepare("
                UPDATE balance SET balance = balance + ? WHERE author = ?
            ");
        $updateBalanceQuery->bind_param("ds", $finalBalance, $sl);
        $updateBalanceQuery->execute();
        $updateBalanceQuery->close();
      }else{
        $insertBalanceQuery = $this->conn->prepare("
                INSERT INTO balance (author, balance) VALUES (?, ?)
            ");
        $insertBalanceQuery->bind_param("sd", $sl, $finalBalance);
        $insertBalanceQuery->execute();
        $insertBalanceQuery->close();
      }

      $deleteTrxQuery = $this->conn->prepare("
            DELETE FROM amount_transaction WHERE author = ? and status = 'hold' and ref = 'top-up'
        ");

      $deleteTrxQuery->bind_param("s", $sl);
      $deleteTrxQuery->execute();
      $deleteTrxQuery->close();

      $response = [
        'status' => true,
        'msg' => 'Balance successfully added to sattlement'
      ];

      echo json_encode($response);
      http_response_code(200);
    } else {
      $this->unAutReq();
      exit();
    }
  }

}
