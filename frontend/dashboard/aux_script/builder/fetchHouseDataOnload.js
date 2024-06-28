$(document).ready(function () {
    const loader = `<div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span> 
                  </div> <span class="pl-2">Loading...</span>`;
    function listHouse(){
      const userData = JSON.parse(localStorage.getItem('homelyAuth'));
      const accessToken = userData.accessToken;
      const email = userData.email;
      const url = `http://localhost/rent_house_uiu/backend/api/v1/house_list_user/abh1522agwe522255hba514125/${accessToken}/${email}`;
      $(".House_List").html(loader);
      $.ajax({
          type: 'GET',
          url: url,
          dataType: 'json',
          success: function (response) {
            $('.House_List').html('');
            if (response.status === true) {
              $('.House_List').append(response.html);
            }
          },
          error: function (error) {
            console.log('Error loading content:', error);
          },
        });
    }
    listHouse();

});