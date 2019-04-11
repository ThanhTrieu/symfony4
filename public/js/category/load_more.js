$(document).ready(function () {
    $('#bt-load-more').on('click', function() {
        var id = $('.list-news-2').attr('data-id');
        var link_start = $(this).attr("data-url");
        if (!id || !link_start)
            return false;

        var page = $(this).attr("data-page"); //get page number from link
        $(this).hide();
        var data = {"id":id, "page": page};
        $.ajax({
            type : "POST",
            url : link_start,
            dataType : "html",
            data : data,
            success : function(data) {
                if (data) {
                    $('.list-news-2').append(data);
                    $('.btn-all').show();
                    page++;
                    $('.btn-all').attr('data-page',page);
                }
            },
        });
    });
});
