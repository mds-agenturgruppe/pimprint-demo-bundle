<?php
/**
 * mds. Agenturgruppe GmbH
 *
 * This source file is available under the terms of the
 * mds. Commercial License (MCL)
 *
 * Full copyright and license information is available in
 * LICENSE.md, which is distributed with this source code.
 *
 * @copyright Copyright (c) mds. Agenturgruppe GmbH (https://www.mds.eu)
 * @license   mds. Commercial License (MCL)
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
     * The method generates the InDesign commands to build the demo publication.
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
     * Initialize the demo document and layer settings
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
     * Sets default demo document settings
     *
     * @return void
     * @throws \Exception
     */
    protected function setDocumentSettings(): void
    {
        // You can use the predefined A4PortraitTemplate with the default InDesign page settings.
        $example = new DocumentSetup(new A4PortraitTemplate(), 20);

        // Note: Many standard page templates with default InDesign dimensions exist in
        // "Mds\PimPrint\CoreBundle\InDesign\Template\Concrete".

        // Here we copy document settings from the template file "PimPrint_CommandDemo.indd".
        $command = new DocumentTemplateSetup();

        // Use the facing-pages setting from the template document.
        // This allows the "Page Handling" demo to work with or without facing pages
        // and to demonstrate dynamic facing-page layout creation
        $command->setFacingPages(false)
                ->setStartNumber(true);
        $this->addCommand($command);

        // Set the document to 20 pages.
        // This ensures enough pages for all demos.
        // The renderer removes empty pages at the end.
        $command = new DocumentSetup(null, 20);
        $this->addCommand($command);
    }

    /**
     * Initializes the demo layer for the project.
     * Sets up the layer using the class name and add the command
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
     * Places a `TextBox` with $text at $left and $top, with a size of $width x $height, on the current active page.
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
     * Generate a string containing a specified number of random words.
     *
     * @param int $number The number of words to generate. Defaults to 5.
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
     * Generates dummy text consisting of multiple paragraphs.
     *
     * @param int    $numberParagraphs The number of paragraphs to generate. Defaults to 2.
     * @param string $length           The length of each paragraph. Defaults to 'medium'.
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
     * Generates HTML content for demo purposes.
     *
     * @param int        $paragraphs Number of paragraphs to generate. Defaults to 3.
     * @param bool       $headers    Whether to include headers before each paragraph. Defaults to true.
     * @param bool       $list       Whether to include an unordered list after the paragraphs. Defaults to true.
     * @param array|null $decorate   Optional tags to decorate specific words in the sentences. Defaults to ['b', 'i'].
     * @param string     $length     Length descriptor for sentences ('short', 'medium', 'long'). Defaults to 'medium'.
     *
     * @return string Generated demo HTML content.
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
                $html .= "<h$i>{$this->getDemoWords(rand(3, 5))}</h$i>";
            }
            $sentence = $this->getFaker()
                             ->sentence($length, false);

            if (null !== $decorate) {
                $words = explode(' ', $sentence);
                for ($j = 0; $j <= rand(1, 3); $j++) {
                    foreach ($decorate as $tag) {
                        $index = rand(0, $length - 1);
                        if (str_starts_with($words[$index], '<')) {
                            continue;
                        }
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
     * Get the length value based on the given verbosity parameter.
     *
     * @param string $length Verbosity level ('short', 'long', 'max', or other).
     *
     * @return int Corresponding length value for the given verbosity level.
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
     * Delegates all undefined method calls to `$project`.
     * This convenience method provides the same interface in all strategies
     * as in the `CommandDemo` project.
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
