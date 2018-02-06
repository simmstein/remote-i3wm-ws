Remote i3-wm WS
===============

This project is a POC for managing a GNU/Linux desktop (with i3-wm). It implements an interface for running:

* `amixer`
* `i3-msg`
* `xdotool`
* `dbus-send`

It allows you to:

* change the i3-wm workspaces
* manage volume and spotify
* send text and shortcuts
* move the pointer, scroll and click

…by using a web interface with your phone.

![](https://upload.deblan.org/u/2018-02/5a79780f.png)
![](https://upload.deblan.org/u/2018-02/5a797815.png)
![](https://upload.deblan.org/u/2018-02/5a798866.png)
![](https://upload.deblan.org/u/2018-02/5a79781e.png)

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

Open `http://your.local.ip:15000` and enjoy!
