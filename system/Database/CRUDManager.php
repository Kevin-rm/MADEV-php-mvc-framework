<?php

namespace MADEV\Database;

use Exception;
use InvalidArgumentException;
use MADEV\Database\Connection\MysqlConnector;
use PDO;
use PDOException;

/**
 * Cette classe gère toutes les opérations basiques de CRUD (Create, Read, Update, Delete) dans le contexte dune base de données.
 *
 * Toutes les fonctions de cette classe sont déclarées comme statiques et
 * requièrent un objet de type PDO en tant qu'argument, représentant la connexion à la base de données.
 * Si l'objet $connection n'est pas défini ou est null, la connexion par défaut sera établie avec MySQL.
 *
 * @package MADEV\Database
 * @author  Kevin Ramarozatovo <kevinramarozatovo@gmail.com>
 */
class CRUDManager
{
    private function __construct() { }

    /**
     * Cette fonction récupère toutes les lignes d'une table dans une base de données.
     *
     * @param  PDO|null $connection La connexion PDO à la base de données.
     *                              Si non spécifié, utilise la connexion par défaut.
     * @param  string $tableName    Le nom de la table à partir de laquelle récupérer les données.
     * @return array|false          Un tableau d'objets si succès, false sinon.
     * @throws PDOException
     */
    public static function findAll($connection, $tableName)
    {
        if (!isset($connection))    $connection = MysqlConnector::getInstance()->getConnection();
        if (empty($tableName))      throw new InvalidArgumentException('Le nom de la table ne doit pas être vide');
        if (!is_string($tableName)) throw new InvalidArgumentException('Le nom de la table doit être une chaîne de caractères');

        $tableName = trim($tableName);
        $stmt = $connection->prepare("SELECT * FROM $tableName");

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new PDOException('Erreur dans CRUDManager.findAll : ' .$e->getMessage());
        }
    }

    /**
     * Cette méthode permet de sélectionner des données depuis une table en fonction de conditions de filtrage.
     *
     * @param  PDO|null $connection       La connexion PDO à la base de données.
     *                                    Si non spécifié, utilise la connexion par défaut.
     * @param  string   $tableName        Le nom de la table à partir de laquelle récupérer les données.
     * @param  string   $whereCondition   La condition de filtrage pour les données à récupérer.
     *                                    Par défaut, récupère toutes les lignes
     * @param  mixed    ...$columnsToShow Les colonnes à sélectionner dans la requête.
     *                                    Par défaut, sélectionne toutes les colonnes.
     * @return array|false                Un tableau d'objets si succès, false sinon.
     * @throws PDOException
     */
    public static function findWithFilters($connection, $tableName, $whereCondition, ...$columnsToShow)
    {
        if (!isset($connection)) $connection = MysqlConnector::getInstance()->getConnection();
        if (empty($tableName))      throw new InvalidArgumentException('Le nom de la table ne doit pas être vide');
        if (!is_string($tableName)) throw new InvalidArgumentException('Le nom de la table doit être une chaîne de caractères');

        if (empty($whereCondition)) $whereCondition = "1";
        if (!is_string($whereCondition)) throw new InvalidArgumentException('La condition de filtrage doit être une chaîne de caractères');

        foreach ($columnsToShow as $column)
            if (is_string($column)) throw new InvalidArgumentException('Chaque élément de $columnsToShow doit être une chaîne de caractères');

        $columns = empty($columnsToShow) ? '*' : implode(", ", $columnsToShow);
        try {
            $stmt = $connection->prepare("SELECT $columns FROM $tableName WHERE $whereCondition");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new PDOException('Erreur dans CRUDManager.findWithFilters : ' . $e->getMessage());
        }
    }

    /**
     * Cette fonction sert à récupérer des données depuis une base en exécutant des requêtes "SELECT" personnalisées.
     *
     * #### ATTENTION ####
     * La possibilité de requête libre requiert un meilleur contrôle du code
     * afin d'éviter des résultats inattendus.
     *
     * @param  PDO|null    $connection La connexion PDO à la base de données.
     *                                 Si non spécifié, utilise la connexion par défaut.
     * @param  string      $query      La requête.
     * @return array|false             Un tableau d'objets si succès, false sinon.
     * @throws PDOException
     */
    public static function selectFromSQLRaw($connection, $query)
    {
        if (!isset($connection)) $connection = MysqlConnector::getInstance()->getConnection();
        if (empty($query))       throw new InvalidArgumentException('La requête ne doit pas être vide');
        if (!is_string($query))  throw new InvalidArgumentException('La requête doit être une chaîne de caractères');

        $query = trim($query);
        if (!string_starts_with(strtolower($query), strtolower("SELECT")))
            throw new InvalidArgumentException('La requête n\'est pas une requête "SELECT"');

        try {
            $stmt = $connection->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new PDOException('Erreur dans CRUDManager.selectFromSQLRaw : ' . $e->getMessage());
        }
    }

    /**
     * Cette méthode permet d'ajouter une ligne de donnée dans une table.
     *
     * @param  PDO|null $connection   La connexion PDO à la base de données.
     *                                Si non spécifié, utilise la connexion par défaut.
     * @param  string   $tableName    Le nom de la table dont on veut ajouter une ligne.
     * @param  array    $data         Les données à insérer.
     * @return bool                   True si l'opération s'est bien passée, false sinon.
     * @throws Exception|PDOException
     */
    public static function add($connection, $tableName, $data)
    {
        if (!isset($connection)) $connection = MysqlConnector::getInstance()->getConnection();
        if (empty($tableName))      throw new InvalidArgumentException('Le nom de la table ne doit pas être vide');
        if (!is_string($tableName)) throw new InvalidArgumentException('Le nom de la table doit être une chaîne de caractères');

        self::verifyDataForDbOperations($data);

        $tableName = trim($tableName);

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        try {
            $stmt = $connection->prepare("INSERT INTO $tableName ($columns) VALUES ($values)");
            foreach ($data as $key => $value) $stmt->bindValue(":$key", $value);

            return $stmt->execute();
        } catch (PDOException $e) {
           throw new PDOException('Erreur dans CRUDManager.add : ' . $e->getMessage());
        }
    }

    /**
     * Permet de mettre à jour les données d'une table.
     *
     * @param  PDO|null $connection     La connexion PDO à la base de données.
     *                                  Si non spécifié, utilise la connexion par défaut.
     * @param  string   $tableName      Le nom de la table à mettre à jour.
     * @param  array    $data           Les nouvelles données à utiliser pour la mise à jour.
     * @param  string   $whereCondition La condition pour filtrer les lignes à mettre à jour.
     * @return bool                     True si l'opération s'est bien passée, false sinon.
     * @throws Exception|PDOException
     */
    public static function update($connection, $tableName, $data, $whereCondition)
    {
        if (!isset($connection)) $connection = MysqlConnector::getInstance()->getConnection();
        if (empty($tableName))      throw new InvalidArgumentException('Le nom de la table ne doit pas être vide');
        if (!is_string($tableName)) throw new InvalidArgumentException('Le nom de la table doit être une chaîne de caractères');

        if (empty($whereCondition))      throw new InvalidArgumentException('La condition d\'update ne doit pas être vide');
        if (!is_string($whereCondition)) throw new InvalidArgumentException('La condition d\'update doit être une chaîne de caractères');

        self::verifyDataForDbOperations($data);

        $tableName = trim($tableName);

        $setClause = implode(
            ', ',
            array_map(
                function ($key) {
                    return "$key = :$key";
                },
                array_keys($data)
            )
        );
        try {
            $stmt = $connection->prepare("UPDATE $tableName SET $setClause WHERE $whereCondition");
            foreach ($data as $key => $value) $stmt->bindValue(":$key", $value);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException('Erreur dans CRUDManager.update : ' . $e->getMessage());
        }
    }

    /**
     * Cette fonction permet de supprimer des données d'une table.
     *
     * @param  PDO|null     $connection     La connexion PDO à la base de données.
     *                                      Si non spécifié, utilise la connexion par défaut.
     * @param  string       $tableName      Le nom de la table à partir de laquelle supprimer les données.
     * @param  string       $whereCondition La condition pour filtrer les lignes à supprimer.
     * @return bool                         True si l'opération s'est bien passée, false sinon.
     * @throws PDOException
     */
    public static function delete($connection, $tableName, $whereCondition)
    {
        if (!isset($connection)) $connection = MysqlConnector::getInstance()->getConnection();
        if (empty($tableName))      throw new InvalidArgumentException('Le nom de la table ne doit pas être vide');
        if (!is_string($tableName)) throw new InvalidArgumentException('Le nom de la table doit être une chaîne de caractères');

        if (empty($whereCondition))      throw new InvalidArgumentException('La condition de delete ne doit pas être vide');
        if (!is_string($whereCondition)) throw new InvalidArgumentException('La condition de delete doit être une chaîne de caractères');

        $tableName = trim($tableName);

        try {
            $stmt = $connection->prepare("DELETE FROM $tableName WHERE $whereCondition");

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException('Erreur dans CRUDManager.delete : ' . $e->getMessage());
        }
    }

    /**
     * Cette fonction vérifie les données mises en paramètres pour les fonctions add et update.
     *
     * @param mixed $data Doit être impérativement un tableau associatif de profondeur 1  de la forme :
     *                    $data['nom_de_colonne'].
     * @return void
     * @throws Exception
     */
    private static function verifyDataForDbOperations($data)
    {
        if (empty($data))                 throw new Exception('$data ne doit pas être vide');
        if (!is_array($data))             throw new Exception('$data doit être un tableau');

        if (get_array_depth($data) !== 1) throw new Exception('$data doit être un tableau de profondeur 1');
        foreach ($data as $key => $value)
            if (!is_string($key))         throw new Exception('$data n\'est pas de la forme : $data[\'nom_de_colonne\']');
    }
}
