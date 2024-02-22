<?php

use MADEV\Core\Routing\RouteCollection;

/*
 * ==================================================
 * FICHIER DE CONFIGURATION DES ROUTES AVEC PHP
 * ==================================================
 *
 * Ce fichier contient des définitions de routes pour l'application web en utilisant la classe RouteCollection.
 *
 * Les routes peuvent également être définies dans le fichier 'routes.json', mais celui-ci offre plus de flexibilité
 * car vous écrivez directement du code. De plus, vous n'êtes pas obligé d'associer une route à un contrôleur, vous avez
 * ainsi un contrôle total entre vos mains.
 *
 * Utilisez la classe RouteCollection pour définir vos différentes routes. Cette approche vous permet de gérer de manière plus
 * précise la navigation au sein de votre application.
 */

RouteCollection::get('/example/{id}/{slug}', function ($id, $slug) {
    return "This is an example $id : $slug";
})->where([
    'id' => '\d+' // \d+ signifie un ou plusieurs chiffres (0-9)
])->name('example');
