var searchPostPublish = function () {
    function search(keyword) {
        var url = "/search?q="+keyword;
        if(keyword.length == 0){
            alert("Enter your keywords");
        } else {
            window.location.href = url;
        }
    }
    return {
        search: search
    };
}();

$(function () {
    $('#btnSearch').click(function () {
        var keyword = $('#keywordSearch').val().trim();
        if(keyword.length < 100){
            searchPostPublish.search(keyword);
        } else {
            alert('Keywords less than 100 characters');
        }
        return false;
    });

    $('#btnSearchMobile').click(function () {
        var keyword = $('#keywordSearchMobile').val().trim();
        if(keyword.length < 100){
            searchPostPublish.search(keyword);
        } else {
            alert('Keywords less than 100 characters');
        }
        return false;
    });

    $('#keywordSearch,#keywordSearchMobile').keyup(function(e){
        if(e.keyCode == 13)
        {
            var keyword = $(this).val().trim();
            if(keyword.length < 100){
                searchPostPublish.search(keyword);
            } else {
                alert('Keywords less than 100 characters');
            }
            return false;
        }
    });
});