<?php
require_once dirname(__FILE__) . '/private/conf.php';  // Conexión a la base de datos

// No es necesario tener autenticación en este caso, ya que es el formulario de registro
// require dirname(__FILE__) . '/private/auth.php';

if (isset($_POST['username']) && isset($_POST['password'])) {
    // Al recibir los datos del formulario, primero los sanitizamos y escapamos.
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Utilizamos funciones para sanitizar las entradas y prevenir inyecciones SQL
    $username = SQLite3::escapeString($username);
    $password = SQLite3::escapeString($password);

    // Asegúrate de no almacenar la contraseña en texto claro en la base de datos. Usamos hash para mayor seguridad.
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);  // Hashing de la contraseña

    // Realizamos la consulta para insertar el usuario en la base de datos
    $query = "INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')";

    // Ejecutamos la consulta
    $db->query($query) or die("Invalid query");

    // Redirigimos al usuario a la página de listado de jugadores después del registro
    header("Location: list_players.php");
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">  <!-- Establece la codificación de caracteres -->
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">  <!-- Estilo de la página -->
    <title>Práctica RA3 - Register</title>  <!-- Título de la página -->
</head>
<body>
    <header>
        <h1>Register</h1>  <!-- Título de la sección -->
    </header>
    <main class="player">
        <form action="#" method="post">
            <!-- Formulario de registro de usuario -->
            <label>Username:</label>
            <input type="text" name="username" required>  <!-- Campo de texto para el nombre de usuario -->
            
            <label>Password:</label>
            <input type="password" name="password" required>  <!-- Campo de contraseña -->

            <input type="submit" value="Send">  <!-- Botón para enviar el formulario -->
        </form>

        <!-- Formulario de navegación para regresar a la lista de jugadores o hacer logout -->
        <form action="#" method="post" class="menu-form">
            <a href="list_players.php">Back to list</a>  <!-- Enlace para regresar al listado de jugadores -->
            <input type="submit" name="Logout" value="Logout" class="logout">  <!-- Botón para cerrar sesión -->
        </form>
    </main>

    <footer class="listado">
        <img src="images/logo-iesra-cadiz-color-blanco.png">  <!-- Logo -->
        <h4>Puesta en producción segura</h4>
        <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >  <!-- Enlace de donación -->
    </footer>
</body>
</html>
