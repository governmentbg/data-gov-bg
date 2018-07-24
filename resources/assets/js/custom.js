// show on load functionality
$(function() {
    $(window).on('load', function() {
        $('.js-show-on-load').css('visibility', 'visible');
    });
});


$(function() {
    if ($('.js-check').length) {
        $('.js-check').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    }
});

// show hide submenu
$(function() {
    $('.clicable').on('click', function(){
        $this = $(this).closest('.js-show-submenu');
        $childMenu = $this.children('.sidebar-submenu');
        $childIcon = $(this).children('i.fa');

        if ($childMenu.is(':hidden')) {
            $childIcon.toggleClass('fa-angle-up').toggleClass('fa-angle-down');
            $this.addClass('remove-after');
            $childMenu.show();
        } else {
            $childIcon.toggleClass('fa-angle-up').toggleClass('fa-angle-down');
            $this.removeClass('remove-after');
            $childMenu.hide();
        }
    });
});

// show hide infobox
$(function() {
    $('.js-toggle-info-box').on('click', function() {
        $parent = $(this).parent();
        $infoBox = $parent.children('.info-box');
        $infoBox.toggle();
    });
});

//custom placeholders for adding tags
$('.tagsBG input').attr('placeholder', 'Въведете нов етикет...');
$('.tagsEN input').attr('placeholder', 'Enter new tag...');

//datepicker settings
$(function() {
    var lang = document.cookie.replace(/(?:(?:^|.*;\s*)language\s*\=\s*([^;]*).*$)|^.*$/, "$1");

    $('.datepicker').datepicker({
        language: 'bg',
        weekStart: 1,
        todayHighlight: true,
        format: 'dd-mm-yyyy',
        autoclose: true
    }).datepicker("setDate", "0");
});

//close navbar menu on mobile version
$('.js-close-navbar').on('click', function(){
    $('#myNavbar').removeClass('in');
});

 $('.btn-sidebar').click(function(e) {
    e.preventDefault();

    var $sidebar = $('.js-sidenav');

    if ($("#sidebar-wrapper").is(':visible')) {
        $("#sidebar-wrapper").css('display', 'none');
        $('.sidebar-open').show();
        $('#app').css('min-height', 'auto');
    } else {
        $('.sidebar-open').hide();
        $("#sidebar-wrapper").css('display', 'block');
        $("#sidebar-wrapper").css('height', 'max-content !important');

        if ($('#app').height() < $sidebar.height()) {
            $('#app').css('min-height', ($sidebar.height() + 220) +'px');
        }
    }
});

