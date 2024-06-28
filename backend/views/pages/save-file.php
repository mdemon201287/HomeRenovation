<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_FILES["uploadFile"]) && $_FILES["uploadFile"]["error"] == 0) {
    $uploadDirectory = "../../public/img/";
    $originalFileName = $_FILES["uploadFile"]["name"];
    $uniqueFileName = uniqid() . '_' . $originalFileName;
    $targetFilePath = $uploadDirectory . $uniqueFileName;

    if (move_uploaded_file($_FILES["uploadFile"]["tmp_name"], $targetFilePath)) {
      require_once(__DIR__ . '/dbConnect.php');
      $sytemConfig = require(__DIR__ . '/config/systemConfig.php');
      $sl = $_GET['sl'] ?? null;
      $BaseUrlBackendPublic = $sytemConfig['Base_Ur_backend_public'];
      $img_link = $BaseUrlBackendPublic . '/img/' . $uniqueFileName;
      $updateQuery = $conn->prepare("UPDATE house_list SET img_link = ? WHERE sl = ?");
      $updateQuery->bind_param("ss", $img_link, $sl);
      $updateQuery->execute();
      $_SESSION['success_message'] = 'File uploaded and database updated successfully.Click close to close the window';
    } else {
      echo "Sorry, there was an error uploading your file.";
    }
  } else {
    echo "Error: " . $_FILES["uploadFile"]["error"];
  }
}
?>



<!DOCTYPE html>
<html lang="en">


<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title id="title"></title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="../assets/img/favicon.png" rel="icon">
  <link href="../assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="../assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="../assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="../assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Theme included stylesheets -->
  <link href="//cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <link href="//cdn.quilljs.com/1.3.6/quill.bubble.css" rel="stylesheet">

  <!-- Core build with no theme, formatting, non-essential modules -->
  <link href="//cdn.quilljs.com/1.3.6/quill.core.css" rel="stylesheet">
  <script src="//cdn.quilljs.com/1.3.6/quill.core.js"></script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Template Main CSS File -->
  <link href="../assets/css/style.css" rel="stylesheet">

</head>

<body>

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="../../../frontend/index.html" class="logo d-flex align-items-center">
        <img src="../assets/img/logo.png" alt="">
        <span class="d-none d-lg-block" id="naveName"></span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->





  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->


  <main id="main" class="main">

    <div class="pagetitle">
      <h1>House List</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="./dashboard.html">House Listing</a></li>
          <li class="breadcrumb-item">Pages</li>
          <li class="breadcrumb-item active">House Listing</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section">
      <div class="row d-flex justify-between-center">

        <div class="col-lg-6">
          <?php
          if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
          ' . $_SESSION['success_message'] . '
         <button type="button" class="btn btn-danger" data-bs-dismiss="alert" aria-label="Close" onclick="closeWindow()">Close</button>
        </div>';
            unset($_SESSION['success_message']);
          }
          ?>
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Form</h5>

              <!-- General Form Elements -->
              <form action="" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-2 col-form-label">Set image</label>
                  <div class="col-sm-10">
                    <input type="file" class="form-control" id="uploadFile" name="uploadFile">
                  </div>
                </div>
                <div class="row mb-3">
                  <label class="col-sm-2 col-form-label">Submit</label>
                  <div class="col-sm-10">
                    <button type="submit" id="submit_house_list_details" class="btn btn-primary">Form</button>
                  </div>
                </div>

              </form><!-- End General Form Elements -->

            </div>
          </div>

        </div>
      </div>
    </section>

  </main><!-- End #main -->

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">
    <div class="copyright">
      &copy; Copyright <strong><span id="footer_name"></span></strong>. All Rights Reserved
    </div>

  </footer><!-- End Footer -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="../assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/vendor/chart.js/chart.umd.js"></script>
  <script src="../assets/vendor/echarts/echarts.min.js"></script>
  <script src="../assets/vendor/quill/quill.min.js"></script>
  <script src="../assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="../assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="../assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="../assets/js/main.js"></script>

  <script>
    function closeWindow() {
      window.close();
    }
  </script>

</body>

</html>