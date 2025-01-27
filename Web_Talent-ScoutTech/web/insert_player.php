<?php
require_once dirname(__FILE__) . '/private/conf.php';  // Conexión con la base de datos

// Requiere que el usuario esté autenticado
require dirname(__FILE__) . '/private/auth.php';

if (isset($_POST['name']) && isset($_POST['team'])) {
    // Si los datos fueron enviados a través del formulario, los procesamos
    $name = $_POST['name'];
    $team = $_POST['team'];

    // Preparamos la consulta SQL para insertar o modificar un jugador
    // Usamos consultas preparadas para evitar inyección SQL
    if (isset($_GET['id'])) {
        // Si existe un 'id' en la URL, modificamos el jugador
        $id = $_GET['id'];
        $query = "INSERT OR REPLACE INTO players (playerid, name, team) VALUES (:id, :name, :team)";
        $stmt = $db->prepare($query);  // Preparamos la consulta
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);   // Vinculamos el parámetro :id con el valor de la URL
    } else {
        // Si no existe un 'id', es una inserción nueva
        $query = "INSERT INTO players (name, team) VALUES (:name, :team)";
        $stmt = $db->prepare($query);  // Preparamos la consulta
    }

    // Vinculamos los valores de los parámetros
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':team', $team, SQLITE3_TEXT);

    // Ejecutamos la consulta de inserción o actualización
    $stmt->execute() or die("Invalid query");

} else {
    // Si no se ha enviado el formulario, mostramos la información del jugador a modificar (si existe 'id' en la URL)
    if (isset($_GET['id'])) {
        // Si se está editando un jugador, recuperamos sus datos desde la base de datos
        $id = $_GET['id'];
        $query = "SELECT name, team FROM players WHERE playerid = :id";
        $stmt = $db->prepare($query);  // Preparamos la consulta
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);  // Vinculamos el parámetro :id
        $result = $stmt->execute() or die ("$query");
        $row = $result->fetchArray() or die ("modifying a nonexistent player!");

        // Asignamos los valores a las variables
        $name = $row['name'];
        $team = $row['team'];
    }
}

// Formulario HTML para ingresar o modificar un jugador
?>
<!doctype html>
<html lang="es">
    <head>
        <meta charset="UTF-8">  <!-- Establece la codificación de caracteres -->
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="css/style.css">  <!-- Estilos de la página -->
        <title>Práctica RA3 - Players list</title>
    </head>
    <body>
        <header>
            <h1>Player</h1>  <!-- Título de la página -->
        </header>
        <main class="player">
            <!-- Formulario para ingresar o modificar un jugador -->
            <form action="#" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id ?? '') ?>"><br>  <!-- Valor oculto para el 'id' -->
                <h3>Player name</h3>
                <textarea name="name"><?= htmlspecialchars($name ?? '') ?></textarea><br>  <!-- Campo para el nombre del jugador -->
                <h3>Team name</h3>
                <textarea name="team"><?= htmlspecialchars($team ?? '') ?></textarea><br>  <!-- Campo para el nombre del equipo -->
                <input type="submit" value="Send"><br>  <!-- Botón de enviar -->
            </form>
            
            <!-- Enlaces para volver al inicio o a la lista de jugadores -->
            <form action="#" method="post" class="menu-form">
                <a href="index.php">Back to home</a>
                <a href="list_players.php">Back to list</a>
                <input type="submit" name="Logout" value="Logout" class="logout">  <!-- Botón de logout -->
            </form>
        </main>
        <footer class="listado">
            <img src="images/logo-iesra-cadiz-color-blanco.png">  <!-- Logo -->
            <h4>Puesta en producción segura</h4>
            <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >  <!-- Enlace de donación -->
        </footer>
    </body>
</html>
