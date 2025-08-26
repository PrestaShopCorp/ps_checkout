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
- `prestashop/1.6.1.x` is the branch for PrestaShop Checkout for PrestaShop v1.6.1.x (deprecated)

Contributors **must** follow the following rules:

* Use the `main` branch
* Do not update the module's version number.
* Follow [the coding standards][1].

### Build

1. Clone repository to local environment
2. Copy .env.dist -> .env
3. Configure .env file to your environment settings. Change `INSTALL_XDEBUG=1` if you want to install Xdebug
4. Copy MODULE_VERSION/.env.dist -> MODULE_VERSION/.env
5. Run Makefile command in terminal `make build module_version=ps8`
6. Run Makefile command in terminal `make run module_version=ps8`
7. Website is accessible at `http://localhost:8991`
8. `http://localhost:8991/admin1` - admin panel

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


Use default PrestaShop credentials to login:
    `demo@prestashop.com`
    `prestashop_demo`
