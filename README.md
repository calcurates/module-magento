### Calcurates magento module

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
