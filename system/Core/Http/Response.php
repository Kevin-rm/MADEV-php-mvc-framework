<?php

namespace MADEV\Core\Http;

use InvalidArgumentException;

/**
 * Représente la réponse HTTP générée par l'application.
 *
 * @package MADEV\Core\Http
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class Response
{
    const          HTTP_OK        = 200;
    const          HTTP_FOUND     = 302;
    const          HTTP_FORBIDDEN = 403;
    const          HTTP_NOT_FOUND = 404;

    public         $headers;
    private        $content;
    private        $statusCode;
    private static $validContentTypes = [
        'text/plain', 'text/html', 'application/json'
    ];
    private static $validStatusCodes  = [
        self::HTTP_OK,
        self::HTTP_FOUND,
        self::HTTP_FORBIDDEN,
        self::HTTP_NOT_FOUND
    ];

    public function __construct(
        $content     = '',
        $statusCode  = self::HTTP_OK,
        $headers     = []
    )
    {
        $this->headers = new HeaderBag($headers);
        $this->setContent($content);
        $this->setStatusCode($statusCode);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param  int $statusCode
     * @return void
     */
    public function setStatusCode($statusCode)
    {
        if (!self::statusCodeIsValid($statusCode)) throw new InvalidArgumentException('Le "status code" est invalide');
        $this->statusCode = $statusCode;
    }

    /**
     * @param  mixed  $content
     * @param  string $contentType
     * @return void
     */
    public function setContent($content, $contentType = 'text/html')
    {
        if (!isset($content))
            throw new InvalidArgumentException('Le contenu d\'une réponse ne peut pas être "undefined" ou "null"');
        if (!is_string($content)) {
            $content     = json_encode($content);
            $contentType = 'application/json';
        }

        $this->setContentType($contentType);
        $this->content = $content;
    }

    /**
     * Définit le type de contenu de la réponse.
     *
     * @param  string $contentType Le type de contenu.
     * @return void
     */
    public function setContentType($contentType)
    {
        if (!in_array($contentType, self::$validContentTypes, true))
            throw new InvalidArgumentException('Le type de contenu n\'est pas valide');

        $this->headers->set('Content-Type', $contentType);
    }

    /**
     * Cette fonction envoie de la réponse HTTP (le code d'état, les en-têtes et le contenu) au client.
     *
     * @return void
     */
    public function send()
    {
        // Définition du code d'état HTTP
        http_response_code($this->statusCode);

        // Définition des en-têtes
        foreach ($this->headers->all() as $name => $value) header("$name: $value");

        // Envoi du contenu
        echo $this->content;
    }

    /**
     * Vérifie si le code de statut HTTP fourni est valide.
     *
     * @param  int $statusCode Le code de statut HTTP à vérifier.
     * @return bool            True si le code de statut est valide, sinon false.
     */
    private static function statusCodeIsValid($statusCode)
    {
        return in_array($statusCode, self::$validStatusCodes, true);
    }
}
