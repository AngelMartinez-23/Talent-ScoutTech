<?php
require_once dirname(__FILE__) . '/private/conf.php';  // Conexión a la base de datos

// Requiere que el usuario esté autenticado
require dirname(__FILE__) . '/private/auth.php';

// Iniciamos la consulta SQL para obtener los jugadores de la base de datos
$query = "SELECT playerid, name, team FROM players ORDER BY playerId DESC";  // Consulta para obtener los jugadores ordenados por ID

// Ejecutamos la consulta
$result = $db->query($query) or die("Invalid query");  // Si la consulta falla, mostramos un mensaje de error
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">  <!-- Establece la codificación de caracteres -->
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">  <!-- Hoja de estilo CSS -->
    <title>Práctica RA3 - Players list</title>  <!-- Título de la página -->
</head>
<body>
    <header class="listado">
        <h1>Players list</h1>  <!-- Título de la sección -->
    </header>
    <main class="listado">
        <section>
            <ul>
                <?php
                // Iteramos sobre los resultados obtenidos de la consulta
                while ($row = $result->fetchArray()) {
                    // Imprimimos cada jugador en una lista con su nombre, equipo y enlaces para ver/editar
                    echo "
                        <li>
                            <div>
                                <span>Name: " . htmlspecialchars($row['name']) . "</span>
                                <span>Team: " . htmlspecialchars($row['team']) . "</span>
                            </div>
                            <div>
                                <a href=\"show_comments.php?id=" . htmlspecialchars($row['playerid']) . "\">(show/add comments)</a> 
                                <a href=\"insert_player.php?id=" . htmlspecialchars($row['playerid']) . "\">(edit player)</a>
                            </div>
                        </li>\n";
                }
                ?>
            </ul>
            <!-- Enlaces para regresar al inicio o cerrar sesión -->
            <form action="#" method="post" class="menu-form">
                <a href="index.php">Back to home</a>
                <input type="submit" name="Logout" value="Logout" class="logout">  <!-- Botón de logout -->
            </form>
        </section>
    </main>
    <footer class="listado">
        <img src="images/logo-iesra-cadiz-color-blanco.png">  <!-- Logo -->
        <h4>Puesta en producción segura</h4>
        <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >  <!-- Enlace de donación -->
    </footer>
</body>
</html>
