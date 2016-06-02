<?php
/**
 * View Component Main Service Provider
 *
 * Main Service provider defines the view loader service.
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

class Provider implements \Pimple\ServiceProviderInterface
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
        // Register the correct loader provider based on config value
        switch ($container["config.service"]["view.loader"]) {
            case "PHP":
                $container->register(new PHPLoaderProvider);
                break;
            default:
                // @todo: raise exception
        }

        // Define view class loader
        $container["loadView.service"] = $container->protect(
            function (string $view) use ($container) {
                $class = rtrim($container["config.service"]["view.classNamespace"], "\\")
                    . "\\"
                    . str_replace("/", "\\", $view);
                return new $class(
                    $container["config.service"],
                    $container["tplLoader.service"],
                    $container["response.service"]
                );
            }
        );
    }
}
