<?php
/**
 * PHP Template Loader Provider
 *
 * Registers the PHP Template Loader as the template loader with the Dependency
 * Injection Container.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
namespace SlaxWeb\View\Service;

use Pimple\Container;

class PHPLoaderProvider implements \Pimple\ServiceProviderInterface
{
    /**
     * Register provider
     *
     * Register the PHP Template Loader as the tempalte loader to the DIC.
     *
     * @param \Pimple\Container $container DIC
     * @return void
     */
    public function register(Container $container)
    {
        $container["tplLoader.service"] = function (Container $container) {
            $loader = new \SlaxWeb\View\Loader\PHP($container["response.service"], $container["logger.service"]());
            return $loader->setTemplateExt($container["config.service"]["view.templateExtension"]);
        };
    }
}
