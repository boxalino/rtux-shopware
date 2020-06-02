# Boxalino Real Time User Experience (RTUX) Framework - Shopware6

## Introduction
For the Shopware6 integration, Boxalino comes with a divided approach: framework layer and integration layer.
The current repository is used as a framework layer and includes:

1. Data Exporter integration
2. API bundle
3. Tracker*

By adding this package to your Shopware6 setup, your store data can be exported to Boxalino.
In order to use the API for generic functionalities (search, autocomplete, recommendations, etc), please check the integration repository
https://github.com/boxalino/rtux-integration-shopware

## Documentation

The latest documentation is available upon request.

## Setup
The Shopware6 plugin has a dependency on the Boxalino API repository (https://github.com/boxalino/rtux-api-php).
In order to activate the bundle, add it to the list of project bundles in config/bundles.php
>Boxalino\RealTimeUserExperienceApi\BoxalinoRealTimeUserExperienceApi::class=>['all'=>true]

## Contact us!

If you have any question, just contact us at support@boxalino.com

*the marked features are not yet available
