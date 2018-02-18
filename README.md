<p align="center">
    <img src="http://sargantanacode.es/uploads/sargantanacode-g.png" alt="SargantanaCode" />
</p>

<p align="center">
    <a href="https://travis-ci.org/sargantanacode/sargantanacode/">
        <img src="https://travis-ci.org/sargantanacode/sargantanacode.svg?branch=master" alt="Build Status"/>
    </a>
</p>

# About
This is the public sourcecode for [SargantanaCode](http://sargantanacode.es)'s project.
This application has been developed from scratch using the [Symfony](https://symfony.com/) PHP framework.

# Requirements
* PHP 7.1 or higher.
* MariaDB or MySQL (this app has not been tested with other DBMS, so we do not offer support for them).
* The [usual Symfony application requirements](https://symfony.com/doc/current/reference/requirements.html).

# Installation
First, you have to clone this repository:
```console
$ git clone https://github.com/sargantanacode/sargantanacode
$ cd sargantanacode
$ composer install --no-interaction
```
Second, you have to edit the settings of the app and fill in the required data:
```console
$ vim app/config/parameters.yml
```
Then it's time to create the database:
```console
$ php bin/console doctrine:database:create
$ php bin/console doctrine:schema:create
```
And once the database has been created, it's time to create the first user
with admin privileges:
```console
$ php bin/console app:create-admin
```

# Usage
**If you're in a test or dev environment**, Symfony no needs to config a
virtual host in your web server to access to this app, just use the built-in web server:
```console
$ php bin/console server:run
```
Now you can access to this app in your browser at http://localhost:8000.
You can stop the built-in server by pressing `Ctrl + C` while you're in the terminal.

**If you want to use a fully-featured web server** (like Nginx or Apache)
to run this app, configure it to point at the *web/* directory of the project.
For more details see [web server configuration info](https://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)
at Symfony's official page.

# Technologies used
* [Symfony](https://symfony.com/).
* [PHP](http://php.net/).
* [MariaDB](https://mariadb.org/).
* [JavaScript](https://developer.mozilla.org/es/docs/Web/JavaScript).
* [jQuery](https://jquery.com/).
* [Bootstrap](https://getbootstrap.com/).
* [Sass](http://sass-lang.com/).
* [FontAwesome](http://fontawesome.io/).
* [Slugify](https://github.com/cocur/slugify).
* [Parsedown](https://github.com/erusev/parsedown).
* [highlight.js](https://github.com/isagalaev/highlight.js).
* [OrnicarGravatarBundle](https://github.com/henrikbjorn/GravatarBundle).
* [DAMADoctrineTestBundle](https://github.com/dmaicher/doctrine-test-bundle).
* [php5-akismet](https://github.com/achingbrain/php5-akismet).
* [HautziSystemMailBundle](https://github.com/christoph-hautzinger/SystemMailBundle).

# Contributing
Read the [CONTRIBUTING.md](CONTRIBUTING.md) file for more information about to
contribute to the project. If you find a bug in the sourcecode we encourage you
to send an issue report; if you find a vulnerability on this source code,
please [send it to us privately](mailto:contact@sargantanacode.es).

# License
[GNU GPL v3](LICENSE.txt)

    Copyright (C) 2017 Javi Palacios <javi@fjp.es>

    This program is free software: you can redistribute it and/or modify it under
    the terms of the GNU General Public License as published by the Free Software
    Foundation, either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT
    ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
    FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with
    this program.  If not, see <http://www.gnu.org/licenses/>.
