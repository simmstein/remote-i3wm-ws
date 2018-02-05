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
     * @var array
     */
    protected $messageHandlers = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    /**
     * Add a message handle.
     *
     * @param string   $type
     * @param callable $callable
     *
     * @return Server;
     */
    public function addMessageHandler($type, callable $callable)
    {
        if (!isset($this->messageHandlers[$type])) {
            $this->messageHandlers[$type] = [];
        }

        $this->messageHandlers[$type][] = $callable;

        return $this;
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

        if ($type === null) {
            return;
        }

        $handlers = $this->messageHandlers[$type] ?? [];

        foreach ($handlers as $handler) {
            $handler($from, $data);
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
