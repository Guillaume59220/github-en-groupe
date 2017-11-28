<?php

// Gestion de la base de donnée
require_once('database.php');

/*
 * formIsSubmit : test si un fomulaire a été soumis
 */
function formIsSubmit($form_name) {
  return (isset($_POST[$form_name]) ? $_POST[$form_name] : '0') === '1';
}

function getVal($value, $default = '') {
  return isset($value) ? $value : $default;
}

function genereTable($query) {
  $table = "";

  while ($result = $query->fetch()) {
    // Première ligne : affichage des titres de colonnes
    if ($table == "") {
      $table = "
  <table class=\"table table-hover table-responsive-sm\">
    <caption>Liste de tous les pokemons existants</caption>
    <thead>
      <tr>
        <th scope=\"col\">
        </th>
        <th scope=\"col\">
        " . implode('</th><th scope=\"col\">', array_keys($result)) . "
        </th>
      </tr>
    </thead>
    <tbody>
      ";
    }
    // Ajout d'une ligne dans la table
    $table .= "
      <tr>
        <td scope=\"row\">
          <a onclick=\"formSubmit('deletePokemon', 'id_delete', '" . $result['id'] . "');\"><i class=\"fa fa-trash-o fa-fw\" aria-hidden=\"true\"></i></a>
        </td>
        <td>
        " . implode('</td><td>', $result) . "
        </td>
      </tr>
    ";
  }

  if($table == "") {
    null;//$errors[] = "Aucune ligne trouvée";
  } else {
    $table .= "
    </tbody>
  </table>
    ";
  }

  return $table;
}

/*Créer une page listant dans un tableau HTML les films présents dans la base de données.  Ce tableau ne contiendra, par film, que le nom du film, le réalisateur et l’année de production. 
 Une colonne de ce tableau contiendra un lien ​« plus d’infos »
​  permettant de voir la fiche d’un film dans le détail. 
*/


function afficheFilms($limit = 20, $offset = 0, $search = '')  {
  // Connexion à la base
  if (!$db = connexion($msg))
    die("Erreur : " . implode($msg));

  // Affichage des pokemons
  if (!$query = $db->query('
  SELECT title, director, year_of_prod, 
    FROM movies
    WHERE 1 = 1 '
    . $search
    . ' LIMIT ' . $limit . ' OFFSET ' . $offset         
  )) {
       $errors[] = "Erreur lors de la création de la requête";
  }
   /* autre écriture possible
  if (!$query = $db->query("
  SELECT pokemon.id, numero, nom, experience, vie, defense, attaque
    FROM pokemon
    LIMIT $limit OFFSET $offset         
  ")) {
       $errors[] = "Erreur lors de la création de la requête";
  }*/

  echo genereTable($query);

}

?>
