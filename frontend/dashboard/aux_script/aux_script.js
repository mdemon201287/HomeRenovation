$(document).ready(function(){
  function primaryData(){
      $.ajax({
        type: "GET",
        url: "http://localhost/rent_house_uiu/backend/api/v1/page-info/abh1522agwe522255hba514125/primay_data",
        dataType: "json",
        success: function (response) {
          $("#title").html(response.adi_data.appName);
          $("#naveName").html(response.adi_data.appName);
          $("#footer_name").html(response.adi_data.appName);
        },
        error: function (error) {
          console.log("Error loading content:", error);
        }
      });
      const userData = JSON.parse(localStorage.getItem('homelyAuth'));
      if(userData !== null && userData.accessToken) {
        $("#check_login").html('Dashboard');
      }
    }
    primaryData();
  function UserData(){
    const userData = JSON.parse(localStorage.getItem('homelyAuth'));
    $("#user_name_profile").html(userData.email);
    $("#user_name").html(userData.email);
    $("#user_email").html(userData.email);
    $("#path").html(userData.path);
  }
  UserData();

  $("#log_out").on("click", function (e) {
    e.preventDefault();
    const userData = JSON.parse(localStorage.getItem('homelyAuth'));
    const email = userData.email;
    const accessToken = userData.accessToken;
    const url = `http://localhost/rent_house_uiu/backend/api/v1/log_out_user/abh1522agwe522255hba514125/${accessToken}/${email}`;

    $.ajax({
          type: "GET",
          url: url,
          dataType: "json",
          success: function (response) {
            if(response.status === true){
              localStorage.removeItem('homelyAuth');
              window.location.href = response.html;
            }
          },
          error: function (error) {
            console.log("Error loading content:", error);
          }
        });
  });
});