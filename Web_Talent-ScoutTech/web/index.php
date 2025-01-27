<?php
    // Al hacer logout, eliminamos las cookies del usuario
    if (isset($_POST['Logout'])) {
        // Eliminamos las cookies relacionadas con la autenticación
        setcookie('user', FALSE, time() - 3600); // Establecemos un tiempo pasado para eliminar la cookie
        setcookie('password', FALSE, time() - 3600); 
        setcookie('userId', FALSE, time() - 3600);

        // También eliminamos las cookies del array global $_COOKIE
        unset($_COOKIE['user']);
        unset($_COOKIE['password']);
        unset($_COOKIE['userId']);

        // Redirigimos al usuario de nuevo a la página de inicio
        header("Location: index.php");
        exit; // Es importante añadir un 'exit' después de un header para evitar que el código posterior se ejecute
    }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">  <!-- Asegura la correcta visualización de caracteres -->
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">  <!-- Vinculamos el archivo de estilos -->
    <title>Práctica RA3</title>
</head>
<body>
    <header>
        <h1>Developers Awards</h1>  <!-- Título de la página -->
    </header>
    <main>
        <h2><a href="insert_player.php"> Add a new player</a></h2> <!-- Enlace para agregar un nuevo jugador -->
        <h2><a href="list_players.php"> List of players</a></h2>    <!-- Enlace para ver la lista de jugadores -->
        <h2><a href="buscador.html"> Search a player</a></h2>      <!-- Enlace para buscar jugadores -->
    </main>

    <!-- Formulario de logout -->
    <form action="#" method="post" class="menu-form">
        <input type="submit" name="Logout" value="Logout" class="logout">  <!-- Botón de logout -->
    </form>

    <footer>
        <h4>Puesta en producción segura</h4>
        <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >  <!-- Enlace de donación -->
    </footer>
</body>
</html>
