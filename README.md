# mds PimPrint DemoBundle
mds PimPrint the InDesign Printing Solution for Pimcore.

This bundle is a demo for [PimPrint CoreBundle](https://github.com/mds-agenturgruppe/pimprint-core-bundle) using the [Pimcore 6 Demo](https://github.com/pimcore/demo/tree/1.6). 

## Prerequisites
- [PHP 7.1](https://secure.php.net/) or higher
- [Pimcore 6 Demo](https://github.com/pimcore/demo/tree/1.6) installed

Install the Pimcore 6 demo by issuing:
```shell
COMPOSER_MEMORY_LIMIT=-1 composer create-project pimcore/demo=1.6.24 my-project
cd ./my-project
./vendor/bin/pimcore-install
```

## Installation
Install the `MdsPimPrintDemoBundle` into your Pimcore 6 Demo by issuing:
```shell
composer require mds-agenturgruppe/pimprint-demo-bundle:^1.0
```

Enable `MdsPimPrintCoreBundle` and `MdsPimPrintDemoBundle` by issuing following commands in exactly this order:
```shell
bin/console pimcore:bundle:enable MdsPimPrintCoreBundle
bin/console pimcore:bundle:enable MdsPimPrintDemoBundle
bin/console pimcore:migrations:migrate -b MdsPimPrintDemoBundle
```

## InDesign Plugin
Document generation in InDesign is done with the mds.PimPrint plugin. Please email <a href="mailto:info@mds.eu?subject=PimPrint Plugin">info@mds.eu</a> to get the plugin.

## Further Information
* [PimPrint Website](https://pimprint.mds.eu/)
* [Demo Documentation](https://pimprint.mds.eu/docs/PimPrint_Demo)
* [Documentation](https://pimprint.mds.eu/docs)
* [API-Documentation](https://pimprint.mds.eu/docs/api)