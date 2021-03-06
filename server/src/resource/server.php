<?php

require __DIR__.'/../../vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\ConnectionInterface;

$options = getopt(
    'v',
    ['verbose', 'vv']
);

$serverOutput = new Output(isset($options['v']) || isset($options['verbose']));
$messageOutput = new Output(isset($options['vv']));
$server = new Server($serverOutput);
$shell = new Shell();

$server->addMessageHandler('pointer', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $x = $data['x'] ?? null;
    $y = $data['y'] ?? null;
    $click = $data['click'] ?? null;

    if ($x !== null && $y !== null) {
        $mouseLocations = $shell->exec('xdotool getmouselocation');
        preg_match('/x:(\d+) y:(\d+) /', $mouseLocations, $matches);
        $mouseX = (int) ($matches[1] + $x * 2.5);
        $mouseY = (int) ($matches[2] + $y * 2.5);

        $shell->exec('xdotool mousemove %s %s', $mouseX, $mouseY);
        $messageOutput->writeln('Pointer moved');
    } elseif ($click !== null) {
        if ($click === 'left') {
            $shell->exec('xdotool click 1');
            $messageOutput->writeln('Left click');
        } elseif ($click === 'middle') {
            $shell->exec('xdotool click 2');
            $messageOutput->writeln('Middle click');
        } elseif ($click === 'right') {
            $shell->exec('xdotool click 3');
            $messageOutput->writeln('Right click');
        }
    }
});

$server->addMessageHandler('scroll', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;

    if ($value === 'down') {
        $shell->exec('xdotool click 5 && xdotool click 5');
        $messageOutput->writeln('Scrolling down');
    } elseif ($value === 'up') {
        $shell->exec('xdotool click 4 && xdotool click 4');
        $messageOutput->writeln('Scrolling up');
    }
});

$server->addMessageHandler('workspace', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;

    if (!empty($value)) {
        $shell->exec("i3-msg 'workspace \"%s\"'", $value);
        $messageOutput->writeln('Workspace changed');
    }
});

$server->addMessageHandler('volume', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;

    if ($value === null) {
        return;
    }

    if ($value === 'up') {
        $shell->exec('amixer set Master 2%%+');
        $from->send(json_encode(['type' => 'response', 'value' => 'Volume up']));
        $messageOutput->writeln('Volume up');
    } elseif ($value === 'down') {
        $shell->exec('amixer set Master 2%%-');
        $from->send(json_encode(['type' => 'response', 'value' => 'Volume down']));
        $messageOutput->writeln('Volume down');
    } else {
        $shell->exec('amixer set Master %d%%', (int) $value);
        $from->send(json_encode(['type' => 'response', 'value' => sprintf('Volume set to %d%%', $value)]));
        $messageOutput->writeln(sprintf('Volume set to %d%%', $value));
    }
});

$server->addMessageHandler('media', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;

    if ($value === 'playpause') {
        $cmd = 'play-pause';
    } elseif ($value === 'next') {
        $cmd = 'next';
    } elseif ($value === 'prev') {
        $cmd = 'previous';
    }

    if (!empty($cmd)) {
        $shell->exec('playerctl -p spotify %s', $cmd);

        usleep(500000);
        $status = trim($shell->exec('playerctl -p spotify status'));

        if ($status === 'Playing') {
            $song = $shell->exec('playerctl -p spotify metadata xesam:title');
            $from->send(json_encode(['type' => 'response', 'value' => 'Playing: '.$song]));
            $messageOutput->writeln('Spotify playing');
        } else {
            $from->send(json_encode(['type' => 'response', 'value' => 'Paused']));
            $messageOutput->writeln('Spotify paused');
        }
    }
});

$server->addMessageHandler('keys', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;

    if (!empty($value)) {
        $keys = explode(',', $value);

        foreach ($keys as $k => $v) {
            if ($v === 'win') {
                $keys[$k] = 'super';
            } elseif ($v === 'ctrl') {
                $keys[$k] = 'Control_L';
            } elseif ($v === 'alt') {
                $keys[$k] = 'Alt_L';
            }
        }

        $value = implode('+', $keys);

        $shell->exec('xdotool key %s', escapeshellarg($value));
        $messageOutput->writeln('Key pressed');
    }
});

$server->addMessageHandler('key', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;
    $map = [
        'up' => 'Up',
        'down' => 'Down',
        'left' => 'Left',
        'right' => 'Right',
        'tab' => 'Tab',
        'backspace' => 'BackSpace',
        'enter' => 'Return',
        'space' => 'space',
        'escape' => 'Escape',
    ];

    if (!empty($value) && isset($map[$value])) {
        $shell->exec('xdotool key %s', $map[$value]);
        $messageOutput->writeln('Key pressed');
    }
});

$server->addMessageHandler('text', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $value = $data['value'] ?? null;

    if (trim($value) !== '') {
        $shell->exec('xdotool type %s', escapeshellarg($value));
        $messageOutput->writeln('Text wrote');
    }
});

$server->addMessageHandler('screenshot', function (ConnectionInterface $from, array $data) use ($shell, $messageOutput) {
    $tmpFilename = sprintf('%s/remote_i3wm_ws_screenshot.jpg', sys_get_temp_dir());
    $shell->exec('import -window root -quality 10 -display :0 %1$s && chmod 600 %1$s', $tmpFilename);

    if (file_exists($tmpFilename)) {
        $base64 = base64_encode(file_get_contents($tmpFilename));

        $from->send(json_encode([
            'type' => 'screenshot',
            'value' => $base64,
        ]));

        unlink($tmpFilename);
    }
});

$server->addMessageHandler('messages', function (ConnectionInterface $from, array $data) use ($server) {
    $value = $data['value'] ?? [];

    foreach ($value as $msg) {
        $server->onMessage($from, json_encode($msg));
    }
});

$webSocker = IoServer::factory(
    new HttpServer(new WsServer($server)),
    14598
);

$webSocker->run();
