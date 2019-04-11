var PopupGallery = {
    url_dataPopup: $('#url_dataPopup').val(),
    init: function (loadUserData) {
        this._InitLoad(); // ready load
        this._InitLoadData(); // ready load data
    },

    // ready load
    _InitLoad: function () {
        var _self = this;

        // click popup
        $(document).on('click', '.bt-popup', function (e) {
            var id = $(this).data('id');
            var link = $(this).data('link');
            var url_post = $(this).data('url');
            var title = $(this).children().children('h3').children('a.name-pro').text();
            if (!id)
                return false;

            _self._getDataPhotos(id, title,link , url_post);
        });

        // click close popup
        $('.close').on('click', function () {
            $('#myModal').hide();
            history.pushState({}, '', '/photo');
        });
    },

    // ready load get data
    _InitLoadData: function () {
        var _self = this;
        var gallery_id = $('#gallery_id').val();
        var gallery_title = $('#gallery_title').val() ? $('#gallery_title').val() : '';
        var gallery_url = $('#gallery_url').val() ? $('#gallery_url').val() : '';
        var url_post = $('#url_post').val() ? $('#url_post').val() : '';
        if (!gallery_id || gallery_id == 0)
            return false;

        _self._getDataPhotos(gallery_id, gallery_title,gallery_url,url_post);
    },

    // get data Photos
    _getDataPhotos: function (id, title,href,url_post) {
        var _self = this;

        $('#modal-content').html('');
        $('#imgMoreLoading2').removeClass('hidden');
        $('#myModal').show();
        var form_data = $('#modal-content');

        $.post(_self.url_dataPopup, {
            id: id,
        }, function (data) {
            if (data.success == 1) {
                form_data.html(data.html);
                $('.text-post').text(title);
                $('.view-more-post').attr('href',url_post);
                $('.view-more-post').attr('title',title);

                _self._callLibrarySlide();
                $('#imgMoreLoading2').addClass('hidden');
                history.pushState({}, '', href);
            } else {
                $('#myModal').hide();
            }
        }, 'json');
        return false;
    },

    // call Library Slide
    _callLibrarySlide: function () {
        var _self = this;
        var slider = $('#image-gallery').lightSlider({
            gallery: true,
            item: 1,
            thumbItem: 5,
            thumbMargin: 10,
            speed: 500,
            auto: false,
            controls: false,
            loop: true,
            adaptiveHeight:true,
            onBeforeSlide: function (el) {
                $('#current').text(el.getCurrentSlideCount());
            },
            onSliderLoad: function () {
                $('#image-gallery').removeClass('cS-hidden');
            }
        });

        $('.lSSlideWrapper').append($('.count-hidden').html());

        $('#total').text(slider.getTotalSlideCount());

        $('#goToPrevSlide').click(function () {
            slider.goToPrevSlide();
            //_self._pushUrl();
        });
        $('#goToNextSlide').click(function () {
            slider.goToNextSlide();
            //_self._pushUrl();
        });
    },

    // push Url detail photo
    _pushUrl: function () {
        $("li.item-photo").each(function (index) {
            if ($(this).hasClass('active')) {
                var href = $(this).data('link');
                if (href)
                    history.pushState({}, '', href);
            }
        });
    },
};

$(function () {
    PopupGallery.init();
});