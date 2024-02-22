<?php

/*
 * L'autoload automatise le chargement des classes enregistrées, évitant ainsi
 * l'inclusion manuelle de chaque fichier de classe.
 *
 * La fonction d'autoload ci-dessous convertit les namespaces en chemins de fichiers, charge
 * automatiquement le fichier correspondant, et signale les erreurs si nécessaire.
 *
 * En résumé, l'autoload simplifie la gestion des dépendances de classes et améliore
 * l'efficacité du chargement, tout en fournissant une syntaxe conviviale avec des alias.
 */

/*
 * Alias pour les namespaces
 *
 * MADEV fait référence aux classes du framework (situées dans le dossier system).
 * Application   fait référence aux classes de l’application de l’utilisateur (situées dans le dossier app).
 *
 * C'est-à-dire que toutes les classes doivent appartenir aux namespaces MADEV ou Application.
 */
const ALIASES = [
    'MADEV'   => 'system',
    'App'     => 'app'
];

spl_autoload_register(
/**
 * @throws Exception
 */ function ($class) {
    /*
     * S'il y a instance de la classe Foo\Bar\Demo par exemple,
     * alors la variable $namespaceParts contiendra ['Foo', 'Bar', 'Demo']
     */
    $namespaceParts = explode('\\', $class);

    if (count($namespaceParts) === 1)
         throw new Exception("La classe \"$class\" doit appartenir aux namespaces \"MADEV\" ou \"Application\"");

    if (in_array($namespaceParts[0], array_keys(ALIASES))) $namespaceParts[0] = ALIASES[$namespaceParts[0]];
    else throw new Exception("Namespace \"$namespaceParts[0]\" invalide. Un namespace doit commencer par : \"MADEV\" ou \"Application\"");

    /*
     * Si une classe se situe dans le namespace Application\Models\Entity,
     * on s’attend à ce qu’elle se situe dans les dossiers app/Models/Entity
     */
    $filePath = BASE_PATH . implode('/', $namespaceParts) . '.php';
    if (!file_exists($filePath))
        throw new Exception("Fichier \"$filePath\" introuvable pour la classe \"$class\". Vérifier le chemin, le nom de la classe ou le namespace");

    require_once $filePath;
});
