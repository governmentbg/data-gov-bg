$(function() {
    if ($('.js-change-pass').length) {
        $('.js-change-pass').on('submit', function(e) {
            e.preventDefault();
            console.log($('.js-change-pass').data('url'));
            $.ajax({
                url: $('.js-change-pass').data('url'),
                type: 'POST',
                data: $('.js-change-pass').serialize(),
                success: function(data) {
                    var response = JSON.parse(data);
                    if (response.success) {
                        $('#js-alert-success').show();
                        $('.alert-success').fadeTo(3000, 500).slideUp(500, function(){
                            $('.alert-success').slideUp(500);
                        });
                    } else {
                        $('#js-alert-danger').show();
                        $('.alert-danger').fadeTo(3000, 500).slideUp(500, function(){
                            $('.alert-danger').slideUp(500);
                        });
                    }
                },
                error: function (jqXHR) {
                    $('#js-alert-danger').show();
                    $('.alert-danger').fadeTo(2000, 500).slideUp(500, function(){
                        $('.alert-danger').alert('close');
                    });
                }
            });
        });
    }
});
