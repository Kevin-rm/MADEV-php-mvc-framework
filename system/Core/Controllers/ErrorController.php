<?php

namespace MADEV\Core\Controllers;

use Exception;

/**
 * Contrôleur de gestion des erreurs de l'application.
 *
 * @package MADEV\Core
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class ErrorController extends BaseController
{
    private static $errorViewsFolder;

    public function __construct()
    {
        $this->viewFolder       = SYS_PATH                        . 'Resources/views' . DIRECTORY_SEPARATOR;
        self::$errorViewsFolder =                                   'errors'          . DIRECTORY_SEPARATOR;
    }

    /**
     * Gère les exceptions et affiche une vue détaillée de l'erreur.
     *
     * @param  Exception $exception L'exception à gérer.
     * @return string               Le contenu de la vue à afficher.
     */
    public function handleExceptions($exception)
    {
        return $this->renderView(
            'layout',
            [
                'content'   => self::$errorViewsFolder . 'error_exceptions',
                'exception' => $exception
            ]
        );
    }
}
