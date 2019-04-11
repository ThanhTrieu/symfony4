'use strict';

/** @param {jQuery} $ jQuery Object */
!(function ($, window, document) {
    'use strict';

    var sliderRoot = $('.full-slider-inner');
    var changing = false;
    var slider = false;
    var resizeSLider = function resizeSLider() {
        if (changing) {
            return false;
        }
        changing = true;
        if ($(window).width() < 992) {
            //INIT SLIDER
            if (!sliderRoot.hasClass('light-slider')) {
                $('.full-slider .item').each(function () {
                    var parent = $(this).closest('.parent-tmp');
                    if (parent.length) {
                        $(this).insertBefore(parent);
                        if (!parent.children().length) {
                            parent.remove();
                        }
                    }
                });
                sliderRoot.addClass('light-slider');
                if (!slider.lightSlider) {
                    // if(lightSlider.length) {
                    slider = $('.light-slider').lightSlider({
                        item: 1,
                        loop: true,
                        slideMargin: 0,
                        adaptiveHeight: true,
                        onSliderLoad: function onSliderLoad() {
                            changing = false;
                        },
                    });
                    // }
                }
            }
        } else {
            //DESTROY SLIDER
            if (slider.lightSlider && sliderRoot.hasClass('light-slider')) {
                slider.destroy();
                sliderRoot.removeClass('light-slider');
                $('.full-slider .item').each(function () {
                    var parent = $(this).closest('.parent-tmp');
                    if (!parent.length) {
                        var parentClass = $(this).attr('data-parent');
                        var parentGroup = $(this).attr('data-group');
                        var existing = sliderRoot.find('.' + parentClass + '[data-group="' + parentGroup + '"]');
                        if (existing.length) {
                            $(this).appendTo(existing);
                        } else {
                            existing = $('<div class="parent-tmp ' + parentClass + '" data-group="' + parentGroup + '"/>');
                            existing.insertBefore($(this));
                        }
                        $(this).appendTo(existing);
                        parent.remove();
                    }
                });
            }
            changing = false;
        }
        window.setTimeout(function () {
            if (changing) {
                changing = false;
            }
        }, 200);
    };
    resizeSLider();
    $(window).resize(function () {
        resizeSLider();
    });
})(jQuery, window, document);
