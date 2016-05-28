<?php
/**
 * Component Post Install
 *
 * Adds the configuration file for the view component to the Configuration directory
 * of the Framework.
 *
 * @package   SlaxWeb\View
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.4
 */
use SlaxWeb\Bootstrap\Application;

function run(Application $app)
{
    $dir = "{$app["appDir"]}Config/Component/View/";
    $file = __DIR__ . "/view.php";
    if (file_exists($dir) === false) {
        mkdir($dir, 0755, true);
    }

    $exit = 0;
    system("cp {$file} {$dir}", $exit);

    return $exit;
}
