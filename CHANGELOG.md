# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [5.0.0] - 2026-01-21

### Feature

- Relicense to *mds. Commercial License (MCL)*
- Pimcore 12 compatibility

### Refactor

- Update `mds-agenturgruppe/php-code-checker` to `^4.0`
- Update `mds-agenturgruppe/pimprint-core-bundle` to `^5.0`

## [4.5.0] - 2026-01-16

### Feature

- Cars demo renderings
    - `\Mds\PimPrint\DemoBundle\Project\CarPrintDemo\AccessoryPartList`
    - `\Mds\PimPrint\DemoBundle\Project\CarPrintDemo\AccessoryPartTable`
    - `\Mds\PimPrint\DemoBundle\Project\CarPrintDemo\CarBrochure`
    - `\Mds\PimPrint\DemoBundle\Project\CarPrintDemo\CarList`
    - `\Mds\PimPrint\DemoBundle\Project\CarPrintDemo\CarSalesLabel`

### Refactor

- `pimprint-core-bundle` v4.5 compatibility - Symfony service architecture
- Example: Use PimPrint `CommandQueue` Service directly in `CarsDemo\CarSalesLabel\LabelRenderer`
- Symfony bundle structure

## [4.4.0] - 2025-06-18

### Feature

- Couple to Pimcore 11 via conflict in `composer.json`
- Update to `mds-agenturgruppe/pimprint-core-bundle:^4.4`

## [4.3.0] - 2025-02-25

### Feature

- Add demo for new placement mode for localized page-elements to omit the master locale box geometry.
    - `\Mds\PimPrint\DemoBundle\Project\LocalizationDemo\LocalizationProject::renderDontUseMasterLocaleText`
- Example for a dynamic paginated document `\Mds\PimPrint\DemoBundle\Project\DynamicPaginationDemo\ColumnPaginationDemo`
    - Column pagination with `CheckNewColumn` command
    - Facing pages aware CheckNewPage with `CheckNewPage::setNewPosXFacingPages()`
    - Demonstrates usage of Plugin-Element `start_alignment`
- `ImageBoxScaled` asset offset and scaling demo
- Update to `mds-agenturgruppe/pimprint-core-bundle:^4.3`

## [4.2.0] - 2023-11-27

### Features

- Update to `mds-agenturgruppe/pimprint-core-bundle:4.2.0`
- Enhance `\Mds\PimPrint\DemoBundle\Project\CommandDemo\ImageBox::assetTypes`
    - Force usage of Pimcore named thumbnails when using Assets.

## [4.1.0] - 2023-10-11

### Features

- Documentation of `CoreBundle` v4.1.0 SVG support.

## [4.0.0] - 2023-09-20

### Features

- Pimcore 11 compatibility

## [3.0.0] - 2023-07-28

### Features

- Example for placing page elements at the position from the InDesign template document:
    - `\Mds\PimPrint\DemoBundle\Project\CommandDemo\CopyBox::copyToTemplatePosition`
    - `\Mds\PimPrint\DemoBundle\Project\CommandDemo\CopyBox::copyToTemplatePositionWithResize`
- Example of sorting layers:
    - `\Mds\PimPrint\DemoBundle\Project\CommandDemo\Layers::sortLayers`
- Example for localizing page elements:
    - `\Mds\PimPrint\DemoBundle\Project\CommandDemo\Localization`
- Document dimensions and margins are set dynamically in:
    - `\Mds\PimPrint\DemoBundle\Project\CommandDemo\AbstractStrategy::setDocumentSettings`
    - `\Mds\PimPrint\DemoBundle\Project\DataPrint\AbstractProject::setDocumentSettings`
- Example of a MasterLocaleRenderingProject:
    - `\Mds\PimPrint\DemoBundle\Project\LocalizationDemo\LocalizationProject`
- VariableOutput example in `\Mds\PimPrint\DemoBundle\Project\CommandDemo\RelativePositioning` demo.

## [2.0.0] - 2022-09-14

### Features

- Pimcore 10 compatibility
- Use `pimprint-core-bundle:^2.0`

## [1.2.0] - 2022-09-13

### Features

- Update to `mds-agenturgruppe/pimprint-core-bundle:1.3.*`

## [1.1.0] - 2022-05-30

### Features

- Pimcore 6 demo installation instructions.
