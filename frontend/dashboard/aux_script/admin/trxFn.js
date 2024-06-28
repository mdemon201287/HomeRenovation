$(document).ready(function () {
  const loader = `<div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span> 
                  </div> <span class="pl-2">Loading...</span>`;

  $('#trxTable').append(loader);

  function fetchTrxData (){
    const userData = JSON.parse(localStorage.getItem('homelyAuth'));
    const accessToken = userData.accessToken;
    const email = userData.email;
    const url = `http://localhost/rent_house_uiu/backend/api/v1/trx_list_admin/abh1522agwe522255hba514125/${accessToken}/${email}`;
    
    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'json',
      success: function (response) {
        $('#trxTable').html('');
        if (response.status === true) {
          $('#trxTable').append(response.html);
        }
      },
      error: function (error) {
        console.log('Error loading content:', error);
      },
    });
  }

  setTimeout(fetchTrxData, 2500);

  $(document).on('click', '.make-sattlement', function (e) {
    e.preventDefault();
    const userData = JSON.parse(localStorage.getItem('homelyAuth'));
    const accessToken = userData.accessToken;
    const email = userData.email;
    var slValue = $(this).data('sl');
    const url = `http://localhost/rent_house_uiu/backend/api/v1/sattlement_user_admin/abh1522agwe522255hba514125/${accessToken}/${email}/${slValue}`;
    
    $.ajax({
      type: 'GET',
      url: url,
      dataType: 'json',
      success: function (response) {
        $('#trxTable').html('');
        if (response.status === true) {
          alert(response.msg);
          fetchTrxData();
        }
      },
      error: function (error) {
        console.log('Error loading content:', error);
      },
    });
  });
});
