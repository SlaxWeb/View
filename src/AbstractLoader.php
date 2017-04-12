<?php
/**
 * Abstract Loader
 *
 * Abstract loader has to be extended by all template loaders, as it provides some
 * base functionality, properties, and constants.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View;

use Psr\Log\LoggerInterface as Logger;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractLoader
{
    /**
     * Template variables caching
     */
    const TPL_CACHE_VARS = 100;
    const TPL_USE_VARS_ONLY = 101;
    const TPL_NO_CACHE_VARS = 102;

    /**
     * Template render output control
     */
    const TPL_RETURN = 200;
    const TPL_OUTPUT = 201;

    /**
     * Response
     *
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response = null;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     * Template file extension
     *
     * @var string
     */
    protected $tplExt = "";

    /**
     * Template file
     *
     * @var string
     */
    protected $template = "";

    /**
     * Template directory
     *
     * @var string
     */
    protected $templateDir = "";

    /**
     * Cached template data
     *
     * @var array
     */
    protected $cachedData = [];

    /**
     * Class constructor
     *
     * Assigns the dependant Response object to the class property. The View loader
     * will automatically add template contents to as response body.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response Response object
     * @param \Psr\Log\LoggerInterface $logger PSR4 compatible Logger object
     */
    public function __construct(Response $response, Logger $logger)
    {
        $this->response = $response;
        $this->logger = $logger;

        if (method_exists($this, "init")) {
            $this->init();
        }

        $this->logger->info("PHP Template Loader initialized");
    }

    /**
     * Set Template File Extension
     *
     * Sets the template file extension to the provided value. It automatically
     * strips the leading dot if present.
     *
     * @param string $tplExt Template File Extension
     * @return self
     */
    public function setTemplateExt(string $tplExt): self
    {
        $this->tplExt = "." . ltrim($tplExt, ",");
        return $this;
    }

    /**
     * Set the template
     *
     * Sets the template filename.
     *
     * @param string $template Name of the template file
     * @return self
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        $this->logger->debug("Template file set to loader.", ["template" => $this->template]);
        return $this;
    }

    /**
     * Set the template directory
     *
     * Sets the template directory name.
     *
     * @param string $templateDir Name of the template directory
     * @return self
     */
    public function setTemplateDir(string $templateDir): self
    {
        $this->templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->logger->debug("Template directory set to loader." , ["templateDir" => $this->templateDir]);
        return $this;
    }

    /**
     * Render the template
     *
     * Loads the template file with the retrieved data array, and returns the rendered
     * template. By default the template data is cached in the internal property
     * for all future renders of that same requests. To disable the cached vars
     * and load the template only with the currently passed in data, constant TPL_NO_CACHE_VARS
     * has to be sent as the third parameter.
     *
     * The Render method will automatically add contents of the rendered template
     * file to the Response object as response body. If you wish to retrieve the
     * contents back, pass in constant TPL_RETURN as the second parameter. When
     * the rendered template is only added to the Response object, an empty string
     * is returned.
     *
     * @param array $data Template data to be passed to the template. Default []
     * @param int $return Output or return rendered template. Default self::TPL_OUTPUT
     * @param int $cacheData Cache template data. Default self::TPL_CACHE_VARS
     * @return string
     *
     * @exceptions SlaxWeb\View\Exception\TemplateNotFoundException
     */
    public function render(
        array $data = [],
        int $return = self::TPL_OUTPUT,
        int $cacheData = self::TPL_CACHE_VARS
    ): string {
        $this->logger->info("Rendering template", ["template" => $this->template]);

        $data = $this->combineData($data, $cacheData);

        $template = preg_replace("~\.{$this->tplExt}$~", "", $this->template)
            . "{$this->tplExt}";

        if (file_exists($this->templateDir . $template) === false) {
            $this->logger->error(
                "Template does not exist or is not readable",
                ["template" => $this->templateDir . $template]
            );
            throw new \SlaxWeb\View\Exception\TemplateNotFoundException(
                "Requested template file ({$this->templateDir}{$template}) was not found."
            );
        }

        $buffer = $this->load($template, $data);
        $this->logger->debug(
            "Template loaded and rendered.",
            ["template" => $template, "data" => $data, "rendered" => $buffer]
        );

        if ($return === AbstractLoader::TPL_RETURN) {
            $this->logger->info("Returning rendered template");
            return $buffer;
        }

        $this->response->setContent($this->response->getContent() . $buffer);
        $this->logger->info("Rendered template appended to Response contents");
        return "";
    }

    /**
     * Combine data
     *
     * Combines the received data and the already cached data, depending on the
     * *$cacheData* parameter passed as the second parameter. The first parameter
     * holds an array of data that will be combined with the cached data.
     *
     * @param array $data Template data to be passed to the template. Default []
     * @param int $cacheData Cache template data.
     * @return array
     */
    protected function combineData(array $data, int $cacheData): array
    {
        if ($cacheData < AbstractLoader::TPL_NO_CACHE_VARS) {
            $this->logger->info("View data combined from input and cache");
            $data = array_merge($this->cachedData, $data);
            if ($cacheData === AbstractLoader::TPL_CACHE_VARS) {
                $this->logger->info("Caching newly combined view data");
                $this->cachedData = $data;
            }
        }

        return $data;
    }

    /**
     * Load template
     *
     * Load the template file. Defined as abstract, because each loader will load
     * its template files in a different way.
     *
     * @param string $template Path to the template file
     * @param array $data View data
     * @return string
     */
    abstract protected function load(string $template, array $data): string;
}
