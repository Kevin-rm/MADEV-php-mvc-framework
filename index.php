<?php

use MADEV\Core\Application;

/*
 * ==================================================
 * CONFIGURATION DES DOSSIERS DE L'APPLICATION
 * ==================================================
 *
 * La modification des valeurs des dossiers ci-dessous est possible
 * mais il faut qu'elles soient impérativement valides.
 * Cela signifie :
 * - Correspondance exacte avec les noms de dossier (attention à la casse).
 * - Non vides.
 * - Existence des dossiers.
 */

// Le dossier "system" contient les fichiers et classes du framework
$system_folder = "system";

// Le dossier qui contient les configurations
$config_folder = "config";

// Le dossier contenant les codes de l'application
$app_folder    = "app";

/*
 * ==================================================
 * DÉFINITION DES CONSTANTES
 * ==================================================
 *
 * Il est important de noter que tous les chemins définis se terminent par "/".
 *
 * ## ATTENTION ##
 * Prière de ne rien modifier à partir de cette section.
 */

// Chemin de base du projet
const                 BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR;

// Chemin vers "system" (les fichiers du framework)
define('SYS_PATH',    BASE_PATH . $system_folder . DIRECTORY_SEPARATOR);

// Chemin vers les configurations de l'application
define('CONFIG_PATH', BASE_PATH . $config_folder . DIRECTORY_SEPARATOR);

// Chemin vers le code de l'application
define('APP_PATH',    BASE_PATH . $app_folder . DIRECTORY_SEPARATOR);

require_once SYS_PATH . 'Core/autoload.php';

/*
 * ==================================================
 *                   BOOTSTRAPPING
 * ==================================================
 */
Application::getInstance()->run();
