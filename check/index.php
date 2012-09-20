<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 *
 * @category   Shopware
 * @package    Shopware
 * @subpackage Shopware
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stefan Hamann
 * @author     $Author$
 */
error_reporting(E_ALL);
ini_set("display_errors",1);
define("installer",true);

// Check the minimum required php version
if (version_compare(PHP_VERSION, '5.3.2', '<')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', Shopware 4 benötigt mindestens PHP 5.3.2';
    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 4 requires at least PHP 5.3.2';
    return;
}
session_start();
if (!isset($_SESSION["parameters"])){
    $_SESSION["parameters"] = array();
}
require 'Slim/Slim.php';
require 'assets/php/Shopware_Install_Requirements.php';
require 'assets/php/Shopware_Install_Requirements_Path.php';
require 'assets/php/Shopware_Install_Database.php';


/**
 * Load language file
 */
$allowedLanguages = array("de","en");
$selectedLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
$selectedLanguage =  substr($selectedLanguage[0],0,2);
if (empty($selectedLanguage) || !in_array($selectedLanguage,$allowedLanguages)) $selectedLanguage = "en";
if (isset($_POST["language"]) && in_array($_POST["language"],$allowedLanguages)){
    $selectedLanguage = $_POST["language"];
    $_SESSION["language"] = $selectedLanguage;
}
elseif (isset($_SESSION["language"]) && in_array($_SESSION["language"],$allowedLanguages)){
    $selectedLanguage = $_SESSION["language"];
}else {
    $_SESSION["language"] = $selectedLanguage;
}
$language = require(dirname(__FILE__)."/assets/lang/$selectedLanguage.php");

// Initiate slim
$app = new Slim();

// Assign components
$app->config('install.requirements',new Shopware_Install_Requirements());
$app->config('install.requirementsPath',new Shopware_Install_Requirements_Path());
$app->config('install.language',$selectedLanguage);


// Save post - parameters
$params = $app->request()->params();

foreach ($params as $key => $value){
    if (strpos($key,"c_")!==false){
        $_SESSION["parameters"][$key] = $value;
    }
}

// Initiate database object
$databaseParameters = array(
    "user" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_user"] : "",
    "password" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_password"] : "",
    "host" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_host"] : "",
    "port" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_port"] : "",
    "database" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_schema"] : "",
);
$app->config("install.database.parameters",$databaseParameters);
$app->config('install.database',new Shopware_Install_Database($databaseParameters));


function getShopDomain(){
    $domain = $_SERVER["HTTP_HOST"];
    $basepath = str_replace("/check/index.php","",$_SERVER["SCRIPT_NAME"]);
    return array("domain" => $domain, "basepath" =>$basepath);
}

// Set global variables
$app->view()->setData("selectedLanguage",$selectedLanguage);
$app->view()->setData("language",$language);
$app->view()->setData("baseURL",str_replace("index.php","",$_SERVER["PHP_SELF"]));
$app->view()->setData("app",$app);
$app->view()->setData("error",false);
$app->view()->setData("parameters",$_SESSION["parameters"]);
$basepath = getShopDomain();
$app->view()->setData("basepath","http://".$basepath["domain"].$basepath["basepath"]);



// Step 1: Select language
$app->map('/', function () {
    $app = Slim::getInstance();
    $app = Slim::getInstance();
    // Check system requirements
    $shopwareSystemCheck = $app->config('install.requirements');
    $systemCheckResults = $shopwareSystemCheck->toArray();
    if ($shopwareSystemCheck->getFatalError() == true){
        $app->view()->setData("errorRequirements",true);
    }
    // Check file & directory permissions
    $shopwareSystemCheckPath = $app->config("install.requirementsPath");
    $systemCheckPathResults = $shopwareSystemCheckPath->toArray();
    if ($shopwareSystemCheckPath->getFatalError() == true){
           $app->view()->setData("errorFiles",true);
    }

    if ($app->request()->post("action")){
        // Check form
       $getParams = $app->config("install.database.parameters");

       if (empty($getParams["user"]) || empty($getParams["host"]) || empty($getParams["port"]) || empty($getParams["database"])){
           $app->view()->setData("databaseError","Please fill in all fields");
       }else {
            // Check if database account is reachable
           $dbObj = $app->config("install.database");
           $dbObj->setDatabase();

           if ($dbObj->getError()){
               $app->view()->setData("databaseError",$dbObj->getError());
           }
       }
    }
    $app->render("/header.php",array(
        "tab"=>"system","systemCheckResults"=>$systemCheckResults,"systemCheckResultsWritePermissions"=>$systemCheckPathResults));
    $app->render("/step1.php",array());
    $app->render("/footer.php");
})->via('GET','POST')->name("step1");

$app->run();