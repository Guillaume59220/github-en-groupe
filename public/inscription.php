<?php
session_start(); // récupération de la session

$id_dresseur = $_SESSION['id'] ?? null;


// Fonctions de bases
require_once('../resources/function.php');

$errors = [];

$form_errors = [];

if (!$db = connexion($errors))
  die("Erreur(s) lors de la connexion : " . implode($errors));

/* Étape 2 : 
 
Créer un formulaire permettant d’ajouter un film et effectuer les vérifications nécessaires. 
 
Prérequis : 
● Les champs “titre, nom du réalisateur, acteurs, producteur et synopsis” comporteront au minimum 5 caractères. 
● Les champs : année de production, langue, category, seront obligatoirement un menu déroulant 
● Le lien de la bande annonce sera obligatoirement une URL valide 
● En cas d’erreurs de saisie, des messages d’erreurs seront affichés en rouge 
 
Chaque film sera ajouté à la base de données créée. Un message de réussite confirmera l’ajout du film.*/ 
// Validation du formulaire d'insertion
if (formIsSubmit('insertMovies')) {
  // Récupération des variables

/* J'ai choisi arbitrairement la longueur des VARCHAR. Je testerai cette longueur à ne pas dépasser dans la saisie. J'ai supposé que nous ne sommes pas encore arrivé au 100 ème siecle et que l'année de production du film s'écrit avec 4 chiffres donc dernière année 9999.

Rappel : structure du script sql de la création de la table movies :
CREATE TABLE movies (
  ID INTEGER NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,    // titre de 5 caractères minimum et de 255 maxi   
  actors VARCHAR(255) NOT NULL,   // acteurs de 5 caractères minimum et de 255 maxi
  director VARCHAR(50) NOT NULL,  // réalisateur de 5 caractères minimum et de 50 maxi
  producer VARCHAR(50) NOT NULL,  // producteur de 5 caractères minimum et de 50 maxi
  year_of_prod YEAR NOT NULL,
  language VARCHAR(50) NOT NULL,
  category ENUM ('classique', 'western', 'drame', 'horreur', 'science fiction', 'documentaire'),
  storyline TEXT(765) NOT NULL,   // synopsis de 5 caractères minimum et de 765 maxi
  video VARCHAR(255) NOT NULL
  PRIMARY KEY(ID)

*/

  $title = $_POST['title'];
  $actors = $_POST['actors'];
  $director = $_POST['director'];
  $producer = $_POST['producer'];
  $year_of_prod = $_POST['year_of_prod'];
  $language = $_POST['language'];
  $category = $_POST['category'];
  $storyline = $_POST['storyline'];
  $video = $_POST['video'];

  // Validation
  if (empty($title)) {
    $form_errors['title'] = "Le titre du film doit être renseigné";
  } elseif (strlen($title) > 255) {
    $form_errors['title'] = "Le titre dois faire 255 caractères maximum";
  } elseif (strlen($title) < 5) {
    $form_errors['title'] = "Le titre doit faire 5 caractères minimum";
  }

  if (empty($actors)) {
    $form_errors['actors'] = "Les noms des acteurs doivent être renseignés";
  } elseif (strlen($actors) > 255) {
    $form_errors['actors'] = "Le nom d'un acteur doit faire 255 caractères maximum";
  } elseif (strlen($actors) < 5) {
    $form_errors['actors'] = "Les noms des acteurs doivent faire 5 caractères minimum";
  }

  if (empty($director)) {
    $form_errors['director'] = "Le nom du réalisateur doit être renseigné";
  } elseif (strlen($director) > 255) {
    $form_errors['director'] = "Le nom du réalisateur doit faire 255 caractères maximum";
  } elseif (strlen($director) < 5) {
    $form_errors['director'] = "Le nom du réalisateur doit faire 5 caractères minimum";
  }

  if (empty($producer)) {
    $form_errors['producer'] = "Le nom du producteur doit être renseigné";
  } elseif (strlen($producer) > 255) {
    $form_errors['producer'] = "Le nom du producteur doit faire 255 caractères maximum";
  } elseif (strlen($producer) < 5) {
    $form_errors['producer'] = "Le nom du producteur doit faire 5 caractères minimum";
  }

  if (empty($year_of_prod)) {
    $form_errors['year_of_prod'] = "L'année de production du film doit être renseignée et doit être au format de 4 chiffres, exemple 2012";
  } elseif (strlen($year_of_prod) !=4) {
    $form_errors['year_of_prod'] = "L'année de production du film doit être au format de 4 chiffres, exemple 2012";
  }

  if (empty($language)) {
    $form_errors['language'] = "La langue doit être renseigné";
  } elseif (strlen($language) > 50) {
    $form_errors['language'] = "La langue doit faire 50 caractères maximum";
  }
    
  if (empty($category)) {
    $form_errors['category'] = "La categorie du film doit être sélectionnée";
  } 

  if (empty($storyline)) {
    $form_errors['storyline'] = "Le synopsis doit être renseigné";
  } elseif (strlen($storyline) > 765) {
    $form_errors['storyline'] = "Le synopsis doit faire 765 caractères maximum";
  } elseif (strlen($storyline) < 5) {
    $form_errors['storyline'] = "Le synopsis doit faire 5 caractères minimum";
  }

  if (empty($video)) {
    $form_errors['video'] = "Le lien internet (url) de la vidéo doit être renseigné";
  } elseif (strlen($video) > 255) {
    $form_errors['video'] = "Le lien internet (url) de la vidéo doit faire 255 caractères maximum";
  }

  if (count($form_errors) == 0 && isset($db)) {
    $query = $db->prepare("
      INSERT INTO movies (title, actors, director, producer, year_of_prod, language, category, storyline, video)
        VALUES           (:title, :actors, :director, :producer, :year_of_prod, :language, :category, :storyline, :video)
    ");

    $query->bindParam(':title', $title, PDO::PARAM_STR);
    $query->bindParam(':actors', $actors, PDO::PARAM_STR);
    $query->bindParam(':director', $director, PDO::PARAM_STR);
    $query->bindParam(':producer', $producer, PDO::PARAM_STR);
    $query->bindParam(':year_of_prod', $year_of_prod, PDO::PARAM_STR);
    $query->bindParam(':language', $language, PDO::PARAM_STR);
    $query->bindParam(':category', $category, PDO::PARAM_STR);
    $query->bindParam(':storyline', $storyline, PDO::PARAM_STR);
    $query->bindParam(':video', $video, PDO::PARAM_STR);
       
    // exécution de la requête préparée
    try {
      null;
      $query->execute();
    } catch(PDOException $e) {
      // Il y a eu une erreur
      var_dump($e);
    }

    /* Si il n'y a pas d'erreur ET que la connexion à la base de données (BDD) est correcte ET qu'il n'y a pas d'erreurs d'exception alors le film est bien inséré dans la BDD et on affiche le message de confirmation via echo */   
    if(count($form_errors) == 0 && isset($db) && !isset($e))
    {
      echo ('Insertion de votre film correctement effectuée dans la base de données.');
    }
  }
}
?>


<!-- formulaire -->
<div class="container">
  <div class="row align-items-center">
    <div class="col-xs-12 col-sm-8">
      <form method="post" id="insertMovies" enctype="multipart/form-data">
        <input type="hidden" name="insertMovies" value="1"/>
        <div class="form-control">
          <div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="title">Titre du film</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?php echo isset($_POST['title']) ? $_POST['title'] : '' ?>">
                <?php echo isset($form_errors['title']) ? '<div class="invalid-feedback">' . $form_errors['title'] . '</div>' : '' ?>
              </div>
            
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="actors">Noms des acteurs</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['actors']) ? 'is-invalid' : '' ?>" id="actors" name="actors" value="<?php echo isset($_POST['actors']) ? $_POST['actors'] : '' ?>">
                <?php echo isset($form_errors['actors']) ? '<div class="invalid-feedback">' . $form_errors['actors'] . '</div>' : '' ?>
              </div>
            </div>
            
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="director">Nom du réalisateur</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['director']) ? 'is-invalid' : '' ?>" id="director" name="director" value="<?php echo isset($_POST['director']) ? $_POST['director'] : '' ?>">
                <?php echo isset($form_errors['director']) ? '<div class="invalid-feedback">' . $form_errors['director'] . '</div>' : '' ?>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="producer">Nom du producteur</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['producer']) ? 'is-invalid' : '' ?>" id="producer" name="producer" value="<?php echo isset($_POST['producer']) ? $_POST['producer'] : '' ?>">
                <?php echo isset($form_errors['language']) ? '<div class="invalid-feedback">' . $form_errors['language'] . '</div>' : '' ?>
              </div>
            </div>
            
            <!-- rappel avec exemple d'année 2012 -->
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="year_of_prod">Année de production du film</label>
              <div class="col-sm-9">
                <input placeholder="Exemple 2012" type="text" class="form-control <?php echo isset($form_errors['year_of_prod']) ? 'is-invalid' : '' ?>" id="year_of_prod" name="year_of_prod" value="<?php echo isset($_POST['year_of_prod']) ? $_POST['year_of_prod'] : '' ?>">
                <?php echo isset($form_errors['year_of_prod']) ? '<div class="invalid-feedback">' . $form_errors['year_of_prod'] . '</div>' : '' ?>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="language">Langue du film</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['language']) ? 'is-invalid' : '' ?>" id="language" name="language" value="<?php echo isset($_POST['language']) ? $_POST['language'] : '' ?>">
                <?php echo isset($form_errors['language']) ? '<div class="invalid-feedback">' . $form_errors['language'] . '</div>' : '' ?>
              </div>
            </div>
           
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="category"></label>
              <div class="col-sm-9">
                <label for="title">Catégorie du film</label>
                <SELECT id="category" name="category" size="1" type="text" class="form-control <?php echo isset($form_errors['category']) ? 'is-invalid' : '' ?>" id="category" name="category" value="<?php echo isset($_POST['category']) ? $_POST['category'] : '' ?>">
                  <option value="">Sélectionnez</option>
                  <option>classique</option>
                  <option>western</option>
                  <option>drame</option>
                  <option>horreur</option>
                  <option>science fiction</option>
                  <option>documentaire</option>
                </SELECT>
                <?php echo isset($form_errors['category']) ? '<div class="invalid-feedback">' . $form_errors['category'] . '</div>' : '' ?>
              </div>
            </div>  

            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="storyline">Synopsis (résumé) du film</label>
              <div class="col-sm-9">
                <input type="textarea" class="form-control <?php echo isset($form_errors['storyline']) ? 'is-invalid' : '' ?>" id="storyline" name="storyline" value="<?php echo isset($_POST['storyline']) ? $_POST['storyline'] : '' ?>">
                <?php echo isset($form_errors['storyline']) ? '<div class="invalid-feedback">' . $form_errors['storyline'] . '</div>' : '' ?>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="video">Lien internet (url) de la vidéo du film</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['video']) ? 'is-invalid' : '' ?>" id="video" name="video" value="<?php echo isset($_POST['video']) ? $_POST['video'] : '' ?>">
                <?php echo isset($form_errors['video']) ? '<div class="invalid-feedback">' . $form_errors['video'] . '</div>' : '' ?>
              </div>
            </div>
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Valider</button>
            <button onclick="window.location.href = './'; return false;" class="btn btn-secondary">Annuler</button>
          </div>
        </div>
      </form>
    </div><!-- Col -->
  </div> <!-- Row -->

  <div class="text-center">
    <?php
      if (count($errors) > 0)
        echo "<p>" . implode("</p><p>", $errors) . "</p>";
    ?>
  </div>
</div> <!-- Container -->

<?php
// Fin du HTML

