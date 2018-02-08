var ws;
var $pointer, $scroller, $response;
var scrollLastTimestamp, scrollLastValue;
var mousePosX, mousePosY, mouseInitPosX, mouseInitPosY;

var createWebSocketConnection = function() {
    ws = new WebSocket('ws://' + window.location.hostname + ':14598');

    ws.onopen = function(event) {
        $('#disconneced').fadeOut();
    }

    ws.onclose = function(event) {
        $('#disconneced').fadeIn();

        window.setTimeout(createWebSocketConnection, 5000);
    }

    ws.onmessage = function(event) {
        var data = JSON.parse(event.data);

        if (data.type === 'response') {
            $response.text(data.value);
            $response.fadeIn();

            window.setTimeout(function() {
                $response.fadeOut();
            }, 2500);
        }
    }
}

var navigationClickHandler = function(e) {
    $('.pane').hide();

    var target = $(this).attr('href');
    $(target).show();

    $('#nav a').removeClass('active');
    $(this).addClass('active');
}

var buttonClickHandler = function(e) {
    var msg = $(this).attr('data-msg');
    ws.send(msg);
}

var shortcutClearClickHandler = function(e) {
    $('#shortcut-key').val('');
    $('#shortcuts_special_keys input:checked').each(function() {
        $(this).prop('checked', false).trigger('change');
    });
}

var shortcutSendClickHandler = function(e) {
    var keys = [];

    $('#shortcuts_special_keys input:checked').each(function() {
        keys.push($(this).val());
    });

    var key = $('#shortcut-key').val();

    if (keys.length) {
        if (key) {
            keys.push(key);
        }

        var msg = '{"type":"keys","value": "' + (keys.join(',').replace('"', '\\"')) + '"}';
        ws.send(msg);
    }
}

var textClearClickHandler = function(e) {
    $('#text').val('');
}

var textSendClickHandler = function(e) {
    var keys = $('#text').val();

    if (keys.length) {
        var msg = '{"type":"text","value": "' + (keys.replace('"', '\\"')) + '"}';
        ws.send(msg);
    }
}

var textKeyUpHandler = function(e) {
    var keys = $('#text').val();

    if (e.keyCode === 13) {
        var msg = '{"type":"text","value": "' + (keys.replace('"', '\\"')) + '"}';
        ws.send(msg);
    }
}

var liveTextKeyUpHandler = function(e) {
    var value = $(this).val();
    var live = false;

    if (e.keyCode === 8) {
        var msg = '{"type":"key","value": "backspace"}';
        ws.send(msg);
    } else if (e.keyCode === 13) {
        var msg = '{"type":"key","value": "enter"}';
        ws.send(msg);
    } else if (value.length) {
        if (value === ' ') {
            var msg = '{"type":"key","value": "space"}';
            ws.send(msg);
        } else {
            var msg = '{"type":"text","value": "' + (value.replace('"', '\\"')) + '"}';
            ws.send(msg);
        }

        $(this).val('');
    }
}

var shortcutsSpecialKeysOnChangeHandler = function(e) {
    $('#shortcuts_special_keys input:checked').each(function() {
        $(this).parent().addClass('btn-primary').removeClass('btn-secondary');
    })

    $('#shortcuts_special_keys input:not(:checked)').each(function() {
        $(this).parent().addClass('btn-secondary').removeClass('btn-primary');
    })
}

var pointerClickHandler = function(e) {
    var msg = '{"type":"pointer","click":"left"}';
    ws.send(msg);
}

var scrollerTouchStartHandler = function(e) {
    var touch = e.targetTouches[0];
    mouseInitPosY = touch.pageY;
}

var scrollerTouchMoveHandler = function(e) {
    var touch = e.changedTouches[0];
    var value = ((touch.pageY - mouseInitPosY > 0) ? 'down' : 'up');
    var now = new Date().getTime();

    if (touch.pageY === mouseInitPosY || value === scrollLastValue && scrollLastTimestamp !== null && now - scrollLastTimestamp < 200) {
        return;
    }

    scrollLastTimestamp = now;
    scrollLastValue = value;

    var msg = '{"type":"scroll","value": "' + value + '"}';

    mouseInitPosY = touch.pageY;
    ws.send(msg);
}

var pointerTouchStartHandler = function(e) {
    var touch = e.targetTouches[0];
    mouseInitPosX = touch.pageX;
    mouseInitPosY = touch.pageY;
}

var pointerTouchMoveHandler = function(e) {
    if (e.changedTouches.length === 2) {
        return scrollerTouchMoveHandler(e);
    }

    var touch = e.changedTouches[0];
    mousePosX = touch.pageX;
    mousePosY = touch.pageY;

    var newX = mousePosX - mouseInitPosX;
    var newY = mousePosY - mouseInitPosY;

    mouseInitPosX = mousePosX;
    mouseInitPosY = mousePosY;

    var msg = '{"type":"pointer","x": "' + newX + '","y": "' + newY + '"}';

    ws.send(msg);
}

var documentHashHandler = function() {
    var hash = window.location.hash;

    if (hash) {
        $(hash).show();
        $('a[href="' + hash + '"]').addClass('active');
    } else {
        $('#pane-keyboard').show();
        $('#nav a').first().addClass('active');
    }
}

var addListeners = function() {
    $('#nav a').click(navigationClickHandler);
    $('button[data-msg]').click(buttonClickHandler);

    $('#shortcut-clear').click(shortcutClearClickHandler);
    $('#shortcuts_special_keys input').change(shortcutsSpecialKeysOnChangeHandler);
    $('#shortcut-send').click(shortcutSendClickHandler);

    $('#text-clear').click(textClearClickHandler);
    $('#text-send').click(textSendClickHandler);
    $('#text').on('keyup', textKeyUpHandler);
    $('#live-text').on('keyup', liveTextKeyUpHandler);

    $scroller
        .on('touchstart', scrollerTouchStartHandler)
        .on('touchmove', scrollerTouchMoveHandler);

    $pointer
        .on('click', pointerClickHandler)
        .on('touchstart', pointerTouchStartHandler)
        .on('touchmove', pointerTouchMoveHandler);
}

var bootstrap = function() {
    documentHashHandler();
    shortcutsSpecialKeysOnChangeHandler();
    createWebSocketConnection();
    addListeners();
}

$(function() {
    $pointer = $('#pointer');
    $scroller = $('#scrollbar');
    $response = $('#response');

    bootstrap();
});
