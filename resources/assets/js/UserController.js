$(function() {
    if ($('.js-logo').length) {
        var $button = $('.js-logo');
        var $input = $('.js-logo-input');
        var $preview = $('.js-preview');

        $button.on('click', function(e) {
            $input.trigger('click');
        });

        $input.change(function() {
            readURL(this);
            $preview.removeClass('hidden');
        });
    }

    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $preview.attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    if ($('#delete-confirm').length) {
        $('#confirm').on('click', function(e) {
            $('#delete-confirm').modal('toggle');
        })
    }

    if ($('.js-select').length) {
        $('.js-select').select2({minimumResultsForSearch: -1});
    }
});

$(function(){
    $('#sendTermOfUseReq').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/user/sendTermsOfUseReq',
            type: 'POST',
            data: $('#sendTermOfUseReq').serialize(),
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
});
