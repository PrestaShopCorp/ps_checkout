# PrestaShop Checkout

![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/prestashopcorp/ps_checkout)
[![GitHub license](https://img.shields.io/github/license/PrestaShopCorp/ps_checkout)](https://github.com/PrestaShopCorp/ps_checkout/LICENSE.md)

## About

PrestaShop official payment module in partnership with PayPal.

## Reporting issues

In order to contact the team, please use the [link][contact-us] available in the
back-office once logged to your PrestaShop account.

## Direct download

If you want to get a zip ready to install on your shop. You can directly download it by clicking [here][direct-download].

### Branches

There are 4 main branches on the repository:
- `prestashop/main` is the branch for PrestaShop Checkout v5 for PrestaShop v1.7, v8 and v9
- `prestashop/9.x` is the branch for PrestaShop Checkout v4 for PrestaShop v9.x
- `prestashop/8.x` is the branch for PrestaShop Checkout v4 for PrestaShop v8.x
- `prestashop/1.7.x` is the branch for PrestaShop Checkout v4 for PrestaShop v1.7.x
- ~~`prestashop/1.6.1.x` is the branch for PrestaShop Checkout for PrestaShop v1.6.1.x~~ (deprecated)

Contributors **must** follow the following rules:

* Use the `main` branch
* Do not update the module's version number.
* Follow [the coding standards][1].

## Development

### Requirements

- PHP
- Composer
- Docker
- Docker Compose
- Make (GNU Make)

#### PHP Matrix versions

- [PrestaShop 1.7.x](https://devdocs.prestashop-project.org/1.7/basics/installation/system-requirements/#php-compatibility-chart)

| PrestaShop Versions | Symfony components version | PHP Versions  | Recommended PHP Version |
|---------------------|----------------------------|---------------|-------------------------|
| `>=1.7.0 <=1.7.3`   | Symfony `2.8` ⚠️           | `>=5.4 <=7.1` | `7.1`                   |
| `1.7.4`             | Symfony `3.4`              | `>=5.6 <=7.1` | `7.1`                   |
| `>=1.7.5 <=1.7.6`   | Symfony `3.4`              | `>=5.6 <=7.2` | `7.2`                   |
| `>=1.7.7`           | Symfony `3.4`              | `>=7.1 <=7.3` | `7.3`                   |
| `>=1.7.8`           | Symfony `3.4`              | `>=7.1 <=7.4` | `7.4`                   |

- [PrestaShop 8.x](https://devdocs.prestashop-project.org/8/basics/installation/system-requirements/#php-compatibility-chart)

| PrestaShop Versions | Symfony components version | PHP Versions  | Recommended PHP Version |
|---------------------|----------------------------|---------------|-------------------------|
| `>=8.0 <=8.2`       | Symfony `4.4`              | `>=7.2 <=8.1` | `8.1`                   |

- [PrestaShop 9.x](https://devdocs.prestashop-project.org/9/basics/installation/system-requirements/#php-compatibility-chart)

| PrestaShop Versions | Symfony components version | PHP Versions  | Recommended PHP Version |
|---------------------|----------------------------|---------------|-------------------------|
| `>=9.0`             | Symfony `6.4`              | `>=8.1 <=8.4` | `8.4`                   |

#### PHP Versions older than 8.x

To install PHP versions older than 8.x, please refer to these specific repositories:

- macOS using Homebrew: https://github.com/shivammathur/homebrew-php
- Ubuntu: https://launchpad.net/~ondrej/+archive/ubuntu/php

### Build

1. Clone repository to local environment
2. Copy .env.dist -> .env
3. Configure the .env file to your environment settings.
4. Copy MODULE_VERSION/.env.dist -> MODULE_VERSION/.env
5. Copy the docker-compose.local.yml.dist -> docker-compose.local.yml
6. Uncomment the services in the docker-compose.local.yml file to your needs.
7. Run Makefile command in terminal `make build`
8. Run Makefile command in terminal `make run`
9. Website is accessible at `http://localhost:8991`
10. `http://localhost:8991/admin1` - admin panel

Use default PrestaShop credentials to login:
- `demo@prestashop.com`
- `prestashop_demo`

### Lint

Run `make lint` in terminal.

Run `make phpcompat-71` to check PHP 7.1 compatibility for shared packages and `ps17/` source files.

### Tests

#### Unit tests

Run `make unit-test` in terminal.

#### Integration tests

Run `make integration-test` in terminal.

## Contributing

PrestaShop modules are open source extensions to the PrestaShop e-commerce solution. Everyone is welcome and even encouraged to contribute with their own improvements.

Contributors wishing to edit a module's files should follow the following process:

1. Fork this project to your GitHub account.
2. Clone your fork to your local machine in the `/modules` directory of your PrestaShop installation.
3. Create a branch in your local clone of the module for your changes.
4. Change the files in your branch. Be sure to follow the [coding standards][1]
5. Push your changed branch to your fork in your GitHub account.
6. Create a pull request for your changes on the target branch of the module's project. Be sure to follow the [contribution guidelines][2] in your pull request. If you need help to make a pull request, read the [GitHub help page about creating pull requests][3].
7. Wait for one of the maintainers either to review the code

## License

This module is released under the [Academic Free License 3.0][AFL-3.0]

[contact-us]: https://help-center.prestashop.com/contact?psx=ps_checkout
[direct-download]: https://github.com/PrestaShopCorp/ps_checkout/releases
[1]: https://devdocs.prestashop-project.org/8/development/coding-standards/
[2]: https://devdocs.prestashop-project.org/8/contribute/contribution-guidelines/
[3]: https://help.github.com/articles/using-pull-requests
[AFL-3.0]: https://opensource.org/licenses/AFL-3.0
