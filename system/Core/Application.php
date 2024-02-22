<?php

namespace MADEV\Core;

use Exception;
use MADEV\Core\Controllers\BaseController;
use MADEV\Core\Controllers\ErrorController;
use MADEV\Core\Http\Request;
use MADEV\Core\Http\Response;
use MADEV\Core\Routing\Router;

require_once (SYS_PATH . 'Utils/debug.php');
require_once (SYS_PATH . 'Utils/general_functions.php');

/**
 * Le bootstrap de l'application.
 *
 * Cette classe représente le point d'entrée principal de l'application. Elle gère le cycle de vie de l'application,
 * créant la requête, traitant la requête via le routeur, et renvoyant la réponse générée au client.
 *
 * @package MADEV\Core
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class Application
{
    private        $router;
    private        $errorController;
    private        $request;
    private        $response;
    private        $booted;
    private static $resourcesUrl;
    private static $instance;

    private function __construct() { $this->booted = false; }

    /**
     * @return string
     */
    public static function getResourcesUrl()
    {
        return self::$resourcesUrl;
    }

    /**
     * @return Application
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) self::$instance = new Application();
        return self::$instance;
    }

    /**
     * Obtient l'URL de base de l'application en utilisant les informations fournies par la requête actuelle.
     *
     * @return string L'URL de base de l'application.
     */
    public function getBaseUrl()
    {
        // Récupérer le protocole (http ou https)
        $protocol = $this->request->server->has('HTTPS') && $this->request->server->get('HTTPS') === 'on' ? "https" : "http";

        // Récupérer le nom du serveur
        $host = $this->request->server->get('HTTP_HOST');

        // Construire l'URL de base
        return "$protocol://$host" . dirname($this->request->server->get('PHP_SELF'));
    }

    /**
     * Exécute l'application.
     *
     * @return void
     */
    public function run()
    {
        try {
            /*
             * Initialisation de l'application, effectué une seule fois.
             * Cette fonction peut générer des exceptions.
             */
            $this->boot();

            // Création de la requête si elle est null
            if ($this->request === null) $this->request  = Request::createFromSuperGlobals();

            // Traitement de la requête
            $this->response = $this->router->dispatch($this->request);
        } catch (Exception $e) {
            $this->response = $this->handleError($e);
        } finally {
            // Envoi de la réponse générée au client
            $this->response->send();

            $this->terminate();
        }
    }

    /**
     * Initialise l'application si ce n'est pas déjà fait.
     *
     * @return void
     * @throws Exception
     */
    private function boot()
    {
        if ($this->booted) { return; }

        $this->request         = Request::createFromSuperGlobals();
        $this->response        = null;
        $this->errorController = new ErrorController();
        self::$resourcesUrl    = self::getBaseUrl() . '/system/Resources' . DIRECTORY_SEPARATOR;

        $this->router          = new Router();

        $this->booted          = true;
    }

    /**
     * Effectue les opérations de nettoyage à la fin du cycle de vie de l'application.
     *
     * Cette méthode est appelée à la fin de l'exécution de l'application pour libérer les ressources
     * et remettre à zéro les variables liées à la requête et à la réponse.
     *
     * @return void
     */
    private function terminate()
    {
        $this->request  = null;
        $this->response = null;
    }

    /**
     * Gère les exceptions et renvoie une réponse appropriée.
     *
     * @param  Exception $exception L'exception à gérer.
     * @return Response             La réponse générée en réponse à l'exception.
     */
    private function handleError($exception)
    {
        return new Response(
            $this->errorController->handleExceptions($exception)
        );
    }
}
