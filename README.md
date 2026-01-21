# mds PimPrint DemoBundle

mds PimPrint the InDesign Printing Solution for Pimcore.

This bundle is a demo for [PimPrint CoreBundle](https://github.com/mds-agenturgruppe/pimprint-core-bundle) using the [Pimcore Demo](https://github.com/pimcore/demo).

## Supported Pimcore Demos Versions

| Pimcore Demo | PimPrint Demo | PimPrint Maintained |
|--------------|---------------|:-------------------:|
| `2025.x`     | `5.x`         |          ✅          |
| `2024.4`     | `4.x`         |          ✅          |

## Prerequisite

A running Pimcore Demo.

- [Pimcore 2025.x Demo](https://github.com/pimcore/demo/tree/2025.x)
- [Pimcore 2024.4 Demo](https://github.com/pimcore/demo/tree/2024.4)

## Installing PimPrint Demo

Install `MdsPimPrintDemoBundle` matching your Pimcore Demo version by issuing:

```bash
composer require mds-agenturgruppe/pimprint-demo-bundle:^5.0
```

Enable `MdsPimPrintCoreBundle` and `MdsPimPrintDemoBundle` in `config/bundles.php`:

```php
\Mds\PimPrint\CoreBundle\MdsPimPrintCoreBundle::class => ['all' => true],
\Mds\PimPrint\DemoBundle\MdsPimPrintDemoBundle::class => ['all' => true],
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
