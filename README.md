# Boxalino Real Time User Experience (RTUX) Framework - Shopware6

## Introduction
For the Shopware6 integration, Boxalino comes with a divided approach: framework layer and integration layer.
The current repository is used as a framework layer and includes:

1. Data Exporter integration
2. API bundle
3. JS tracker

By adding this package to your Shopware6 setup, your store data can be exported to Boxalino.
In order to use the API for generic functionalities (search, autocomplete, recommendations, etc), please check the integration repository
https://github.com/boxalino/rtux-integration-shopware

## Documentation

The latest documentation is available upon request.

## Setup
1. Add the plugin to your project via composer
``composer require boxalino/rtux-shopware``

2. (obsolete) The Shopware6 plugin has a dependency on the Boxalino API repository (https://github.com/boxalino/rtux-api-php).
   In order to activate the bundle, add it to the list of project bundles in config/bundles.php
``Boxalino\RealTimeUserExperienceApi\BoxalinoRealTimeUserExperienceApi::class=>['all'=>true]``

3. Activate the plugin per Shopware use
``./bin/console plugin:refresh``
``./bin/console plugin:install --activate --clearCache BoxalinoRealTimeUserExperience``
  
4. Log in your Shopware admin and configure the plugin with the configurations provided for your setup
Shopware Admin >> Settings >> System >> Plugins >> Boxalino RTUX Framework for Shopware v6

5. Due to the JS files in the plugin (tracker, Shopware6 CMS blocks, etc), a theme compilation might be required:
``./psh.phar administration:build `` or ``./bin/build-administration.sh ``
``./psh.phar storefront:build`` or `./bin/build-storefront.sh ``

6. In order to kick off your account, a full export is required. 
For this, please set the exporter configuration per Sales Channel and disable the plugin where it is not in use.
The Headless channel must have the plugin disabled.
``./bin/console boxalino:exporter:run full``

The exporter will create a _boxalino_ directory in your project where the temporary CSV files will be stored before the export;
The exporter will log it`s process in a dedicated log _./var/log/boxalino-<env>.log_ 

7. Proceed with the integration features available in our guidelines suggestions https://github.com/boxalino/rtux-integration-shopware

## Contact us!

If you have any question, just contact us at support@boxalino.com

*the marked features are not yet available
