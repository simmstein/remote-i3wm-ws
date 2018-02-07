$(function() {
    var ws = new WebSocket('ws://' + window.location.hostname + ':14598');
    var $pointer = $('#pointer');
    var $scroller = $('#scrollbar');
    var mouseInitPosX = null;
    var mouseInitPosY = null;
    var mousePosX = null;
    var mousePosY = null;
    var scrollLastTimestamp = null;
    var scrollLastValue = null;

    var hash = window.location.hash;

    if (hash) {
        $(hash).show();
        $('a[href="' + hash + '"]').addClass('active');
    } else {
        $('#pane-keyboard').show();
        $('#nav a').first().addClass('active');
    }

    $('#nav a').click(function(e) {
        $('.pane').hide();

        var target = $(this).attr('href');
        $(target).show();

        $('#nav a').removeClass('active');
        $(this).addClass('active');
    });

    ws.onopen = function(event) {
        $('#disconneced').hide();
    }

    ws.onclose = function(event) {
        $('#disconneced').show();
    }

    ws.onmessage = function(event) {}

    $('button[data-msg]').click(function() {
        var msg = $(this).attr('data-msg');
        ws.send(msg);
    });

    $('#shortcut-clear').click(function() {
        $('#shortcut-keys').val(null).trigger('change');
        $('#shortcut-key').val('');
    });

    var shortcutsSpecialKeysOnChange = function() {
        $('#shortcuts_special_keys input:checked').each(function() {
            $(this).parent().addClass('btn-primary').removeClass('btn-secondary');
        })

        $('#shortcuts_special_keys input:not(:checked)').each(function() {
            $(this).parent().addClass('btn-secondary').removeClass('btn-primary');
        })
    }

    $('#shortcuts_special_keys input').change(shortcutsSpecialKeysOnChange);
    shortcutsSpecialKeysOnChange();

    $('#shortcut-send').click(function() {
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
    });

    $('#text-clear').click(function() {
        $('#text').val('');
    });

    $pointer.on('click', function(e) {
        var msg = '{"type":"pointer","click":"left"}';
        ws.send(msg);
    });

    $('#text-send').click(function() {
        var keys = $('#text').val();

        if (keys.length) {
            var msg = '{"type":"text","value": "' + (keys.replace('"', '\\"')) + '"}';
            ws.send(msg);
        }
    });

    $('#text').on('keyup', function(e) {
        var keys = $('#text').val();

        if (e.keyCode === 13) {
            var msg = '{"type":"text","value": "' + (keys.replace('"', '\\"')) + '"}';
            ws.send(msg);
        }
    });


    $('#live-text').on('keyup', function(e) {
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
    });

    $scroller.on('touchstart', function(e) {
        var touch = e.targetTouches[0];
        mouseInitPosY = touch.pageY;
    });

    $scroller.on('touchmove', function(e) {
        var touch = e.changedTouches[0];
        var value = ((touch.pageY - mouseInitPosY > 0) ? 'down' : 'up');
        var now = new Date().getTime();

        if (value === scrollLastValue && scrollLastTimestamp !== null && now - scrollLastTimestamp < 200) {
            return;
        }

        scrollLastTimestamp = now;
        scrollLastValue = value;

        var msg = '{"type":"scroll","value": "' + value + '"}';

        mouseInitPosY = touch.pageY;
        ws.send(msg);
    });

    $pointer.on('touchstart', function(e) {
        var touch = e.targetTouches[0];
        mouseInitPosX = touch.pageX;
        mouseInitPosY = touch.pageY;
    });

    $pointer.on('touchmove', function(e) {
        var touch = e.changedTouches[0];
        mousePosX = touch.pageX;
        mousePosY = touch.pageY;

        var newX = mousePosX - mouseInitPosX;
        var newY = mousePosY - mouseInitPosY;

        mouseInitPosX = mousePosX;
        mouseInitPosY = mousePosY;

        var msg = '{"type":"pointer","x": "' + newX + '","y": "' + newY + '"}';

        ws.send(msg);
    });
});
