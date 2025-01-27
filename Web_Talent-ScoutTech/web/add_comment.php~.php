<?php
// Incluir archivo de configuración de la base de datos
require_once dirname(__FILE__) . '/private/conf.php';

// Incluir archivo de autenticación para verificar si el usuario está logueado
require dirname(__FILE__) . '/private/auth.php';

// Verificar que el usuario esté autenticado antes de permitir la creación de comentarios
if (!isset($_COOKIE['userId'])) {
    die("You must be logged in to comment.");
}

// Comprobar si el formulario de comentario ha sido enviado y si existe el parámetro 'id' en la URL
if (isset($_POST['body']) && isset($_GET['id'])) {
    // Obtener el cuerpo del comentario del formulario
    $body = $_POST['body'];

    // Prevenir ataques XSS escapando los caracteres especiales (como <, >, &, etc.) en el contenido
    $body = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');

    // Preparar la consulta SQL para evitar inyección SQL
    // Usamos una consulta preparada para insertar el comentario en la base de datos
    $stmt = $db->prepare("INSERT INTO comments (playerId, userId, body) VALUES (:playerId, :userId, :body)");
    $stmt->bindValue(':playerId', $_GET['id'], SQLITE3_INTEGER);  // Enlazar el ID del jugador
    $stmt->bindValue(':userId', $_COOKIE['userId'], SQLITE3_INTEGER);  // Enlazar el ID del usuario (que debe estar en la cookie)
    $stmt->bindValue(':body', $body, SQLITE3_TEXT);  // Enlazar el contenido del comentario

    // Ejecutar la consulta SQL
    if ($stmt->execute()) {
        // Redirigir al usuario a la página de lista de jugadores si el comentario se insertó correctamente
        header("Location: list_players.php");
        exit();
    } else {
        // Mostrar mensaje de error si ocurre un problema al insertar el comentario
        die("Error inserting comment.");
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <title>Práctica RA3 - Comments creator</title>
</head>
<body>
<header>
    <h1>Comments creator</h1>
</header>
<main class="player">
    <!-- Formulario para crear un nuevo comentario -->
    <form action="#" method="post">
        <h3>Write your comment</h3>
        <textarea name="body" required></textarea>  <!-- Campo para ingresar el comentario -->
        <input type="submit" value="Send">  <!-- Botón para enviar el comentario -->
    </form>

    <!-- Enlace para volver a la lista de jugadores y botón para cerrar sesión -->
    <form action="#" method="post" class="menu-form">
        <a href="list_players.php">Back to list</a>  <!-- Volver a la lista de jugadores -->
        <input type="submit" name="Logout" value="Logout" class="logout">  <!-- Botón para cerrar sesión -->
    </form>
</main>
<footer class="listado">
    <img src="images/logo-iesra-cadiz-color-blanco.png">
    <h4>Puesta en producción segura</h4>
    <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
</footer>
</body>
</html>
