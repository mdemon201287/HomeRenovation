$(document).ready(function () {
    $(document).on('click', '#submit_house_list_details', function (e) {
        e.preventDefault();
        const title = $('#productTitle').val();
        const price = $('#productPrice').val();
        const address = $('#productAddress').val();
        const detail = $('#productDetail').val();
        const addType = $('input[name="addTyoe"]:checked').val();

        const userData = JSON.parse(localStorage.getItem('homelyAuth'));
        const accessToken = userData.accessToken;
        const email = userData.email;
        const url = `http://localhost/rent_house_uiu/backend/api/v1/submit_house_builder/abh1522agwe522255hba514125/${accessToken}/${email}`;

        $.ajax({
            url: url,
            type: 'POST',
            data: JSON.stringify({
               author: email,
               title: title,
               price: price,
               address: address,
               detail: detail,
               addType: addType,
           }),
           contentType: 'application/json',
           beforeSend: function () {
             $("#submit_house_list_details").attr("disabled", "disabled");
             $("#submit_house_list_details").html("Please Wait");
           },
            success: function (response) {
                window.open(response.url, '_blank');
                window.location.reload();
            },
            error: function (error) {
                console.error(error);
            }
        });
    });
});
