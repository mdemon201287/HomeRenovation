$(document).ready(function () {
  const loader = `<div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span> 
                  </div> <span class="pl-2">Loading...</span>`;

  $('#userList').append(loader);

  function fetchUserData() {
    const userData = JSON.parse(localStorage.getItem('homelyAuth'));
    const accessToken = userData.accessToken;
    const email = userData.email;
    const url = `http://localhost/rent_house_uiu/backend/api/v1/user_list_admin/abh1522agwe522255hba514125/${accessToken}/${email}`;
    
    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'json',
      success: function (response) {
        $('#userList').html('');
        if (response.status === true) {
          $('#userList').append(response.html);
        }
      },
      error: function (error) {
        console.log('Error loading content:', error);
      },
    });
  }

  setTimeout(fetchUserData, 2000);

  $(document).on('click', '.edit-button', function (e) {
    e.preventDefault();
    const userData = JSON.parse(localStorage.getItem('homelyAuth'));
    const accessToken = userData.accessToken;
    const email = userData.email;
    var slValue = $(this).data('sl');
    const url = `http://localhost/rent_house_uiu/backend/api/v1/change_user_status_admin/abh1522agwe522255hba514125/${accessToken}/${email}/${slValue}`;
    
    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'json',
      success: function (response) {
        $('#userList').html('');
        if (response.status === true) {
          alert(response.msg);
          fetchUserData();
        }
      },
      error: function (error) {
        console.log('Error loading content:', error);
      },
    });
  });
});
