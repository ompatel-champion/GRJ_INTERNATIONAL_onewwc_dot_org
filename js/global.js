/**
 * @version 8.2 [rev.8.2.01]
 */

jQuery(document).ready(function ($) {
    if (typeof slickRtl === 'undefined') {
        slickRtl = false;
    }

    $('.alert-box').on('blur', function (e) {
        e.preventDefault();

        var message = $(this).attr('data-message');
        bootbox.alert(message);
    });

    $('.dialog-box').on('click', function (e) {
        e.preventDefault();

        var href = $(this).attr('href');
        var title = $(this).attr('title');

        $.get(href, function (data) {
            bootbox.dialog({
                title: title,
                message: data,
                closeButton: false,
                buttons: {
                    main: {
                        label: msgs.close,
                        className: "btn-default"
                    }
                }
            });
        });
    });

    $('.confirm-box').on('click', function (e) {
        e.preventDefault();

        var href = $(this).attr('href');
        var message = msgs.confirmThisAction;
        if ($(this).attr('data-message')) {
            message = $(this).attr('data-message');
        }

        bootbox.confirm({
            buttons: {
                confirm: {
                    label: msgs.ok,
                    className: "btn-primary"
                },
                cancel: {
                    label: msgs.cancel,
                    className: "btn-default"
                }
            },
            message: message,
            callback: function (result) {
                if (result) {
                    window.location.replace(href);
                }
            }
//            title: "You can also add a title"
        });
    });

    $('.confirm-form').on('click', function (e) {
        e.preventDefault();

        var message = msgs.confirmThisAction;
        var confirmFormBtn = $(this);

        if (confirmFormBtn.attr('data-message')) {
            message = $(this).attr('data-message');
        }
        var option = confirmFormBtn.val();
        var form = confirmFormBtn.closest('form');

        bootbox.confirm({
            buttons: {
                confirm: {
                    label: msgs.ok,
                    className: "btn-primary"
                },
                cancel: {
                    label: msgs.cancel,
                    className: "btn-default"
                }
            },
            message: message,
            callback: function (result) {
                if (result) {
                    form.find('[name="option"]').val(option);

                    var btnFormAction = confirmFormBtn.attr('formaction');

                    if (typeof btnFormAction !== "undefined") {
                        form.attr('action', btnFormAction);
                    }

                    form.submit();
                }
            }
//            title: "You can also add a title"
        });
    });

    // checkboxes - select all/none
    $('[name="selectAll"]').click(function () {
        var checked = $(this).prop('checked');
        $('.select-all').prop('checked', checked);
    });

    // postage calculator from listing details page
    $('#calculate-postage').click(function () {
        $('#postage-calculator').find('.result').calculatePostage({
            selector: '#postage-calculator',
            postUrl: paths.calculatePostage,
            btn: $(this)
        });
    });

    /* attach loading modal behavior to button */
    $('.btn-loading-modal').on('click', function () {
        $('body').addClass('loading');
    });

    if (!modRewrite) {
        // workaround for posting get forms when mod rewrite is not available
        $('form').submit(function (e) {
            if ($(this).attr('method').toLowerCase() === 'get') {
                e.preventDefault();
                $(this).attr('method', 'post');
                $(this).submit();
            }
        });
    }

    $('pre').each(function () {
        $(this).text($(this).html()); //makes the html into plaintext
    });

    /* jquery table rows filter */
    $('.table-filter').keyup(function () {
        var rex = new RegExp($(this).val(), 'i');
        var table = $(this).closest('table');
        table.find('.searchable tr').hide();
        table.find('.searchable tr').filter(function () {
            return rex.test($(this).text());
        }).show();
    });

    // @version 7.6

    // open form in a bootbox modal
    $('.jq-popup-form').on('click', function (e) {
        e.preventDefault();

        $('body').addClass('loading');

        var title = $(this).attr('title');
        var onCloseRedirect = $(this).attr('data-close-redirect');
        var url = null;
        var method = 'GET';
        var data = null;

        if ($(this).is(':submit')) {
            var form = $(this).closest('form');

            var formMethod = form.attr('method');
            if (formMethod !== '' && $.type(formMethod) !== 'undefined') {
                method = formMethod;
            }

            url = $(this).attr('formaction');
            if (url === '' || $.type(url) === 'undefined') {
                url = form.attr('action');
            }

            data = form.serializeArray();
            data.push({name: this.name, value: this.value});
        } else {
            url = $(this).attr('href');
        }

        $.ajax({
            url: url,
            data: data,
            method: method,
            success: function (data) {
                bootbox.dialog({
                    title: title,
                    message: data,
                    closeButton: false,
                    buttons: {
                        main: {
                            label: msgs.close,
                            className: "btn-default",
                            callback: function () {
                                if (onCloseRedirect !== '' && $.type(onCloseRedirect) !== 'undefined') {
                                    $('body').addClass('loading');
                                    window.location.reload(onCloseRedirect);
                                }
                            }
                        }
                    }
                });

                $('body').removeClass('loading');
            }
        });
    });

    // full size images gallery
    $('.jq-gallery').magnificPopup({
        type: 'image',
        gallery: {
            enabled: true
        }
    });

    // ajax load pages in popup - use href as ajax load destination
    $('.ajax-popup').magnificPopup({
        type: 'ajax'
    });

    // workaround so that slides do not display unless the slick slider library has been loaded
    $('.jq-loading-slider')
        .on('init', function (slick) {
            $(this).find('.jq-slide').removeClass('hidden');
        });

    // slick carousel implementation for the listing details page main image and thumbnails
    $('#jq-mainImage')
        .slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: false,
            arrows: true,
            asNavFor: '#jq-thumbnails',
            rtl: slickRtl
        })
        .find('img')
        .on('click', function () {
            var thumbId = $(this).attr('data-gallery-id');
            $('.jq-gallery').eq(thumbId).trigger('click');
        });

    $('#jq-thumbnails')
        .slick({
            vertical: true,
    		infinite: true,
    		slidesPerRow: 1,
    		slidesToShow: 1,
    		asNavFor: '#jq-mainImage',
    		focusOnSelect: true,
    		arrows: true,
    		draggable: true,
        });

    // slick carousel implementation for home page slider
    $('.jq-rs-slider')
        .slick({
            dots: true,
            arrows: false,
            autoplay: slickAutoplay,
            autoplaySpeed: slickAutoplaySpeed,
            rtl: slickRtl
        });

    // left side search checkboxes - submit on click
    $('#searchFilterForm > form').find('input[type="checkbox"]').on('click', function () {
        $(this).closest('form').trigger('submit');
    });

    // left side and mobile search boxes
    $('.btn-icon-search').on('click', function () {
        $(this).closest('form').trigger('submit');
    });

    // if submitting the header search form with only a category selected and no keywords, change the submission url
    // to the category's url
    $('#form-header-search').on('submit', function (e) {
        var srcForm = $(this).closest('form');
        var keywords = srcForm.find('input[name="keywords"]').val();

        if (keywords === '') {
            var parentId = srcForm.find('input[name="parent_id"]').val();

            if (parentId !== '') {
                var category = srcForm.find('[data-id=' + parentId + ']');
                if (category !== undefined) {
                    var href = category.attr('href');
                    if (href !== '' && href !== undefined) {
                        e.preventDefault();
                        window.location.replace(href);
                    }
                }
            }
        }
    });

    // initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // add to cart
    // @version 8.0: UNUSED
    $('.jq-add-to-cart').on('click', function (e) {
        // e.preventDefault();
        // var el = $(this);
        // var form = el.closest('form');
        //
        // var formData = form.serializeArray();
        // formData.push({name: el.prop('name'), value: el.val()});
        //
        // $.ajax({
        //     type: form.attr('method'),
        //     url: form.attr('action'),
        //     data: formData,
        //     success: function (data) {
        //         bootbox.alert(data.message);
        //
        //         if (data.success) {
        //             $('.au-cart-dropdown').html(data.cartDropdown);
        //             feather.replace();
        //         }
        //     },
        //     dataType: 'json'
        // });
    });

    $(document).on('click', '.jq-cart-action', function (e) {
        e.preventDefault();

        var el = $(this);

        $.get({
            url: el.prop('href'),
            data: {
                async: true
            },
            method: 'GET',
            success: function (data) {
                bootbox.alert(data.message);

                if (data.success) {
                    $('.au-cart-dropdown').html(data.cartDropdown);
                    feather.replace();
                }
            },
            dataType: 'json'
        });
    });
});

/* chevron icon animation for sidebar submenus */
$('.nav-dropdown-toggle').click(function (e) {
    e.preventDefault();
    $(this).toggleClass('open');
});
/* /chevron icon animation for sidebar submenus */

/* activate sticky buttons */
$('.form-sticky-buttons').stickyButtons();

/* activate wish list async buttons */
$('.btn-wishlist-async').wishListButton();

/* activate grid / list toggle for cards on browse pages */
$('#listGridToggle').listGridToggle();
