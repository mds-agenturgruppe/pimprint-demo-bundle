<?php
/**
 * mds PimPrint
 *
 * This source file is licensed under GNU General Public License version 3 (GPLv3).
 *
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license    https://pimprint.mds.eu/license GPLv3
 */

namespace Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto;

use Pimcore\Model\DataObject\Concrete;

/**
 * Simple class to represent a chapter in the CarsDemo rendering.
 * A chapter consists of:
 *  - name
 *  - objectIds to render
 *
 * Use Pimcore DataObject for direct user engagement.
 *
 * @package Mds\PimPrint\DemoBundle\Project\CarsDemo\Dto
 */
class ChapterDto
{
    /**
     * ID of Pimcore Object Chapter is generated from.
     * Used to reference generated InDesign Page-Elements to Pimcore data for content-aware updates.
     *
     * @var int
     */
    public int $referenceId;

    /**
     * Name of the chapter
     *
     * @var string|null
     */
    public ?string $name = null;

    /**
     * Pimcore object ids to render into chapter
     *
     * @var array
     */
    public array $objectIds = [];

    /**
     * ChapterDto
     *
     * @param int         $referenceId
     * @param string|null $name
     * @param array       $objectIds
     */
    public function __construct(int $referenceId, ?string $name = null, array $objectIds = [])
    {
        $this->referenceId = $referenceId;
        $this->name = $name;
        $this->objectIds = $objectIds;
    }

    /**
     * Convenience factory method
     *
     * @param Concrete $object
     * @param array    $objectIds
     *
     * @return ChapterDto
     */
    public static function fromDataObject(Concrete $object, array $objectIds = []): ChapterDto
    {
        $name = null;
        if (method_exists($object, 'getName')) {
            $name = $object->getName();
        }

        return new self((int)$object->getId(), $name, $objectIds);
    }
}
