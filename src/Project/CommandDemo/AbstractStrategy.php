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

namespace Mds\PimPrint\DemoBundle\Project\CommandDemo;

use Mds\PimPrint\CoreBundle\InDesign\Command\AbstractCommand;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\Project\AbstractProject;
use Mds\PimPrint\CoreBundle\Service\ImageDimensions;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Mds\PimPrint\CoreBundle\Service\SpecialChars;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Listing as AssetListing;

/**
 * Abstract strategy for command demos to encapsulate each demo publication.
 *
 * @method AbstractProject addCommand(AbstractCommand $command)
 * @method AbstractProject addCommands(array $commands)
 * @method AbstractProject addPreMessage(string $message)
 * @method AbstractProject addPageMessage(string $message, bool $onPage = false)
 * @method AbstractProject setBoxIdentGenericPostfix(string $postfix)
 * @method PluginParameters pluginParams()
 * @method ImageDimensions imageDimensions()
 * @method SpecialChars specialChars()
 *
 * @package Mds\PimPrint\DemoBundle\Project\CommandDemo
 */
abstract class AbstractStrategy
{
    /**
     * PimPrint project instance.
     *
     * @var AbstractProject
     */
    protected AbstractProject $project;

    /**
     * Factory method for strategies.
     *
     * @param string          $class
     * @param AbstractProject $project
     *
     * @return AbstractStrategy
     */
    public static function factory(string $class, AbstractProject $project): AbstractStrategy
    {
        $class = __NAMESPACE__ . "\\" . $class;

        return new $class($project);
    }

    /**
     * Method generated the InDesign commands to build the demo publication.
     *
     * @return void
     */
    abstract public function build(): void;

    /**
     * AbstractStrategy constructor.
     *
     * @param AbstractProject $project
     */
    public function __construct(AbstractProject $project)
    {
        $this->project = $project;
    }

    /**
     * Delegated all undefined method calls to $project.
     * Convenience method offer in all strategies the same interface as in CommandDemo project.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return call_user_func_array([$this->project, $method], $arguments);
    }

    /**
     * Initializes the demo layer in InDesign document.
     *
     * @return void
     * @throws \Exception
     */
    protected function initDemoLayer(): void
    {
        $class = (new \ReflectionClass($this))->getShortName();
        $this->addCommand(new SetLayer($class . ' Demo'));
        $this->setBoxIdentGenericPostfix($class);

        TextBox::setDefaultUseLanguageLayer(false);
    }

    /**
     * Loads a random jpg or png Asset in $path.
     * If $maxWidth is set only assets that are smaller are returned. In some demonstrations we wan't to have small
     * images and in Pimcore demo may be larger ones.
     *
     * @param string   $path
     * @param int|null $maxWidth Max width of the asset.
     * @param string[] $mimeTypes
     *
     * @return Asset|null
     */
    protected function loadRandomAsset(
        string $path,
        int $maxWidth = null,
        array $mimeTypes = ['image/jpeg', 'image/png']
    ): ?Asset {
        $listing = new AssetListing();
        $listing->addConditionParam('path LIKE :path', ['path' => $path])
                ->addConditionParam("mimetype IN (:mimeTypes)", ['mimeTypes' => $mimeTypes])
                ->setLimit(1)
                ->setOrderKey('RAND()', false)
                ->load();

        $asset = $listing->current();
        if (false === $asset instanceof Asset) {
            return null;
        }
        if (null === $maxWidth) {
            return $asset;
        }
        $dimensions = $asset->getDimensions();
        if (false === $dimensions) {
            //we can't check the size. Try another image.
            return $this->loadRandomAsset($path, $maxWidth, $mimeTypes);
        }
        if ($dimensions['width'] > $maxWidth) {
            return $this->loadRandomAsset($path, $maxWidth, $mimeTypes);
        }

        return $asset;
    }

    /**
     * Places a TextBox with $text at $left,$top with size $widthX$height on current active page.
     *
     * @param string $text
     * @param float  $left
     * @param float  $top
     * @param float  $width
     * @param float  $height
     *
     * @return void
     * @throws \Exception
     */
    protected function placeText(
        string $text,
        float $left = 12.7,
        float $top = 12.7,
        float $width = 100.0,
        float $height = 4.0
    ): void {
        $box = new TextBox('copyText', $left, $top, $width, $height, TextBox::FIT_FRAME_TO_CONTENT);
        $box->addString($text);
        $this->addCommand($box);
    }

    /**
     * Returns $number of demo words from Loripsum API.
     *
     * @param int $number
     *
     * @return string
     */
    protected function getDemoWords(int $number = 5): string
    {
        $text = $this->callLoripsumApi('plaintext/2/long/headers');
        $text = preg_replace("#[^A-Za-z0-9 ]#", '', $text);
        $text = str_replace('Lorem ipsum dolor sit amet', '', $text);
        $words = explode(' ', $text, (int)$number + 1);
        array_pop($words);

        return implode(' ', $words);
    }

    /**
     * Returns plaintext demo text from Loripsum API.
     *
     * @param int    $paragraphs
     * @param string $length
     *
     * @return string
     */
    protected function getDemoText(int $paragraphs = 2, string $length = 'medium'): string
    {
        return $this->callLoripsumApi("plaintext/{$length}/{$paragraphs}");
    }

    /**
     * Returns html demo text from Loripsum API.
     *
     * @param int    $paragraphs
     * @param bool   $headers
     * @param bool   $list
     * @param bool   $decorate
     * @param string $length
     *
     * @return string
     */
    protected function getDemoHtml(
        int $paragraphs = 2,
        bool $headers = false,
        bool $list = false,
        bool $decorate = false,
        string $length = 'medium'
    ): string {
        $query = [
            $length,
            $paragraphs,
        ];
        $headers ? $query[] = 'headers' : false;
        $list ? $query[] = 'ul' : false;
        $decorate ? $query[] = 'decorate' : false;

        $html = $this->callLoripsumApi(implode('/', $query));

        //remove not XHTML compliant <mark> tag,
        return str_replace(array('<mark>', '</mark>'), '', $html);
    }

    /**
     * Calls loripsum.net API via 'file_get_contents'.
     *
     * @param string $query
     *
     * @return string
     */
    protected function callLoripsumApi(string $query): string
    {
        $text = file_get_contents("https://loripsum.net/api/" . $query);
        if (false === $text) {
            $text = "Unable to load example text from loripsum.net via 'file_get_contents'";
        }

        return trim($text);
    }
}
