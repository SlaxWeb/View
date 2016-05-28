<?php
/**
 * View Component Config
 *
 * View Component Configuration file
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
/*
 * Template base direcotry
 *
 * Where all of your template files are located.
 */
$configuration["view.baseDir"] = __DIR__ . "/../../../Template/";

/*
 * Automatically set template name if none was set before
 */
$configuration["view.autoTplName"] = true;

/*
 * Template loader
 *
 * Available options are:
 * - PHP
 */
$configuration["view.loader"] = "PHP";

/*
 * Load View Component Provider
 *
 * Do not change, unless you know what you are doing. You may break your system.
 */
$configuration["app.providerList"] = [
    \SlaxWeb\View\Service\Provider::class
];
