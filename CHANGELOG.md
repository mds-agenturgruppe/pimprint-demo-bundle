# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.2.0] - 2023-11-27

### Features

- Update to `mds-agenturgruppe/pimprint-core-bundle:3.2.0`
- Enhance `\Mds\PimPrint\DemoBundle\Project\CommandDemo\ImageBox::assetTypes`
    - Force usage of Pimcore named thumbnails when using Assets.

## [3.1.0] - 2023-10-11

### Features

- Documentation of `CoreBundle` v3.1.0 SVG support.

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
