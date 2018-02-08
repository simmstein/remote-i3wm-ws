Remote i3-wm WS
===============

This project is a POC for managing a GNU/Linux desktop (with i3-wm). It implements an interface for running:

* `amixer`
* `i3-msg`
* `xdotool`
* `playerctl`

It allows you to:

* change the i3-wm workspaces
* manage volume and spotify
* send text and shortcuts
* move the pointer, scroll and click

…by using a web interface with your phone.

![](https://upload.deblan.org/u/2018-02/5a7b3064.png)
![](https://upload.deblan.org/u/2018-02/5a7b2217.png)
![](https://upload.deblan.org/u/2018-02/5a7b221a.png)
![](https://upload.deblan.org/u/2018-02/5a7b221d.png)

Installation
------------

**Requirements**
* PHP7
* composer

```
$ git clone https://gitnet.fr/deblan/remote-i3wm-ws.git
$ cd remote-i3wm-ws/
$ cd client && composer install && cd ..
$ cd server && composer install && cd ..
$ php -S 0.0.0.0:15000 -t client/&
$ php server/server&
```

The server can be run with with verbose:

* `-v` or `--verbose` to show server messages
* `--vv` to show message handlers messages

Open `http://your.local.ip:15000` and enjoy!
