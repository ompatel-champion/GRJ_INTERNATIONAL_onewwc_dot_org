/**
 * @version 8.1 [rev.8.1.01]
 */
/**
 * MOD:- PICKUP LOCATIONS
 */

(function ($) {
    /* array intersect function */
    $.arrayIntersect = function (a, b) {
        return $.grep(a, function (i) {
            return $.inArray(i, b) > -1;
        });
    };
    /* /array intersect function */

    /* sticky buttons function */
    $.fn.stickyButtons = function (options) {
        var settings = $.extend({
            buttonsContainerClass: ".buttons-sticky-bottom"
        }, options);

        var form = $(this);
        var stickyButtonsContainer = form.find(settings.buttonsContainerClass);
        var defaultMessage = stickyButtonsContainer.data('message');
        var noResultsMessage = stickyButtonsContainer.data('message-no-results');

        if (defaultMessage) {
            stickyButtonsContainer.css('display', 'block');
            stickyButtonsContainer.find('button').hide();
            stickyButtonsContainer.append('<h6 class="message text-dark m-0">' + defaultMessage + '</h6>');
        }
        else {
            stickyButtonsContainer.css('display', 'none');
        }

        form.find('input:checkbox').on('change', function () {
            var array = [];
            var counter = 0;

            form.find('input:checkbox:checked').each(function () {
                var data = [];

                if ($(this).data('buttons')) {
                    data = $(this).data('buttons').split(' ');
                    counter++;
                }

                if (array.length) {
                    array = $.arrayIntersect(array, data);
                }
                else {
                    array = data;
                }
            });

            if (array.length) {
                $.each(array, function (index, item) {
                    var split = item.split('_');
                    array[index] = split[0];
                });

                // console.log(array);

                stickyButtonsContainer.find('button').hide()
                    .each(function () {
                        var button = $(this);
                        var min = button.data('min-results');
                        var display = true;


                        if (typeof min !== "undefined" && min > counter) {
                            display = false;
                        }

                        if ($.inArray(button.val(), array) > -1 && display) {
                            button.show();
                        }
                    });

                stickyButtonsContainer.find('.message').hide();
                stickyButtonsContainer.css('display', 'block');
            }
            else {
                stickyButtonsContainer.find('button').hide();

                var message = null;

                // we have checkboxes selected
                if (counter > 0) {
                    if (noResultsMessage) {
                        message = noResultsMessage;
                    }
                    else if (defaultMessage) {
                        message = defaultMessage;
                    }
                }
                else {
                    if (defaultMessage) {
                        message = defaultMessage;
                    }
                }

                if (message) {
                    stickyButtonsContainer.find('.message').text(message).show();
                }
                else {
                    stickyButtonsContainer.css('display', 'none');
                }
            }
        });
    };
    /* /sticky buttons function */

    /* search autocomplete function */
    $.fn.searchAutocomplete = function (options) {
        var element = $(this);

        var settings = $.extend({
            // defaults
            url: null,
            // items: [], // not used
            // theme: "standard", // not used
            limit: 5, // results limit
            chars: 1
        }, options, element.data());

        // initialize dropdown
        element.addClass('dropdown-toggle')
            .attr('data-toggle', 'dropdown');

        element.after('<div class="dropdown-menu"></div>');
        element.next().andSelf().wrapAll('<div class="dropdown"></div>');

        var dropdownMenu = element.next();

        element.on('click keyup', function (e) {
            e.stopPropagation();

            var term = $(this).val();

            // jquery post, url != null
            if (settings.url !== null) {
                if (term.length >= settings.chars) {
                    $.post(
                        settings.url,
                        {
                            input: term,
                            limit: settings.limit
                        },
                        function (data) {
                            dropdownMenu.html('');

                            $.each(data, function (index, item) {
                                var itemLabel = item.label;
                                var reg = new RegExp('(' + term + ')', 'gi');

                                itemLabel = itemLabel.replace(reg, '<span>$1</span>');

                                if (item.itemType === 'header') {
                                    dropdownMenu.append('<h6 class="dropdown-header">' + itemLabel + '</h6>');
                                }
                                else {
                                    dropdownMenu.append('<a class="dropdown-item" href="' + item.path + '">' + itemLabel + '</a>');
                                }

                                dropdownMenu.find('.dropdown-item').first().addClass('hover');
                            });

                            if (data.length === 0) {
                                element.closest('.dropdown').removeClass('show').find('.dropdown-menu').removeClass('show');
                                element.dropdown('dispose');
                            }
                            else if (element.closest('.dropdown').hasClass('show') === false) {
                                element.dropdown('toggle');
                            }
                        },
                        'json'
                    );
                }
                else {
                    element.closest('.dropdown').removeClass('show').find('.dropdown-menu').removeClass('show');
                    element.dropdown('dispose');
                }
            }
        });

        $(document).on('hover mouseover', '.dropdown-menu > a', function () {
            dropdownMenu.find('.hover').removeClass('hover');
        });
    };
    /* /search autocomplete function */

    /* wish list add / remove function */
    $.fn.wishListButton = function (options) {
        var element = $(this);

        var settings = $.extend({
            classDefault: 'btn-default',
            classActive: 'btn-primary',
            bootbox: false
        }, options, element.data());

        element.on('click', function (e) {
            e.preventDefault();
            var el = $(this);
            var url = el.prop('href');

            $.post(
                url,
                {
                    async: true
                },
                function (data) {
                    if (data.action === 'add') {
                        el.removeClass(settings.classDefault).addClass(settings.classActive).blur();
                    }
                    else if (data.action === 'remove') {
                        el.removeClass(settings.classActive).addClass(settings.classDefault).blur();
                    }

                    if (settings.bootbox === true) {
                        bootbox.alert(data.message['msg']);
                    }
                },
                'json'
            );

        });
    };
    /* /wish list add / remove function */

    /* list / grid cards toggle function */
    $.fn.listGridToggle = function (options) {
        var element = $(this);

        var settings = $.extend({
            cookieName: 'list_grid',
            btnListId: 'btn-list',
            btnGridId: 'btn-grid',
            defaultBtnClass: 'btn-default',
            activeBtnClass: 'btn-secondary'
        }, options, element.data());

        var btnList = $('#' + settings.btnListId);
        var btnGrid = $('#' + settings.btnGridId);

        var gridClass = element.data('grid-class');
        var listClass = element.data('list-class');

        btnList.on('click', function (e) {
            e.preventDefault();
            $.cookie(settings.cookieName, 'list', {path: baseUrl, expires: 30});

            btnGrid.removeClass(settings.activeBtnClass).addClass(settings.defaultBtnClass);
            btnList.addClass(settings.activeBtnClass).removeClass(settings.defaultBtnClass).blur();

            element.children().removeClass(gridClass).addClass(listClass);
        });

        btnGrid.on('click', function (e) {
            e.preventDefault();
            $.cookie(settings.cookieName, 'grid', {path: baseUrl, expires: 30});

            btnList.removeClass(settings.activeBtnClass).addClass(settings.defaultBtnClass);
            btnGrid.addClass(settings.activeBtnClass).removeClass(settings.defaultBtnClass).blur();

            element.children().removeClass(listClass).addClass(gridClass);
        });

        var listGridCookie = $.cookie(settings.cookieName);

        if (listGridCookie === 'list') {
            btnList.click();
        }
        else {
            btnGrid.click();
        }
    };
    /* /list / grid cards switch function */

    /* postage calculator jquery plugin */
    $.fn.calculatePostage = function (data) {

        // the data that will be used by the function to output the postage options
        var settings = $.extend({
            selector: null,
            btn: null,
            postUrl: null,
            ids: null,
            quantity: null,
            locationId: null,
            postCode: null,
            enableSelection: null,
            formSubmit: true,
            initialLoad: false,
            postageId: null,
            /* ## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ] */
            pickup: null,
            postageAmountField: null,
            invoiceTotalsBox: false
        }, data);

        if (settings.btn !== null) {
            settings.btn.button('loading');
        }

        // shortcut method
        if (settings.selector !== null) {
            // no overrides for the settings
            if (settings.ids === null) {
                settings.ids = $(settings.selector).find('.ids').map(function () {
                    return $(this).val();
                }).get();
            }
            if (settings.quantity === null) {
                settings.quantity = $(settings.selector).find('.qty').map(function () {
                    return $(this).val();
                }).get();
            }

            if (settings.locationId === null) {
                settings.locationId = $(settings.selector).find('select[name="locationId"]').val();
            }
            if (settings.postCode === null) {
                /* ## -- ONE LINE :: CHANGE -- [ MOD:- PICKUP LOCATIONS ] */
                settings.postCode = $(settings.selector).find('[name="postCode"]').val();
            }
            if (settings.enableSelection === null) {
                settings.enableSelection = $(settings.selector).find('input[name="enableSelection"]').val();
            }
            /* ## -- START :: ADD -- [ MOD:- PICKUP LOCATIONS ] */
            if (settings.pickup === null) {
                var pickupElement = $(settings.selector).find('input[name="pickup"]');

                if (pickupElement.length > 0) {
                    if (pickupElement.is(':checked')) {
                        settings.pickup = 1;
                    }
                    else {
                        settings.pickup = -1;
                    }
                }

            }
            /* ## -- END :: ADD -- [ MOD:- PICKUP LOCATIONS ] */
            if (settings.postageId === null) {
                settings.postageId = $(settings.selector).find('input[name="postage_id"]').val();
            }
        }

        var selector = this;

        $.post(
            settings.postUrl,
            {
                ids: settings.ids,
                quantity: settings.quantity,
                locationId: settings.locationId,
                postCode: settings.postCode,
                /* ## -- ONE LINE :: ADD -- [ MOD:- PICKUP LOCATIONS ] */
                pickup: settings.pickup,
                enableSelection: settings.enableSelection,
                formSubmit: settings.formSubmit,
                postageId: settings.postageId
            },
            function (data) {
                if (settings.btn !== null) {
                    setTimeout(function () {
                        settings.btn.button('reset')
                    }, 500);
                }

                return selector.each(function () {
                    selector.html(data);

                    if (settings.enableSelection) {
                        if ($(settings.selector).find('input:radio[name="postage_id"]').length === 0) {
                            $(settings.selector).find('input:hidden[name="postage_id"]').val('');
                            if (settings.postageAmountField !== null) {
                                settings.postageAmountField.val('');
                            }
                        }
                        else {
                            $(settings.selector).find('input:hidden[name="postage_id"]').val($(settings.selector).find('input:radio[name="postage_id"]:checked').val());
                        }
                    }

                    if (settings.postageAmountField !== null && settings.initialLoad === false) {
                        var price = $(settings.selector).find('input:radio[name="postage_id"]:checked').data('price');
                        settings.postageAmountField.val(price);
                    }

                    if (settings.invoiceTotalsBox === true) {
                        var updatePostageAmount = false;
                        if (settings.initialLoad === false) {
                            updatePostageAmount = true;
                        }
                        InvoiceTotalsBox(updatePostageAmount);
                    }
                });

            }
        );
    };
    /* /postage calculator jquery plugin */

    /* composite element function - we attach the function to the container */
    $.fn.elementComposite = function (options) {
        var container = $(this);

        var settings = $.extend({
            row: 'composite-row',
            btnAdd: 'btn-composite-add',
            btnDelete: 'btn-composite-delete',
            btnMoveUp: 'btn-composite-move-up',
            btnMoveDown: 'btn-composite-move-down',
            htmlAdd: 'Add',
            htmlDelete: 'Delete',
            htmlMoveUp: '<span data-feather="chevron-up"></span>',
            htmlMoveDown: '<span data-feather="chevron-down"></span>',
            arrange: false
        }, options, container.data());

        var moveUp = function (row) {
            var before = row.prev();
            row.insertBefore(before);
        };

        var moveDown = function (row) {
            var after = row.next();
            var last = container.find('.' + settings.row).last();

            if (!after.is(last)) {
                row.insertAfter(after);
            }
        };

        var deleteRow = function (row) {
            row.remove();
        };

        var addRow = function (row) {
            container.find('select').each(function () {
                if ($(this)[0].selectize) {
                    // store the current value of the select/input
                    var value = $(this).val();
                    // store options in case we have a select box with remote options
                    var options = $.map($(this).find('option'), function (option) {
                        return option;
                    });

                    // destroy selectize()
                    $(this)[0].selectize.destroy();

                    var selectElement = $(this);
                    if (selectElement.hasClass('element-selectize-remote')) {
                        $.each(options, function (index, option) {
                            selectElement.append(new Option(option.text, option.value, false, option.selected));
                        });
                    }
                    else {
                        selectElement.val(value);
                    }
                }
            });

            /* new row is called 'cloned' */
            var cloned = row.clone(true, true);

            cloned.find('input, select, textarea').each(function () {
                $(this).val('');
                $(this).attr('checked', false);
            });

            row.find('.' + settings.btnAdd).remove();

            if (settings.arrange === true) {
                $('<a>').attr('href', '#')
                    .addClass(settings.btnMoveUp)
                    .addClass('btn btn-light ml-1')
                    .html(settings.htmlMoveUp)
                    .appendTo(row);
                $('<a>').attr('href', '#')
                    .addClass(settings.btnMoveDown)
                    .addClass('btn btn-light ml-1')
                    .html(settings.htmlMoveDown)
                    .appendTo(row);
            }

            $('<button>')
                .addClass(settings.btnDelete)
                .addClass('btn btn-danger ml-1')
                .html(settings.htmlDelete)
                .appendTo(row);

            cloned.insertAfter(row);

            var cnt = 0;

            container.find('.' + settings.row).each(function () {
                $(this).find('input, select, textarea').each(function () {
                    var inputName = $(this).prop('name').replace(/(\[\d+\])/g, '[' + cnt + ']');
                    $(this).prop('name', inputName);
                });

                cnt++;
            });

            container.find('select').each(function () {
                var selectizeFunctionName = $(this).data('function-name');
                var selectizeFunction = eval(selectizeFunctionName);
                if (typeof selectizeFunction === 'function') {
                    selectizeFunction($(this));
                }
            });
        };

        container
            .on('click', '.' + settings.btnMoveUp, function (e) {
                e.preventDefault();
                moveUp($(this).closest('.' + settings.row));
            })
            .on('click', '.' + settings.btnMoveDown, function (e) {
                e.preventDefault();
                moveDown($(this).closest('.' + settings.row));
            })
            .on('click', '.' + settings.btnAdd, function (e) {
                e.preventDefault();
                addRow($(this).closest('.' + settings.row));
                feather.replace();
            })
            .on('click', '.' + settings.btnDelete, function (e) {
                e.preventDefault();
                deleteRow($(this).closest('.' + settings.row));
            });
    };
    /* /composite element function - we attach the function to the container */

    /* form async submit function */
    $.fn.formAsync = function (options) {
        var settings = $.extend({
            wrapper: ".form-async-wrapper",
            redirectUrl: null,
            submitElementDisabled: true,
            submitElementValue: null,
            headerLinksUrl: null,
            onChange: null,
            ajaxActions: null
        }, options);

        var form = $(this);

        var wrapper = form.data('wrapper');
        if (typeof wrapper !== "undefined") {
            settings.wrapper = wrapper;
        }

        var redirectUrl = form.data('redirect-url');
        if (typeof redirectUrl !== "undefined") {
            settings.redirectUrl = redirectUrl;
        }

        var submitElementDisabled = form.data('submit-element-disabled');
        if (typeof submitElementDisabled !== "undefined") {
            settings.submitElementDisabled = submitElementDisabled;
        }

        var submitElementValue = form.data('submit-element-value');
        if (typeof submitElementValue !== "undefined") {
            settings.submitElementValue = submitElementValue;
        }

        var onChange = form.data('form-on-change');
        if (typeof onChange !== "undefined") {
            settings.onChange = onChange;
        }

        var ajaxActions = form.data('ajax');
        if (typeof ajaxActions !== "undefined") {
            settings.ajaxActions = ajaxActions;
        }

        form.find('[type=submit]').on('click', function (e) {
            e.preventDefault();
            submitForm($(this));
        });

        if (settings.onChange !== null) {
            form.find(settings.onChange).on('change', function (e) {
                e.preventDefault();
                submitForm($(this));
            });
        }

        function submitForm(element) {
            var formData = form.serializeArray();
            formData.push({name: element.prop('name'), value: element.val()});

            var submitElement = form.find('[type=submit]');

            submitElement.attr('disabled', settings.submitElementDisabled);

            if (settings.submitElementValue !== null) {
                if (submitElement.is('button')) {
                    submitElement.text(settings.submitElementValue);
                }
                else {
                    submitElement.val(settings.submitElementValue);
                }
            }

            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: formData,

                success: function (data) {
                    try {
                        var obj = JSON.parse(data);
                        var redirectUrl = obj.redirectUrl;
                        if (redirectUrl !== '') {
                            window.location.replace(obj.redirectUrl);
                        }
                        else {
                            window.location.reload();
                        }
                    }
                    catch (e) {
                        form.closest(settings.wrapper).html(data);

                        /* run each ajax action attached */
                        if (settings.ajaxActions !== null) {
                            $.each(settings.ajaxActions, function (id, obj) {
                                $.ajax({
                                    type: 'POST',
                                    url: obj.url,
                                    data: obj.data,
                                    success: function (data) {
                                        $(obj.wrapper).html(data);
                                        feather.replace();
                                    }
                                });
                            });
                        }
                    }
                }
            });
        }
    };
    /* /form async submit function */

    /* button loading text function */
    $.fn.button = function(action) {
        if (action === 'loading' && this.data('loading-text')) {
            this.data('original-text', this.html()).html(this.data('loading-text')).prop('disabled', true);
        }
        if (action === 'reset' && this.data('original-text')) {
            this.html(this.data('original-text')).prop('disabled', false);
        }
    };
    /* button loading text function */
}(jQuery));
