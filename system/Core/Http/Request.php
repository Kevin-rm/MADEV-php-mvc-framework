<?php

namespace MADEV\Core\Http;

/**
 * Cette classe représente une requête HTTP.
 *
 * @package MADEV\Core\Http
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class Request
{
    public $get;
    public $post;
    public $attributes;
    public $cookies;
    public $files;
    public $server;

    private function __construct(
        $get,
        $post,
        $cookies,
        $files,
        $server
    )
    {
        $this->get        = new ParameterBag($get);
        $this->post       = new ParameterBag($post);
        $this->attributes = new ParameterBag();
        $this->cookies    = new ParameterBag($cookies);
        $this->files      = new ParameterBag($files);
        $this->server     = new ParameterBag($server);
    }

    /**
     * Crée une instance de la classe Request à partir des variables superglobales de PHP.
     *
     * @return Request
     */
    public static function createFromSuperGlobals()
    {
        return new self($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    /**
     * Récupère la partie du chemin de l'URL après le nom du script.
     *
     * @return string
     */
    public function getPathInfo()
    {
        return substr(
            parse_url     ($this->server->get('REQUEST_URI'), PHP_URL_PATH),
            strlen(dirname($this->server->get('SCRIPT_NAME')))
        );
    }

    /**
     * Récupère la méthode HTTP utilisée dans la requête.
     *
     * @return string La méthode HTTP utilisée (par exemple, "GET", "POST", etc...).
     */
    public function getMethod()
    {
        return $this->server->get('REQUEST_METHOD');
    }

    /**
     * Récupère les en-têtes de la requête.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = [];
        foreach ($this->server->all() as $name => $value)
            if (strpos($name, 'HTTP_') === 0) {
                /*
                 * Formatage du nom depuis la clé de la variable superglobale $_SERVER.
                 *  
                 * "HTTP_HOST" par exemple deviendra => "Host" 
                 */
                $formattedName = str_replace(
                    ' ',
                    '-',
                    ucwords(
                        strtolower(
                            str_replace('_', ' ', substr($name, 5))
                        )
                    )
                );
                $headers[$formattedName] = $value;
            }

        return $headers;
    }
}
