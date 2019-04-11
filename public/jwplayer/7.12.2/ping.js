/** JW Player plugin that pings playback events back to server. **/
window.jwplayer().registerPlugin('ping', '6.12', function(_player, _options, _div) {

    /** No display elements, but function is required. **/
    _div.style.display = 'none';
    this.resize = function() {};

    /** Last time tick **/
    var _lastTime = -1;
    /** Start time **/
    var _startTime = -1;

    /** Initialize the plugin on player ready. **/
    _player.onReady(function() {
        _player.onPlay(_stateHandler);
    });

    /** If moving from idle, the item is started. **/
    function _stateHandler(event) {
        if(event.oldstate === window.jwplayer.events.state.IDLE) {
            _sendPing('item');
            _startTime = -1;
            _lastTime = -1;
        }
    }

    /** Wrap up the url generation and do the ping. **/
    function _sendPing(event) {
        var item = _player.getPlaylistItem();
        var mediaid = item.mediaid;
        if(!mediaid) {
        	return;
        }
        var file = item.file;
        if(!file) {
            file = item.sources[0].file;
        }
        var query = '?event='+event;
        query += '&file='+encodeURIComponent(file);
        query += '&mediaid='+encodeURIComponent(mediaid);
        query += '&r='+Math.random();
        if(_options.pixel) {
            var image = new Image();
            image.src = _options.pixel + query;
        }
    }
});
