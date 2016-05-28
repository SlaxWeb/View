<?php
/**
 * PHP Template Loader
 *
 * The PHP Template Loader loads the PHP Template file, and injects the set data
 * into the template.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View\Loader;

class PHP
{
    /**
     * Template file
     *
     * @var string
     */
    protected $_template = "";

    /**
     * Template directory
     *
     * @var string
     */
    protected $_templateDir = "";

    /**
     * Cached template data
     *
     * @var array
     */
    protected $_cachedData = [];

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
        $this->_template = $template;
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
        $this->_templateDir = rtrim($templateDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        return $this;
    }

    /**
     * Render the template
     *
     * Loads the template file with the retrieved data array, and returns the rendered
     * template. By default the template data is cached in the internal property
     * for all future renders of that same requests. To disable the cached vars
     * and load the template only with the currently passed in data, bool(false)
     * has to be sent as the second parameter.
     *
     * @param array $data Template data to be passed to the template. Default []
     * @param bool $cacheData Cache template data. Default bool(true)
     * @return string
     *
     * @exceptions SlaxWeb\View\Exception\TemplateNotFoundException
     */
    public function render(array $data = [], bool $cacheData = true)
    {
        if ($cacheData) {
            $this->_cachedData = array_merge($this->_cachedData, $data);
            $data = $this->_cachedData;
        }

        if (file_exists($this->_templateDir . $this->_template) === false) {
            throw new \SlaxWeb\View\Exception\TemplateNotFoundException(
                "Requested template file ({$this->_templateDir}{$this->_template})was not found."
            );
        }

        extract($data);

        $buffer = "";
        ob_start();
        include $this->_templateDir . $this->_template;
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
}
