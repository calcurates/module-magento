### Calcurates magento module

#### Requirements
- Magento >= 2.3
- PHP >= 7.1

#### Manual installation
- Download [latest release](https://github.com/calcurates/module-magento/releases/latest)
- Unpack archive to `app/code/Calcurates/ModuleMagento`
- Execute
```bash
cd path_to_magento
sudo -u magento_user php bin/magento setup:upgrade
sudo -u magento_user php bin/magento cache:flush
```

#### Composer installation
```bash
cd path_to_magento
composer require calcurates/module-magento
sudo -u magento_user php bin/magento setup:upgrade
sudo -u magento_user php bin/magento cache:flush
```

#### Upgrades
Magento provides options to the `setup:install` and `setup:upgrade` commands that enable safe installations and rollbacks:
- `--safe-mode=1` - Creates a data dump during the installation or upgrade process
- `--data-restore=1` - (Used with the `setup:upgrade` command only) Performs a rollback. Before you rollback, you must first check out code to the previous version of Magento. Then run `setup:upgrade --data-restore=1`

#### Configuration instructions
1. Login into Admin
1. Go to "Stores -> Configuration -> Sales -> Shipping Methods"
1. In "Calcurates [by Calcurates]":
    - "Calcurates API Token" - token obtained in Calcurates platform
    -  "Magento API Token" - token should be generated and added into Calcurates platform
1. Configure Magento API Integration token as shown in - [Magento Integration Token Instructions](https://devdocs.magento.com/guides/v2.3/get-started/authentication/gs-authentication-token.html)


#### API Permissions
- Calcurates API resource
- Stores → Settings → All Stores (the codes are `Calcurates_ModuleMagento::api` and `Magento_Backend::store`)
