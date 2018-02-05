<?php

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * class Server.
 *
 * @author Simon Vieille <simon@deblan.fr>
 */
class Server implements MessageComponentInterface
{
    /**
     * @var \SplObjectStorage
     */
    protected $clients;

    /**
     * @var \SplObjectStorage
     */
    protected $authenticatedClients;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if ($data === null) {
            return;
        }

        $type = $data['type'] ?? null;

        if ($type === 'pointer') {
            $x = $data['x'] ?? null;
            $y = $data['y'] ?? null;
            $click = $data['click'] ?? null;

            if ($x !== null && $y !== null) {
                $mouseLocations = shell_exec('xdotool getmouselocation');
                preg_match('/x:(\d+) y:(\d+) /', $mouseLocations, $matches);
                $mouseX = (int) ($matches[1] + $x * 2.5);
                $mouseY = (int) ($matches[2] + $y * 2.5);

                return shell_exec(sprintf('xdotool mousemove %s %s', $mouseX, $mouseY));
            } elseif ($click !== null) {
                if ($click === 'left') {
                    return shell_exec('xdotool click 1');
                } elseif ($click === 'dleft') {
                    // return shell_exec("xdotool click 1; xdotool click 1");
                } elseif ($click === 'middle') {
                    return shell_exec('xdotool click 2');
                } elseif ($click === 'right') {
                    return shell_exec('xdotool click 3');
                }
            }
        } elseif ($type === 'workspace') {
            $value = $data['value'] ?? null;

            if (!empty($value)) {
                return shell_exec(sprintf("i3-msg 'workspace \"%s\"'", $value));
            }
        } elseif ($type === 'volume') {
            $value = $data['value'] ?? null;

            if ($value === null) {
                return;
            }

            if ($value === 'up') {
                return shell_exec('amixer set Master 2%+');
            } elseif ($value === 'down') {
                return shell_exec('amixer set Master 2%-');
            } else {
                return shell_exec('amixer set Master '.((int) $value).'%');
            }
        } elseif ($type === 'media') {
            $value = $data['value'] ?? null;

            if ($value === 'playpause') {
                $cmd = 'PlayPause';
            } elseif ($value === 'next') {
                $cmd = 'Next';
            } elseif ($value === 'prev') {
                $cmd = 'Previous';
            }

            if (!empty($cmd)) {
                return shell_exec('dbus-send --print-reply --dest=org.mpris.MediaPlayer2.spotify /org/mpris/MediaPlayer2 org.mpris.MediaPlayer2.Player.'.$cmd);
            }
        } elseif ($type === 'keys') {
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

                return shell_exec(sprintf('xdotool key %s', escapeshellarg($value)));
            }
        } elseif ($type === 'key') {
            $value = $data['value'] ?? null;

            if (!empty($value)) {
                switch ($value) {
                    case 'up':
                    return shell_exec('xdotool key Up');
                    case 'down':
                        return shell_exec('xdotool key Down');
                    case 'left':
                        return shell_exec('xdotool key Left');
                    case 'right':
                        return shell_exec('xdotool key Right');
                    case 'tab':
                        return shell_exec('xdotool key Tab');
                    case 'backspace':
                        return shell_exec('xdotool key BackSpace');
                    case 'enter':
                        return shell_exec('xdotool key Return');
                    case 'space':
                        return shell_exec('xdotool key space');
                    case 'escape':
                        return shell_exec('xdotool key Escape');
                }

                return shell_exec(sprintf('xdotool type %s', escapeshellarg($value)));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }
}
