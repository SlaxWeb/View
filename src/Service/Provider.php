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
        // Register the PHP view loader if configuration says so
        if (strtolower($container["config.service"]["view.loader"]) === "php") {
            $container->register(new PHPLoaderProvider);
        }

        // Define view class loader
        $container["loadView.service"] = $container->protect(
            function (string $view, bool $useLayout = true) use ($container) {
                $cacheName = "loadView.service-{$view}" . ($useLayout ? "1" : "0");
                if (isset($container[$cacheName]) && isset($container["view.skipCache"]) === false) {
                    return $container[$cacheName];
                }

                $class = $container["view.className"] ?? $this->getViewClass($view, $container);
                $view = new $class(
                    $container["config.service"],
                    $container["tplLoader.service"],
                    $container["response.service"]
                );

                if (method_exists($view, "init")) {
                    $args = func_get_args();
                    array_slice($args, 2);
                    $view->init(...$args);
                }

                if ($useLayout && ($layoutClass = $container["config.service"]["view.defaultLayout"]) !== "") {
                    $view->setLayout($container["loadView.service"]($layoutClass, false));
                }

                return $container[$cacheName] = $view;
            }
        );

        $container["loadTemplate.service"] = $container->protect(
            function (string $template, bool $useLayout = true) use ($container) {
                $cacheName = "loadTemplate.service-{$template}" . ($useLayout ? "1" : "0");
                if (isset($container[$cacheName])) {
                    return $container[$cacheName];
                }

                $container["view.skipCache"] = true;
                $container["view.className"] = \SlaxWeb\View\Base::class;
                $view = $container["loadView.service"]("", false);
                $view->template = $template;
                unset($container["view.skipCache"], $container["view.className"]);

                if ($useLayout) {
                    $layoutName = $container["config.service"]["view.defaultLayout"];
                    // if the layout class exist, load the layout as a view
                    $layoutView = class_exists($this->getViewClass($layoutName, $container))
                        ? $container["loadView.service"]($layoutName, false)
                        : $container["loadTemplate.service"]($layoutName, false);

                    $view->setLayout($layoutView);
                }

                return $container[$cacheName] = $view;
            }
        );
    }

    /**
     * Get View Class
     *
     * Constructs the full view class name with the fully qualified namespace name
     * and returns it as a string.
     *
     * @param string $view Namespaceless view class name
     * @param \Pimple\Container $container Service container
     * @return string
     */
    protected function getViewClass(string $view, Container $container): string
    {
        return rtrim($container["config.service"]["view.classNamespace"], "\\")
            . "\\"
            . str_replace("/", "\\", $view);
    }
}
