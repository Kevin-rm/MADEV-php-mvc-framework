<?php

namespace MADEV\Core\Http;

use InvalidArgumentException;

/**
 * La classe HeaderBag représente un conteneur pour les en-têtes HTTP d'une requête ou d'une réponse.
 *
 * @package MADEV\Core\Http
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class HeaderBag
{
    /*
     * Les en-têtes HTTP.
     *
     * Quelques exemples courants :
     *  - Content-Type    : indique le type de média du corps ('text/html', 'application/json', 'image/jpeg', etc...).
     *  - Location        : indique l'URL vers laquelle le client doit être redirigé.
     *  - Set-Cookie      : utilisé pour envoyer des cookies au client.
     *  - Cache-Control   : contrôle comment les pages peuvent être mises en cache.
     *  - Custom Header   : en-têtes personnalisés pour des besoins spécifiques.
     *  - Accept-Language : indique les langues préférées pour le contenu.
     */
    private $headers;

    public function __construct($headers = [])
    {
        $this->headers = [];
        $this->add($headers);
    }

    /**
     * Définit un en-tête HTTP.
     *
     * @param string $name  Nom de l'en-tête.
     * @param string $value Valeur de l'en-tête.
     */
    public function set($name, $value)
    {
        self::validateHeaderName($name);
        if (empty($value) || !is_string($value))
            throw new InvalidArgumentException("La valeur de l'en tête \"$name\" doit être une chaîne de caractères non vide");

        $this->headers[$name] = $value;
    }

    /**
     * Ajoute des en-têtes HTTP.
     *
     * @param  array $headers
     * @return void
     */
    public function add($headers)
    {
        if (!is_associative_array($headers))
            throw new InvalidArgumentException('Les en-têtes doivent être un tableau associatif (paires clé-valeur)');

        foreach ($headers as $name => $value) $this->set($name, $value);
    }

    /**
     * Récupère la valeur d'un en-tête HTTP spécifique.
     *
     * @param  string $name Nom de l'en-tête.
     * @return string|null  Valeur de l'en-tête ou null si l'en-tête n'existe pas.
     */
    public function get($name)
    {
        self::validateHeaderName($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : null;
    }

    /**
     * Vérifie si un en-tête HTTP existe.
     *
     * @param  string $name Nom de l'en-tête à vérifier.
     * @return bool         True si l'en-tête existe, sinon false.
     */
    public function has($name)
    {
        self::validateHeaderName($name);
        return isset($this->headers[$name]);
    }

    /**
     * Supprime un en-tête HTTP.
     *
     * @param string $name Nom de l'en-tête à supprimer.
     */
    public function remove($name)
    {
        self::validateHeaderName($name);
        unset($this->headers[$name]);
    }

    /**
     * Récupère tous les en-têtes HTTP.
     *
     * @return array Tableau associatif des en-têtes HTTP.
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Récupère les clés (noms) de tous les en-têtes HTTP.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->headers);
    }

    /**
     * Compte le nombre d'en-têtes HTTP.
     *
     * @return int Le nombre d'en-têtes.
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * Un helper qui sert à vérifier si le nom de l'en-tête HTTP
     * est valide (une chaîne de caractères non vide et ne contenant pas de chiffres) ou pas.
     *
     * @param  mixed $name
     * @return void
     */
    private static function validateHeaderName($name)
    {
        if (empty($name) || !is_string_without_digit($name))
            throw new InvalidArgumentException('Le nom d\'un en-tête HTTP doit être une chaîne de caractères non vide, et ne doit pas contenir de chiffre');
    }
}
