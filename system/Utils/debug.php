<?php

use MADEV\Core\Application;

/**
 * Affiche le contenu des variables mises en paramètres
 * de manière lisible à des fins de débogage.
 *
 * @param  mixed ...$vars Les variables à afficher.
 * @return void
 */
function display_var(...$vars)
{
    if (empty($vars))
        throw new InvalidArgumentException('La fonction display_var attends au moins un paramètre');

    $output  = '<link rel="stylesheet" href="' . Application::getResourcesUrl() . 'css/display_var.css' . '">';

    $output .= '<div class="display-var-container">';
    $output .= '<p id="caller-file">Fichier : ' . debug_backtrace()[1]['file'] . '</p>';
    foreach ($vars as $index => $var) {
        $output .= '<div class="toggle-container">';
        $output .= '<button class="toggle-button"></button>';
        $output .= '<pre id="pre-' . $index . '">';

        if ($var !== null) {
            ob_start();
            var_dump($var);
            $output .= htmlentities(ob_get_clean(), ENT_QUOTES);
        } else $output .= 'null';

        $output .= '</pre>';
        $output .= '</div>';
    }
    $output .= '</div>';

    $output .= '<script src="' . Application::getResourcesUrl() . 'js/display_var.js' . '"></script>';

    echo $output;
}

/**
 * Appelle la fonction display_var puis puis arrête l'exécution du script.
 *
 * @param  mixed ...$vars
 * @return void
 */
function display_var_and_exit(...$vars)
{
    display_var(...$vars);
    exit();
}
