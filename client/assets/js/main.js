$(function() {
    var ws = new WebSocket('ws://' + window.location.hostname + ':14598');
    var $pointer = $('#pointer');
    var mouseInitPosX = null;
    var mouseInitPosY = null;
    var mousePosX = null;
    var mousePosY = null;

    $('.select2').select2();

    $('#nav a').click(function(e) {
        e.preventDefault();
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

    $('#shortcut-send').click(function() {
        var keys = $('#shortcut-keys').val();
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

    $pointer.on('touchstart', function(e) {
        if (e.targetTouches.length == 1) {
            var touch = e.targetTouches[0];
            mouseInitPosX = touch.pageX;
            mouseInitPosY = touch.pageY;
        }
    });

    $pointer.on('touchmove', function(e) {
        if (e.changedTouches.length == 1) {
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
    });
});
