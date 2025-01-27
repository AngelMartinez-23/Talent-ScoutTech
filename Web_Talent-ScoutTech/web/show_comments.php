<?php
require_once dirname(__FILE__) . '/private/conf.php';  // Incluir configuración de la base de datos

// Requiere usuarios autenticados
require dirname(__FILE__) . '/private/auth.php';  

// Verificación de si 'id' está presente en la URL (GET) y es válido
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $playerId = $_GET['id'];  // Guardar el ID del jugador para usarlo más adelante

    // Consulta SQL para obtener los comentarios de un jugador específico
    $query = "SELECT commentId, username, body 
              FROM comments C
              JOIN users U ON U.userId = C.userId
              WHERE C.playerId = :playerId
              ORDER BY C.commentId DESC";  // Usamos un JOIN para obtener datos de usuarios

    // Preparar y ejecutar la consulta
    $stmt = $db->prepare($query);
    $stmt->bindValue(':playerId', $playerId, SQLITE3_INTEGER);
    $result = $stmt->execute();

    if ($result) {
        // Mostrar los comentarios
        while ($row = $result->fetchArray()) {
            echo "<div>
                    <h4>" . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "</h4>
                    <p>commented: " . nl2br(htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8')) . "</p>
                  </div>";
        }
    } else {
        echo "No comments found for this player.";
    }
} else {
    echo "Invalid player ID.";
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">  <!-- Vincula la hoja de estilos -->
    <title>Práctica RA3 - Comments editor</title>
</head>
<body>
<header>
    <h1>Comments editor</h1>  <!-- Título de la página -->
</header>
<main class="player">

<!-- Enlace para regresar a la lista de jugadores -->
<div>
    <a href="list_players.php">Back to list</a>
    <a class="black" href="add_comment.php?id=<?php echo $playerId;?>"> Add comment</a>  <!-- Enlace para agregar comentarios -->
</div>

</main>

<footer class="listado">
    <img src="images/logo-iesra-cadiz-color-blanco.png">  <!-- Logo -->
    <h4>Puesta en producción segura</h4>
    <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >  <!-- Enlace para donar -->
</footer>

</body>
</html>
