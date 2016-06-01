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
$configuration["baseDir"] = __DIR__ . "/../Template/";

/*
 * Automatically set template name if none was set before
 */
$configuration["autoTplName"] = true;

/*
 * Template loader
 *
 * Available options are:
 * - PHP
 * - Twig (only if slaxweb/view-twig subcomponent is installed)
 */
$configuration["loader"] = "PHP";

/*
 * View class namespace
 *
 * The namespace in which the View classes are defined.
 *
 * NOT: If you change the autoloader config, you have to change this configuration
 * as well. If you fail to do so, the "View Class Loader" will no longer work.
 */
$configuration["classNamespace"] = "\\App\\View\\";
