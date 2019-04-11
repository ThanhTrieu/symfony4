// count view - TrieuNT
var countView = (function () {
    function updateView(type, id) {
        var i = new Image(1, 1);
        i.src = "/lg.gif?type=" + type + "&id=" + id;
        i.onload = function () {
            // Do nothing
        };
    }

    return {
        update: updateView
    }
})();


// Common init
$(window).on("load", function () {

    // Get more lastest posts in home
    $('#btnLastestMore').click(function () {
        var me = $(this);
        var postUrl = me.data('url');
        var loadMoreToken = me.attr('data-token');
        me.hide();
        $('#imgMoreLoading').removeClass('hidden');
        $.get(postUrl, {'loadMoreToken': loadMoreToken}, function (responeData) {
            if (responeData && responeData.success == 1) {
                $('#pnlPostList').append(responeData.data);
                if (responeData.loadMoreToken != null) {
                    $('#imgMoreLoading').addClass('hidden');
                    me.show();
                    me.attr('data-token', responeData.loadMoreToken);
                    //console.log(responeData.loadMoreToken);
                } else {
                    $('#imgMoreLoading').remove();
                    return;
                }
            } else {
                $('#imgMoreLoading').addClass('hidden');
                me.show();
            }
        });
    });

    // click show menu mobile
    $('#event-menu').on('click', function () {
        var me = $(this);
        if (me.hasClass('active')) {
            me.removeClass('active');
            $('.menu-mobile').hide();
            me.children('.icon-menu').show();
            me.children('.icon-cancel').hide();

            $('html, body').css({
                overflow: 'auto',
                height: 'auto'
            });
        } else {
            me.addClass('active');
            $('.menu-mobile').show();
            me.children('.icon-menu').hide();
            me.children('.icon-cancel').show();

            $('#event-search').removeClass('active');
            $('.search-mobile').slideUp();
            $('#event-search').children('.icon-search').show();
            $('#event-search').children('.icon-cancel').hide();

            $('html, body').css({
                overflow: 'hidden',
                height: '100%'
            });
        }
    });

    // click show search mobile
    $('#event-search').on('click', function () {
        $('html, body').css({
            overflow: 'auto',
            height: 'auto'
        });

        var me = $(this);
        if (me.hasClass('active')) {
            me.removeClass('active');
            $('.search-mobile').slideUp();
            me.children('.icon-search').show();
            me.children('.icon-cancel').hide();
        } else {
            me.addClass('active');
            $('.search-mobile').slideDown();
            me.children('.icon-search').hide();
            me.children('.icon-cancel').show();

            $('#event-menu').removeClass('active');
            $('.menu-mobile').hide();
            $('#event-menu').children('.icon-menu').show();
            $('#event-menu').children('.icon-cancel').hide();
        }
    });

    var $window = $(window);
    var check_resize = 0;

    // Optimalisation: Store the references outside the event handler:
    function checkWidth() {
        var windowsize = $window.width();
        if (windowsize <= 991) {
            $('body').addClass('body-mobile');
            $('.menu-mobile').hide();
            if (check_resize) {
                return false;
            } else {
                check_resize = 1;
                $('ul.left-nav li i.icon-down-open-big').on('click', function () {
                    var me = $(this);
                    var pme = $(this).parent();
                    if (me.hasClass('active')) {
                        me.removeClass('active');
                        pme.children('.menu-sub-mobile').slideUp();
                    } else {
                        $('ul.left-nav li i.icon-down-open-big').removeClass('active');
                        $('.menu-sub-mobile').slideUp();
                        me.addClass('active');
                        pme.children('.menu-sub-mobile').slideDown();
                    }
                });
                // fix top
                fixNav();
            }
        } else {
            check_resize = 1;
            $('ul.left-nav li i.icon-down-open-big').removeClass('active');
            $('.menu-sub-mobile').slideUp();

            // fix top default
            $('body').removeClass('body-mobile');
            document.getElementById("nav-mobile").style.top = "0";

            $('.menu-mobile').show();
        }
    }

    // fix nav top
    function fixNav() {
        var prevScrollpos = window.pageYOffset;
        window.onscroll = function () {
            var currentScrollPos = window.pageYOffset;
            if (prevScrollpos > currentScrollPos) {
                document.getElementById("nav-mobile").style.top = "0";
            } else if (currentScrollPos > 200) {
                document.getElementById("nav-mobile").style.top = "-50px";
            } else {
                document.getElementById("nav-mobile").style.top = "0";
            }
            prevScrollpos = currentScrollPos;
        }
    }

    // Execute on load
    checkWidth();
    // Bind event listener
    $(window).resize(checkWidth);
});

