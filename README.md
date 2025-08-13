# ps-checkout

## Description

Payment integration module for prestashop 

## Technical description
 
### Tests

#### Linting

## Installation

1. Clone repository to local environment
2. Copy .env.dist -> .env
3. Configure .env file to your environment settings. Change `INSTALL_XDEBUG=1` if you want to install Xdebug
4. Copy MODULE_VERSION/.env.dist -> MODULE_VERSION/.env
5. Run Makefile command in terminal `make build module_version=ps8`
6. Run Makefile command in terminal `make run module_version=ps8`
7. Website is accessible at `http://localhost:8991`
8. `http://localhost:8991/admin1` - admin panel

Use default PrestaShop credentials to login:
    `demo@prestashop.com`
    `prestashop_demo`