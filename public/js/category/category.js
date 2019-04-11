$(function () {
    $('.short-info').each(function() {
        var el = $(this);
        var divHeight = el.outerHeight();
        var lineHeight = parseInt(el.css('line-height').replace('px', ''));
        var lines = Math.ceil(divHeight / lineHeight);
        var str = $.trim($(el).text());
        if(lines > 3){
            let textData = str.substring(0,130)+"...";
            $(el).html(textData);
        }
    });
});