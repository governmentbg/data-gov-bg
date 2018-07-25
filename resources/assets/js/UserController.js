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
});
