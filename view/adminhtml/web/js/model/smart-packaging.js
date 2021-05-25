/**
 * @author Calcurates Team
 * @copyright Copyright © 2019 Calcurates (https://www.calcurates.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @package Calcurates_ModuleMagento
 */

define([
    'jquery',
    "Magento_Shipping/order/packaging",
    'mage/translate'
], function ($, packagingClass, $t) {
    'use strict';

    return {
        alreadyPacked: false,
        smartPackagingInProgress: false,
        packages: [],
        packaging: {},
        /**
         * @param {Packaging} packaging
         * @param {Object} packages
         */
        init: function (packaging, packages) {
            this.packaging = packaging;
            this.packages = packages;
            this.addMixins(packaging);
        },

        /**
         * @param {Packaging} packaging
         */
        addMixins: function (packaging) {
            var originalActionShowWindow = packaging.showWindow,
                originalProcessPackagePrepare = packaging.processPackagePrepare,
                self = this;

            packaging.showWindow = function () {
                originalActionShowWindow.apply(this);
                self.loadPackages();
            };

            packaging.processPackagePrepare = function (grid) {
                originalProcessPackagePrepare.apply(this, [grid]);
                self.waitingForPackItems(grid);
            }
        },

        waitingForPackItems: function (grid) {
            // hard trick for waiting showing buttons
            setTimeout(function () {
                this.packItems(grid);
            }.bind(this), 100);
        },

        loadPackages: function () {
            if (this.alreadyPacked) {
                return;
            }

            this.alreadyPacked = true;
            this.smartPackagingInProgress = true;

            this.packages.forEach(this.initPackage.bind(this));
        },

        /**
         *
         * @param {Object} packageItem
         * @param {Integer} index
         */
        initPackage: function (packageItem, index) {
            if (index > 0) {
                this.packaging.newPackage();
            }

            var packageBlockId = 'package_block_' + this.packaging.packageIncrement,
                packageBlock = $('#' + packageBlockId),
                containerType = packageBlock.find('select[name=package_container]');

            containerType.val(this.getPackageIdentifier(packageItem));
            this.packages[index].packageBlockId = packageBlockId;
            this.packaging.changeContainerType(containerType[0]);
            this.packaging.getItemsForPack(packageBlock.find("button[data-action='package-add-items']")[0]);

        },

        getPackageIdentifier: function (packageItem) {
            return packageItem.id || packageItem.code;
        },

        packItems: function (grid) {
            if (!this.smartPackagingInProgress) {
                return;
            }

            var packageBlock = $(grid).closest('[id^="package_block"]'),
                currentPackageIndex;

            this.packages.forEach(function (packageItem, index) {
                if (packageBlock.attr('id') === packageItem.packageBlockId) {
                    currentPackageIndex = index;
                }
            });

            this.packages[currentPackageIndex].products.forEach(function (product) {
                this.selectItem(product, packageBlock);
            }.bind(this));

            this.packages[currentPackageIndex].isPacked = true;
            packaging.packItems(grid);

            if (this.isAllPacked()) {
                this.smartPackagingInProgress = false;
                this.packaging.messages.show().update(
                    $t('Products have been added to packages according to Calcurates Smart Packaging algorithm')
                );
            }
        },

        selectItem: function (item, packageBlock) {
            var checkbox = packageBlock.find('[type="checkbox"][value="' + item.item_id + '"]'),
                qtyField = checkbox.closest('tr').find('[name="qty"]');

            checkbox.attr('checked', 'checked');
            qtyField.val(item.qty);
        },

        /**
         * @return {boolean}
         */
        isAllPacked: function () {
            var isAllPacked = true;
            this.packages.forEach(function (packageItem) {
                if (!packageItem.isPacked) {
                    isAllPacked = false;
                }
            });

            return isAllPacked;
        },

    }
});
