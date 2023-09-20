# mds PimPrint DemoBundle

mds PimPrint the InDesign Printing Solution for Pimcore.

This bundle is a demo for [PimPrint CoreBundle](https://github.com/mds-agenturgruppe/pimprint-core-bundle) using the [Pimcore 11 Demo](https://github.com/pimcore/demo/tree/11.x).

## Supported Pimcore Versions

- Pimcore 11: `mds-agenturgruppe/pimprint-demo-bundle:^4.0`
- Pimcore 10: `mds-agenturgruppe/pimprint-demo-bundle:^3.0`
- Pimcore 5/6: `mds-agenturgruppe/pimprint-demo-bundle:^1.0`

## Prerequisites

- [PHP 8.1](https://secure.php.net/) or higher
- [Pimcore 11 Demo](https://github.com/pimcore/demo/tree/11.x) installed

## Installing PimPrint Demo

Install `MdsPimPrintDemoBundle` into your Pimcore Demo by issuing:

```bash
composer require mds-agenturgruppe/pimprint-demo-bundle:^4.0
```

Enable `MdsPimPrintCoreBundle` and `MdsPimPrintDemoBundle` in `config/bundles.php`:

```php
MdsPimPrintCoreBundle::class => ['all' => true],
MdsPimPrintDemoBundle::class => ['all' => true],
```

```bash
bin/console pimcore:bundle:install MdsPimPrintCoreBundle
```

For further details please refer the [installation guide](https://pimprint.mds.eu/docs/PimPrint_Demo/Installation.html) in the documentation.

## InDesign Plugin

Document generation in InDesign is done with the mds.PimPrint plugin. Please email <a href="mailto:info@mds.eu?subject=PimPrint Plugin">info@mds.eu</a> to get the plugin.

## Further Information

* [PimPrint Website](https://pimprint.mds.eu)
* [Demo Documentation](https://pimprint.mds.eu/docs/PimPrint_Demo)
* [Documentation](https://pimprint.mds.eu/docs)
