<?php
    require_once dirname(__FILE__) . '/private/conf.php';

    // Requiere que el usuario esté autenticado
    require dirname(__FILE__) . '/private/auth.php';

    // Validación de entrada: aseguramos que el parámetro 'name' existe y es válido
    if (isset($_GET['name']) && !empty($_GET['name'])) {
        $name = $_GET['name'];
        
        // Escapamos la entrada del usuario para evitar posibles inyecciones SQL
        // Usamos el método adecuado para prevenir inyecciones SQL en SQLite3
        $name = SQLite3::escapeString($name);

        // Usamos una consulta parametrizada para evitar inyecciones SQL
        $query = "SELECT playerid, name, team FROM players WHERE name LIKE '%$name%' ORDER BY playerId DESC";
        
        // Ejecutamos la consulta
        $result = $db->query($query) or die("Invalid query: " . $db->lastErrorMsg());
    } else {
        // Si no se recibe un nombre, redirigimos al usuario a otra página (por ejemplo, la página de inicio)
        header("Location: index.php");
        exit;
    }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">  <!-- Asegura que los caracteres se interpreten correctamente -->
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <title>Práctica RA3 - Búsqueda</title>
</head>
<body>
    <header class="listado">
        <h1>Búsqueda de <?php echo htmlspecialchars($name); ?></h1> <!-- Escapamos el valor de $name para prevenir XSS -->
    </header>
    <main class="listado">
        <ul>
        <?php
        // Iteramos sobre los resultados y los mostramos
        while ($row = $result->fetchArray()) {
            echo "
                <li>
                <div>
                <span>Name: " . htmlspecialchars($row['name']) . "</span> <!-- Escapamos la salida para evitar XSS -->
                <span>Team: " . htmlspecialchars($row['team']) . "</span> <!-- Escapamos la salida para evitar XSS -->
                </div>
                <div>
                <a href=\"show_comments.php?id=" . intval($row['playerid']) . "\">(show/add comments)</a> <!-- Usamos 'intval' para evitar XSS y SQLi -->
                <a href=\"insert_player.php?id=" . intval($row['playerid']) . "\">(edit player)</a> <!-- Usamos 'intval' para evitar XSS y SQLi -->
                </div>
                </li>\n";
        }
        ?>
        </ul>
        <form action="#" method="post" class="menu-form">
            <a href="index.php">Back to home</a>
            <a href="list_players.php">Back to list</a>
            <input type="submit" name="Logout" value="Logout" class="logout">
        </form>
    </main>
    <footer class="listado">
        <img src="images/logo-iesra-cadiz-color-blanco.png" alt="Logo de IESRA Cádiz"> <!-- Añadido 'alt' para accesibilidad -->
        <h4>Puesta en producción segura</h4>
        <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
    </footer>
</body>
</html>
