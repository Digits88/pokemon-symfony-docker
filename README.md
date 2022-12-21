# Pokemon Project - Symfony 6 & Docker

**A Pokemon Project built with [Symfony 6](https://symfony.com) web framework, 
virtualized with [Docker](https://www.docker.com/)-based installer 
and runtime with full [HTTP/2](https://symfony.com/doc/current/weblink.html), 
[HTTP/3](https://symfony.com/doc/current/weblink.html) 
and [HTTPS](https://symfony.com/doc/current/http_client.html#https-certificates) support.**

**`Scenario: A new pokemon tournament is coming soon and a strong team must be created to try to excel at the competition! 
On this project by using the Pokemon API at https://pokeapi.co/ we can create, list or edit teams.`**

## Getting Started

1. Install [Docker Compose](https://docs.docker.com/compose/install/) (v2.10+), or proceed to second step if already installed.
2. Clone this repository 
3. Enter inside the folder.
4. Run `docker-compose build --no-cache` to build fresh images
5. Run `docker-compose up -d` to run the containers in backround or `docker-compose up` for the logs to be displayed in the current shell.
6. Run `symfony console doctrine:migrations:migrate` on the root folder of the project to create the database tables.
7. Open `https://localhost` in your favorite web browser and accept the auto-generated TLS certificate.

* Helper single cli command instead of point 3 & 4: `docker compose up --build -d --force-recreate`
* Run `docker compose down --remove-orphans` to stop & remove the Docker containers.

* Helper terminal command to create database schema: `symfony console doctrine:schema:create`
* Helper terminal command to create database tables: `symfony console doctrine:migrations:migrate`
* Helper terminal command to generate new migrations from entity: `symfony console doctrine:migrations:diff`


## Features

* Production, development and CI ready
* [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex

[//]: # (* Automatic HTTPS &#40;in dev and in prod!&#41;)

[//]: # (* HTTP/2, HTTP/3 and [Preload]&#40;https://symfony.com/doc/current/web_link.html&#41; support)

[//]: # (* Built-in [Mercure]&#40;https://symfony.com/doc/current/mercure.html&#41; hub)

[//]: # (* [Vulcain]&#40;https://vulcain.rocks&#41; support)
* Native [XDebug](docs/xdebug.md) integration

[//]: # (* 5 services &#40;PHP-FPM, Caddy Webserver, Mysql Db, Redis Cache, Mailcatcher&#41;)
* 5 services (PHP-FPM, Nginx Webserver, Mysql Db, Redis Cache, Mailcatcher)
* Easy-readable configuration

**Wish you enjoy and excel at the competition!**

## Docs

1. [Build options](docs/build.md)
2. [Support for extra services](docs/extra-services.md)
3. [Debugging with Xdebug](docs/xdebug.md)
4. [TLS Certificates](docs/tls.md)
5. [Using a Makefile](docs/makefile.md)
6. [Troubleshooting](docs/troubleshooting.md)


## License

MIT License.

## Credits

Created by [Lefter Zaka](https://www.linkedin.com/in/lefter-zaka/).
