# mds PimPrint DemoBundle

mds PimPrint the InDesign Printing Solution for Pimcore.

This bundle is a demo for [PimPrint CoreBundle](https://github.com/mds-agenturgruppe/pimprint-core-bundle) using the [Pimcore 10 Demo](https://github.com/pimcore/demo/tree/10.3).

## Supported Pimcore Versions

- Pimcore 10: `mds-agenturgruppe/pimprint-demo-bundle:^3.0`
- Pimcore 5/6: `mds-agenturgruppe/pimprint-demo-bundle:^1.0`

## Prerequisites

- [PHP 8.0](https://secure.php.net/) or higher
- [Pimcore 10 Demo](https://github.com/pimcore/demo/tree/10.3) installed

## Installing PimPrint Demo

Install the `MdsPimPrintDemoBundle` into your Pimcore Demo by issuing:

```bash
composer require mds-agenturgruppe/pimprint-demo-bundle:^3.0
```

Enable `MdsPimPrintCoreBundle` and `MdsPimPrintDemoBundle` by issuing following commands in exactly this order:

```bash
bin/console pimcore:bundle:enable MdsPimPrintCoreBundle
bin/console pimcore:bundle:install MdsPimPrintCoreBundle
 
bin/console pimcore:bundle:enable MdsPimPrintDemoBundle
```

For further details please refer the [installation guide](https://pimprint.mds.eu/docs/PimPrint_Demo/Installation.html) in the documentation.

## InDesign Plugin

Document generation in InDesign is done with the mds.PimPrint plugin. Please email <a href="mailto:info@mds.eu?subject=PimPrint Plugin">info@mds.eu</a> to get the plugin.

## Further Information

* [PimPrint Website](https://pimprint.mds.eu)
* [Demo Documentation](https://pimprint.mds.eu/docs/PimPrint_Demo)
* [Documentation](https://pimprint.mds.eu/docs)
