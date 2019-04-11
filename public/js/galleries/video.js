$(function () {
    var firstVideoAvatar = document.getElementById('avatarUrlVideos');
    var playerInstance = jwplayer("mainPlayVideo");
    if(firstVideoAvatar) {
        var imgAvatar = firstVideoAvatar.value;
        initVideos(false,imgAvatar);
        fixCssVideo();
        changeVideoByClick();
    }

    function initVideos(type, img) {
        playerInstance.setup({
            file: getUrlVideo(),
            image: (img) ? img : null,
            width: "100%",
            aspectratio: "16:9",
            autostart: (type) ? true : false,
            mute: false,
        });
    }
    function getUrlVideo() {
        var url = $('#hddUrlVideos').val().trim();
        var pos = url.indexOf('?');
        var paramsVideos = (pos == -1) ? "?showinfo=0&iv_load_policy=3&modestbranding=1&nologo=1&autoplay=0&controls=0&showtitle=0&cc_load_policy=1&loop=1" : "&showinfo=0&iv_load_policy=3&modestbranding=1&nologo=1&autoplay=0&controls=0&showtitle=0&cc_load_policy=1&loop=1";
        var fullUrl = url + paramsVideos;
        return fullUrl;
    }
    function fixCssVideo() {
        $('#mainPlayVideo').css({width: '100%'});
    }
    function changeVideoByClick() {
        $('.iframetrack').click(function () {
            var urlDetailLink = $(this).attr('data-url');
            var ulrVideo = $(this).attr('rel');
            var titleVideo = $(this).next().find('h3>a.name-pro').text() || $(this).text();
            var sapoVideos = $(this).next().find('p.sapoVideos').text() || $(this).parent().parent().find('p.sapoVideos').text();
            var distanceTop = $('div.title-menu').offset().top;
            // load video
            $('#hddUrlVideos').val(ulrVideo);
            initVideos(true);
            fixCssVideo();
            // change text content
            $('#firstTitle').text(titleVideo);
            $('#firstSapo').text(sapoVideos);
            $('html,body').animate({scrollTop: distanceTop}, 'slow');
            window.history.pushState(null,null, urlDetailLink);
            return false;
        });

        $('.iframetrackTitle').click(function () {
            var urlLinkDetail = $(this).find('a.name-pro').attr('rel');
            var ulrVideo = $(this).parent().prev().attr('rel');
            var titleVideo = $(this).find('a.name-pro').text();
            var sapoVideos = $(this).find('p.sapoVideos').text();
            var distanceTop = $('div.title-menu').offset().top;
            // load video
            $('#hddUrlVideos').val(ulrVideo);
            initVideos(true);
            fixCssVideo();
            // change text content
            $('#firstTitle').text(titleVideo);
            $('#firstSapo').text(sapoVideos);
            $('html,body').animate({scrollTop: distanceTop}, 'slow');
            window.history.pushState(null,null, urlLinkDetail);
            return false;
        });
    }
});
