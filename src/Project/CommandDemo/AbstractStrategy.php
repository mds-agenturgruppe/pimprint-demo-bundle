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
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\DocumentTemplateSetup;
use Mds\PimPrint\CoreBundle\InDesign\Command\SetLayer;
use Mds\PimPrint\CoreBundle\InDesign\Command\TextBox;
use Mds\PimPrint\CoreBundle\InDesign\Template\Concrete\A4PortraitTemplate;
use Mds\PimPrint\CoreBundle\Project\AbstractProject;
use Mds\PimPrint\CoreBundle\Service\ImageDimensions;
use Mds\PimPrint\CoreBundle\Service\PluginParameters;
use Mds\PimPrint\CoreBundle\Service\SpecialChars;
use Mds\PimPrint\DemoBundle\Project\Traits\FakerGeneratorTrait;
use Mds\PimPrint\DemoBundle\Project\Traits\LoadRandomAssetTrait;

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
    use LoadRandomAssetTrait;
    use FakerGeneratorTrait;

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
     * Initializes rendering of demo publication.
     *
     * @return void
     * @throws \Exception
     */
    protected function initDemo(): void
    {
        $this->setDocumentSettings();
        $this->initDemoLayer();
    }

    /**
     * Sets default demo page settings
     *
     * @return void
     * @throws \Exception
     */
    protected function setDocumentSettings(): void
    {
//        Use the predefined A4PortraitTemplate page template class with the default InDesign page settings.
        $example = new DocumentSetup(new A4PortraitTemplate(), 20);
//        Note: In "Mds\PimPrint\CoreBundle\InDesign\Template\Concrete" in many page templates are predefined
//        with standard indesign dimensions

//      But we simply transfer the document settings from the template file "PimPrint-CommandDemo.indd"
        $command = new DocumentTemplateSetup();
//        Facing pages is used from the manually created document to have the "Page Handling" demo
//        work with or without facing pages to demonstrate the dynamic facing page layout creation.
        $command->setFacingPages(false)
                ->setStartNumber(true);
        $this->addCommand($command);

//        We set the number of pages to 20, to have enough pages in the document for all command demos.
//        Empty pages will be removed at the end of the rendering.
        $command = new DocumentSetup(null, 20);
        $this->addCommand($command);
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
     * Returns $number of words
     *
     * @param int $number
     *
     * @return string
     * @throws \Exception
     */
    protected function getDemoWords(int $number = 5): string
    {
        return implode(
            ' ',
            $this->getFaker()
                 ->words($number)
        );
    }

    /**
     * Returns $numberParagraphs lines in $length of demo plaintext
     *
     * @param int    $numberParagraphs
     * @param string $length
     *
     * @return string
     * @throws \Exception
     */
    protected function getDemoText(int $numberParagraphs = 2, string $length = 'medium'): string
    {
        $length = $this->getLengthFromVerboseParam($length);

        $paragraphs = [];
        for ($i = 0; $i <= $numberParagraphs; $i++) {
            $paragraphs[] = $this->getFaker()
                                 ->text($length);
        }

        return implode("\n", $paragraphs);
    }

    /**
     * Returns html demo text from Loripsum API.
     *
     * @param int        $paragraphs
     * @param bool       $headers
     * @param bool       $list
     * @param array|null $decorate
     * @param string     $length
     *
     * @return string
     * @throws \Exception
     */
    protected function getDemoHtml(
        int $paragraphs = 3,
        bool $headers = true,
        bool $list = true,
        ?array $decorate = ['b', 'i'],
        string $length = 'medium',
    ): string {
        $length = $this->getLengthFromVerboseParam($length);

        $html = '';
        for ($i = 1; $i <= $paragraphs; $i++) {
            if ($headers) {
                $html .= "<h$i>{$this->getDemoWords(rand(1, 3))}</h$i>";
            }
            $sentence = $this->getFaker()
                             ->sentence($length, false);

            if (null !== $decorate) {
                $words = explode(' ', $sentence);
                for ($i = 0; $i <= rand(1, 3); $i++) {
                    foreach ($decorate as $tag) {
                        $index = rand(0, $length - 1);
                        $words[$index] = "<$tag>$words[$index]</$tag>";
                    }
                }
                $sentence = implode(' ', $words);
            }

            $html .= "<p>$sentence</p>";
        }

        if ($list) {
            $html .= "<ul>";
            for ($i = 0; $i < rand(1, 10); $i++) {
                $html .= "<li>{$this->getFaker()->words(rand(1,5), true)}</li>";
            }
            $html .= "</ul>";
        }

        return $html;
    }

    /**
     * Returns length in int for verbose $length parameter
     *
     * @param string $length
     *
     * @return int
     */
    protected function getLengthFromVerboseParam(string $length): int
    {
        return match ($length) {
            'short' => 20,
            'long' => 100,
            'max' => 1000,
            default => 50,
        };
    }

    /**
     * Delegated all undefined method calls to $project.
     * Convenience method offers in all strategies the same interface as in CommandDemo project.
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
}
