# mds PimPrint DemoBundle
mds PimPrint the InDesign Printing Solution for Pimcore.

This bundle is a demo for [PimPrint CoreBundle](https://github.com/mds-agenturgruppe/pimprint-core-bundle) using the [Pimcore Demo](https://github.com/pimcore/demo). 

## Prerequisites
- [PHP 7.1](https://secure.php.net/) or higher
- [Pimcore Demo](https://github.com/pimcore/demo) installed

## Installation
Install the `MdsPimPrintDemoBundle` into your Pimcore Demo by issuing:
```bash
composer require mds-agenturgruppe/pimprint-demo-bundle
```

Enable `MdsPimPrintCoreBundle` and `MdsPimPrintDemoBundle` by issuing following commands in exactly this order:
```bash
bin/console pimcore:bundle:enable MdsPimPrintCoreBundle
bin/console pimcore:bundle:enable MdsPimPrintDemoBundle
```

## InDesign Plugin
Document generation in InDesign is done with the mds.PimPrint plugin. Please email <a href="mailto:info@mds.eu?subject=PimPrint Plugin">info@mds.eu</a> to get the plugin.

## Further Information
* [PimPrint Website](https://pimprint.mds.eu/)
* [Demo Documentation](https://pimprint.mds.eu/docs/PimPrint_Demo)
* [Documentation](https://pimprint.mds.eu/docs)
* [API-Documentation](https://pimprint.mds.eu/docs/api)
