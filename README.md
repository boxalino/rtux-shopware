# Boxalino Real Time User Experience (RTUX) Framework - Shopware6

## Introduction
For the Shopware6 integration, Boxalino comes with a divided approach: framework layer, data export layer and integration layer.
The current repository is used as a **framework layer** and includes:

1. API bundle
2. JS tracker

By adding this package to your Shopware6 project, your setup can do the following:
 1. Enable the tracking
 2. Proceed to integrate features.
 
In order to create your dedicated account data index, the **data export layer has to be installed before you continue**.
https://github.com/boxalino/exporter-shopware6

In order to use the API for generic functionalities (search, autocomplete, recommendations, etc), 
please **continue with the guidelines from the integration repository** https://github.com/boxalino/rtux-integration-shopware

## Documentation
Check the public documentation on Framework Integrations 
https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/349503489/Framework+Integration
https://boxalino.atlassian.net/wiki/spaces/BPKB/pages/349601793/Shopware+6

Additionally, please check the GITHUB wiki page.

## Setup
1. Add the plugin to your project via composer
``composer require boxalino/rtux-shopware``

2. Activate the plugin per Shopware use
``./bin/console plugin:refresh``
``./bin/console plugin:install --activate --clearCache BoxalinoRealTimeUserExperience``
  
3. Log in your Shopware admin and configure the plugin with the configurations provided for your setup
Shopware Admin >> Settings >> System >> Plugins >> Boxalino RTUX Framework for Shopware v6

4. Due to the JS files in the plugin (tracker, Shopware6 CMS blocks, etc), a theme compilation might be required:
``./psh.phar administration:build `` or ``./bin/build-administration.sh ``
``./psh.phar storefront:build`` or `./bin/build-storefront.sh ``

5. If the plugin configurations are not displayed, they can be accessed via direct link:
``admin#/sw/plugin/settings/BoxalinoRealTimeUserExperience``

All events will use a dedicated log file _./var/log/boxalino-<env>.log_ 

## Contact us!

If you have any question, just contact us at support@boxalino.com
