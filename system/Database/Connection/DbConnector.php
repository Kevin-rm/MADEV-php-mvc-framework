<?php

namespace MADEV\Database\Connection;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;

/**
 * Une classe abstraite permettant de se connecter à une base de données,
 * qui possède des propriétés et des méthodes que ses classes filles vont partager.
 *
 * @package MADEV\Database\Connection
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
abstract class DbConnector
{
    protected        $host;
    protected        $port;
    protected        $dbName;
    protected        $user;
    protected        $password;
    protected static $config;

    /**
     * @return void
     */
    protected function setHost($host)
    {
        if (empty($host))      throw new InvalidArgumentException('L\'hôte ne doit pas être vide');
        if (!is_string($host)) throw new InvalidArgumentException('L\'hôte doit être un "string"');

        $this->host = $host;
    }

    /**
     * @return void
     */
    protected function setPort($port)
    {
        if (empty($port))                throw new InvalidArgumentException('Le port ne doit pas être vide');
        if (!is_int($port))              throw new InvalidArgumentException('Le port doit être un "integer"');
        if ($port <= 0 || $port > 65535) throw new InvalidArgumentException('Le port doit être compris entre 0 et 65535');

        $this->port = $port;
    }

    /**
     * @return void
     */
    protected function setDbName($dbName)
    {
        if (empty($dbName))      throw new InvalidArgumentException('Le nom de la base de données ne doit pas être vide');
        if (!is_string($dbName)) throw new InvalidArgumentException('Le nom de la base de données doit être un "string"');

        $this->dbName = $dbName;
    }

    /**
     * @return void
     */
    protected function setUser($user)
    {
        if (empty($user))      throw new InvalidArgumentException('Le nom d\'utilisateur ne doit pas être vide');
        if (!is_string($user)) throw new InvalidArgumentException('Le nom d\'utilisateur doit être un "string"');

        $this->user = $user;
    }

    /**
     * @return void
     */
    protected function setPassword($password)
    {
        if ($password === null)    throw new InvalidArgumentException('Le mot de passe ne doit pas être "null"');
        if (!is_string($password)) throw new InvalidArgumentException('Le mot de passe doit être un "string"');

        $this->password = $password;
    }

    /**
     * Charge les informations sur les bases de données de l'utilisateur via
     * le fichier de configuration dédié.
     *
     * @return void
     * @throws Exception
     */
    protected static function loadConfig()
    {
        if (self::$config === null) {
            $configFilePath = CONFIG_PATH . 'database.json';
            if (!file_exists($configFilePath))
                throw new Exception("Le fichier \"$configFilePath\" de configuration de la base de données est introuvable");

            $rawConfig = json_decode(file_get_contents($configFilePath), true, 3);
            self::parseRawConfig($rawConfig);

            self::$config = $rawConfig;
        }
    }

    /**
     * Analyse les données d'informations entrées par les utilisateurs
     * dans le fichier de configuration de base de données.
     *
     * @param  array $rawConfig
     * @return void
     * @throws Exception
     */
    private static function parseRawConfig($rawConfig)
    {
        if ($rawConfig === null)
            throw new InvalidArgumentException('Format invalide des informations dans le fichier de configuration. Suivez et respectez la structure de données établie par défaut');

        $expectedDBMS         = ['mysql'];
        $expectedDbProperties = ['host', 'port', 'dbname', 'user', 'password'];

        foreach ($rawConfig as $key => $element) {
            if (!in_array(strtolower($key), $expectedDBMS))
                throw new Exception("Le type de base de données \"$key\" n'est pas reconnu par ce programme");

            // Les propriétés réelles de $element
            $actualProperties = array_keys($element);

            // Vérification des propriétés manquantes
            $missingProperties = array_diff($expectedDbProperties, $actualProperties);
            if (!empty($missingProperties))
                throw new Exception("Propriétés manquantes pour le type de base de données \"$key\" : \"" . implode(', ', $missingProperties) . "\"");

            // Vérification des propriétés inattendues
            $unexpectedProperties = array_diff($actualProperties, $expectedDbProperties);
            if (!empty($unexpectedProperties))
                throw new Exception("Propriétés inattendues pour le type de base de données \"$key\" : \"" . implode(', ', $unexpectedProperties) . "\"");
        }
    }

    /**
     * Cette fonction permet de récupérer un objet de connexion PDO.
     *
     * @return PDO Objet de connexion PDO.
     */
    public function getConnection() {
        try {
            $con = new PDO($this->getDSN(), $this->user, $this->password);
            $con->exec('SET NAMES utf8');

            return $con;
        } catch(PDOException $e) {
            echo 'PDOException dans DbConnector.getConnection : ' . '<br/>' .
                 $e->getMessage();
            die();
        }
    }

    /**
     * Récupère la chaîne de connexion DSN (Data Source Name) pour la base de données.
     *
     * Le DSN est une chaîne de connexion utilisée pour identifier et accéder à la base,
     * il est donc variable en fonction du DBMS (Database Management System) utilisé.
     *
     * @return string Le DSN spécifique au DBMS choisi.
     */
    abstract protected function getDSN();
}
