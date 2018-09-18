// show on load functionality
$(function() {
    $(document).ready(function() {
        $('.js-show-on-load').css('visibility', 'visible');

        if ($('.nano').length) {
            $('.nano').nanoScroller({});
        }

        $('.js-parent-org-filter').change(function() {
            this.form.submit();
        });
    });
});

// data tables functionality
$(function() {
    $(document).ready(function() {
        $('.data-table').DataTable({
            pageLength: 25,
            responsive: true,
            language: {
                search: $('.js-translations').data('search'),
                info: $('.js-translations').data('info'),
                infoFiltered: $('.js-translations').data('info-filtered'),
                infoEmpty: $('.js-translations').data('info-empty'),
                zeroRecords: $('.js-translations').data('zero-records'),
                lengthMenu: $('.js-translations').data('length-menu'),
                paginate: {
                    first: $('.js-translations').data('first'),
                    last: $('.js-translations').data('last'),
                    next: $('.js-translations').data('next'),
                    previous: $('.js-translations').data('previous'),
                },
            }
        });
    });
});

$(function () {
    var $checkboxes = $('.js-check');

    if ($checkboxes.length) {
        $checkboxes.each(function () {
            var $checkbox = $(this);

            $checkbox.iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green'
            });

            $checkbox.on('ifClicked', function () {
                var $this = $(this);

                if ($this.hasClass('js-uncheck')) {
                    if ($('input', $this).prop('checked')) {
                        $this.iCheck('uncheck');
                    }
                }

                if ($this.hasClass('js-submit')) {
                    setTimeout(function() {
                        $this.parents('form').submit();
                    }, 100);
                }
            });
        });
    }
});

// show hide submenu
$(function() {
    $('.clicable').on('click', function() {
        var $this = $(this).closest('.js-show-submenu');
        var $childMenu = $this.children('.sidebar-submenu');
        var $childIcon = $(this).children('i.fa');

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

    $('.clicable').each(function() {
        var $this = $(this).closest('.js-show-submenu');
        var $childMenu = $this.children('.sidebar-submenu');

        $('a', $childMenu).each(function () {
            if ($(this).hasClass('active')) {
                $childMenu.show();
                $this.addClass('remove-after');
            }
        });
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

    $('.datepicker').each(function() {
        $(this).attr('autocomplete', 'off');

        $(this).datepicker({
            language: 'bg',
            weekStart: 1,
            todayHighlight: true,
            format: 'dd-mm-yyyy',
            autoclose: true,
            setDate: ''
        });
    });
});

//close navbar menu on mobile version
$('.js-close-navbar').on('click', function(){
    $('#my-navbar').removeClass('in');
});

 $('.btn-sidebar').click(function(e) {
    e.preventDefault();

    var $sidebar = $('.js-sidenav');

    if ($('#sidebar-wrapper').is(':visible')) {
        $('#sidebar-wrapper').css('display', 'none');
        $('.sidebar-open').show();
        $('#app').css('min-height', 'auto');
    } else {
        $('.sidebar-open').hide();
        $('#sidebar-wrapper').css('display', 'block');
        $('#sidebar-wrapper').css('height', 'max-content !important');

        if ($('#app').height() < $sidebar.height()) {
            $('#app').css('min-height', ($sidebar.height() + 220) + 'px');
        }
    }
});

//on li hover get href and change color
$('.js-check-url').mouseover(function(){
    $href = $(this).children('a').attr('href');
    $href = $href.split('/');

    if ($href[3] != 'undefined' && $href[3] == 'user') {
        $('.js-check-url').addClass('user');
        $('.js-check-url').removeClass('index');
    } else {
        $('.js-check-url').addClass('index');
        $('.js-check-url').removeClass('user');
    }
});

/**
 * Confirmation dialog
 *
 */
$(function() {
    $('[data-confirm]').on('click', function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    })
});

$(function() {
    $('.js-doc-btn').on('click', function(e) {
        e.preventDefault();

        if ($(this).hasClass('edit')) {
            $('.js-doc-input').prop('disabled', false);
            $('.js-doc-input').attr('type', 'file');
        }

        $('.js-doc-input').trigger('click');
    })
});

$(function() {
    $('.js-terms-req-preview, .js-terms-req-close').on('click', function(event) {
        var index = $(this).data('index');
        var action = $(this).data('action');
        var $content = $('.js-terms-req-cont-' + index);
        var $btns = $('.js-terms-req-btns-' + index);

        switch (action) {
            case 'show':
                $content.removeClass('hidden');
                $btns.removeClass('hidden');
                $(this).addClass('hidden');
                break;
            case 'close':
                $content.addClass('hidden');
                $btns.addClass('hidden');
                $('.js-terms-req-preview[data-index="'+ index +'"]').removeClass('hidden');
                break;
        }

    });
});

$(function() {
    $('.js-from-filter, .js-to-filter').on('change', function(event) {
        var $form = $(this).closest('form');
        $form.submit();
    });
});

$(function() {
    if ($('.js-add-custom-field').length > 0) {

        $('.js-add-custom-field').on('click', function(e) {
            e.preventDefault();

            $('.js-custom-fields').append($('.js-custom-field-set').last().clone());

            var $element = $('.js-custom-field-set').last();
            $element.find('.hidden-input').remove();
            $('.js-delete-custom-field').removeClass('hidden');

            var index = $element.data('index');

            $element.attr('data-index', index + 1);
            $element.attr('data-id', null);

           $('input', $element).each(function() {
               $(this).attr('name', $(this).attr('name').replace(/\d/g, index + 1)).val('');
               $(this).removeAttr('disabled');
           });
        });

        $(document).on('click', '.js-delete-custom-field', function() {
            var $target = $(this).parents('.js-custom-field-set');

            if ($target.data('id')) {
                $.ajax({
                    url: '/delSettings',
                    delay: 1000,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: 'POST',
                    data: {id: $target.data('id')},
                    success: function(response) {
                        if (response.success) {
                            removeSett($target);
                        }
                    }
                });
            } else {
                removeSett($target);
            }
        });

        function removeSett($target) {
            if ($('.js-custom-field-set').length > 1) {
                $target.remove();
            } else {
                $('input', $target).each(function() {
                    $(this).val('');
                    $(this).removeAttr('disabled');
                });
            }
        }

    }

    var $tagsInput = $('[data-role="tagsinput"]');

    if ($tagsInput.length) {
        $tagsInput.each(function() {
            var $tagInput = $(this);

            $tagInput.siblings('.bootstrap-tagsinput').keydown(function(e) {
                var key = e.keyCode ? e.keyCode : e.which;

                if (key == 13) {
                    e.preventDefault();

                    $tagInput.tagsinput('add', $(this).find('input').val());

                    $(this).find('input').val('');

                    $tagInput.tagsinput('refresh');
                }
            });
        });
    }
});

// Sticky footer
var $head = $('.js-head');
var $content = $('.js-content');
var $footer = $('.js-footer');

if ($head && $content && $footer) {
    $(window).bind('load', function() {
        stickyFooter();

        $(window).resize(stickyFooter);
    });
}

function stickyFooter() {
    var windowHeight = $(document).height() > 0 ? $(document).height() : screen.height;
    var headerHeight = getInt($head.css('height')) + getInt($head.css('margin-bottom'));
    var footerHeight = getInt($footer.css('height'));
    var contentHeight = windowHeight - headerHeight - footerHeight;

    $content.css('min-height', contentHeight);

    $footer.removeClass('hidden');
}

function getInt(string) {
    return parseInt(string.replace(/[^0-9]/g, ''));
}

$(function () {
    $('#sendSignal').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $('#sendSignal').serialize(),
            success: function success(data) {
                var response = JSON.parse(data);
                if (response.success) {
                    $('#js-alert-success').show();
                    $('.alert-success').fadeTo(3000, 500).slideUp(500, function () {
                        $('.alert-success').slideUp(500);
                    });
                } else {
                    $('#js-alert-danger').show();
                    $('.alert-danger').fadeTo(3000, 500).slideUp(500, function () {
                        $('.alert-danger').slideUp(500);
                    });
                }
            },
            error: function error(jqXHR) {
                $('#js-alert-danger').show();
                $('.alert-danger').fadeTo(2000, 500).slideUp(500, function () {
                    $('.alert-danger').alert('close');
                });
            }
        });
    });
});

$(function() {
    if ($('#js-code').length > 0) {
        var width = $("#js-width").val();
        var height = $("#js-height").val();
        var uri = $(".js-res-uri").data('uri');

        $("#js-width").on("change paste keyup", function(width, height) {
            width = $(this).val();
            height = $("#js-height").val();
            updateTextarea(width, height);
        });

        $("#js-height").on("change paste keyup", function(width, height) {
            height = $(this).val();
            width = $("#js-width").val();
            updateTextarea(width, height);
        });

        updateTextarea(width, height);

        $(".js-copy").on("click", function() {
            $('#js-code').select();
            document.execCommand("copy");
        });

        function updateTextarea(width, height) {
            var code = '<iframe width="'+ width +'" height="'+ height;
            code += '" src="'+ window.location.origin +'/data/resource/embed/'+ uri +'"></iframe>';
            $('#js-code').val(code);
        }
    }
});