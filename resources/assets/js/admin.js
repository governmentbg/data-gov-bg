$(function() {
    if ($('.js-change-pass').length) {
        $('.js-change-pass').on('submit', function(e) {
            e.preventDefault();

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
                        $('#oldPass').val('');
                        $('#password').val('');
                        $('#confPass').val('');
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

$(function() {
    if ($('.js-add-member').length) {
        $('.js-add-member').on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: $('.js-add-member').data('url'),
                type: 'POST',
                data: $('.js-add-member').serialize(),
                success: function(data) {
                    var response = JSON.parse(data);

                    if (response.success) {
                        $('#js-alert-success').show();
                        $('.alert-success').fadeTo(3000, 500).slideUp(500, function(){
                            $('.alert-success').slideUp(500);
                        });
                        location.reload();
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

$(function() {
    if ($('.js-invite-member').length) {
        $('.js-invite-member').on('submit', function(e) {
            var invButton = $(document.activeElement);
            invButton.prop('disabled', true);
            e.preventDefault();

            $.ajax({
                url: $('.js-invite-member').data('url'),
                type: 'POST',
                data: $('.js-invite-member').serialize(),
                success: function(data) {
                    var response = JSON.parse(data);

                    if (response.success) {
                        $('#js-alert-success').show();
                        $('.alert-success').fadeTo(3000, 500).slideUp(500, function(){
                            $('.alert-success').slideUp(500);
                        });
                        location.reload();
                    } else {
                        $('#js-alert-danger').show();
                        $('.alert-danger').fadeTo(3000, 500).slideUp(500, function(){
                            $('.alert-danger').slideUp(500, function() {
                                invButton.prop('disabled', false);
                            });
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
