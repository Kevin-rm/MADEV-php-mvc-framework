<?php

use MADEV\Core\Application;

/**
 * Vérifie si une chaîne commence par une sous-chaîne donnée.
 *
 * @param  string $haystack La chaîne à vérifier.
 * @param  string $needle   La sous-chaîne à rechercher.
 * @return bool             True si la chaîne commence par la sous-chaîne, false sinon.
 */
function string_starts_with($haystack, $needle) 
{
    if (!is_string($haystack) || !is_string($needle))
        throw new InvalidArgumentException('Les deux arguments de la fonction ' . __FUNCTION__ . ' doivent être des chaînes de caractères');

    return substr($haystack, 0, strlen($needle)) === $needle;
}

/**
 * Vérifie si une chaîne de caractères contient une sous-chaîne.
 *
 * @param  string $haystack La chaîne principale.
 * @param  string $needle   La sous-chaîne à rechercher.
 * @return bool             True si la sous-chaîne est trouvée, false sinon.
 */
function string_contains($haystack, $needle)
{
    if (!is_string($haystack) || !is_string($needle))
        throw new InvalidArgumentException('Les deux arguments de la fonction ' . __FUNCTION__ . ' doivent être des chaînes de caractères');

    return strpos($haystack, $needle) !== false;
}

/**
 * Vérifie si une chaîne donnée contient des balises HTML.
 *
 * @param  string $string La chaîne d'entrée à vérifier.
 * @return bool           True si la chaîne contient des balises HTML, false sinon.
 */
function string_contains_HTML($string)
{
    if (!is_string($string)) return false;

    return $string !== strip_tags($string);
}

/**
 * Vérifie si une chaîne de caractères ne contient aucun chiffre.
 *
 * @param  string $string La chaîne de caractères à vérifier.
 * @return bool           True si la chaîne ne contient aucun chiffre, false sinon.
 */
function is_string_without_digit($string)
{
    if (!is_string($string)) return false;

    return !preg_match('/[0-9]/', $string);
}

/**
 * Compare deux chaînes de caractères sans tenir compte de la casse.
 *
 * @param  string $string1 La première chaîne à comparer.
 * @param  string $string2 La deuxième chaîne à comparer.
 * @return int             0 si les 2 chaînes sont identiques,
 *                         un entier négatif si la première chaîne est considérée comme inférieure à la deuxième,
 *                         un entier positif si la première chaîne est considérée comme supérieure à la deuxième.
 */
function compare_string_ignore_case($string1, $string2) 
{
    if (!is_string($string1) || !is_string($string2))
        throw new InvalidArgumentException('Les deux arguments de la fonction ' . __FUNCTION__ . ' doivent être des chaînes de caractères');

    return strcmp(strtolower($string1), strtolower($string2));
}

/**
 * Récupère la profondeur d'un tableau donné.
 *
 * @param  array $array Le tableau à vérifier.
 * @return int          La profondeur du tableau.
 */
function get_array_depth($array) 
{
    if (!is_array($array)) return 0;

    $max_depth = 1;
    foreach ($array as $value)
        if (is_array($value)) {
            $depth     = get_array_depth($value) + 1;
            $max_depth = max($max_depth, $depth);
        }

    return $max_depth;
}

/**
 * Vérifie si un tableau est associatif.
 *
 * @param  array $array Le tableau à vérifier.
 * @return bool         True si le tableau est associatif, false sinon.
 */
function is_associative_array($array)
{
    if      (!is_array($array) || !isset($array))              return false;
    foreach (array_keys($array) as $key) if (!is_string($key)) return false;

    return true;
}

/**
 * Obtient l'URL de base de l'application.
 *
 * @return string L'URL de base de l'application.
 */
function base_url()
{
    return Application::getInstance()->getBaseUrl();
}
