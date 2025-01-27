<?php
require_once dirname(__FILE__) . '/conf.php';

$userId = FALSE;

// Función para verificar si el usuario y la contraseña son válidos
function areUserAndPasswordValid($user, $password) {
    global $db, $userId;

    // Usar consultas preparadas para prevenir inyección SQL
    $query = $db->prepare('SELECT userId, password FROM users WHERE username = :username');
    $query->bindValue(':username', $user, SQLITE3_TEXT);
    $result = $query->execute();

    $row = $result->fetchArray(SQLITE3_ASSOC);

    if(!isset($row['password'])) return FALSE;

    // Verificar la contraseña usando password_verify para evitar el uso de contraseñas en texto claro
    if (password_verify($password, $row['password'])) {
        $userId = $row['userId'];

        // Regenerar el ID de sesión para evitar fijación de sesión
        session_regenerate_id(true);

        // Establecer cookies seguras
        setcookie('userId', $userId, time() + 3600, '/', '', true, true); // Cookies seguras: HttpOnly, Secure, SameSite
        setcookie('user', $user, time() + 3600, '/', '', true, true);

        return TRUE;
    } else {
        return FALSE;
    }
}

// Al iniciar sesión
if (isset($_POST['username'])) {
    $_COOKIE['user'] = $_POST['username'];
    if (isset($_POST['password']))
        $_COOKIE['password'] = $_POST['password'];
    else
        $_COOKIE['password'] = "";
} else {
    // Si no se encuentra usuario ni cookies, vaciar las cookies
    if (!isset($_POST['Logout']) && !isset($_COOKIE['user'])) {
        $_COOKIE['user'] = "";
        $_COOKIE['password'] = "";
    }
}

// Al hacer logout
if (isset($_POST['Logout'])) {
    // Borrar cookies de sesión
    setcookie('user', '', time() - 3600, '/');
    setcookie('password', '', time() - 3600, '/');
    setcookie('userId', '', time() - 3600, '/');

    unset($_COOKIE['user']);
    unset($_COOKIE['password']);
    unset($_COOKIE['userId']);

    header("Location: index.php");
    exit;
}

// Verificación del usuario y la contraseña si las cookies están presentes
if (isset($_COOKIE['user']) && isset($_COOKIE['password'])) {
    if (areUserAndPasswordValid($_COOKIE['user'], $_COOKIE['password'])) {
        $login_ok = TRUE;
        $error = "";
    } else {
        $login_ok = FALSE;
        $error = "Invalid user or password.<br>";
    }
} else {
    $login_ok = FALSE;
    $error = "This page requires you to be logged in.<br>";
}

if ($login_ok == FALSE) {
?>
    <!doctype html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="css/style.css">
        <title>Práctica RA3 - Authentication page</title>
    </head>
    <body>
    <header class="auth">
        <h1>Authentication page</h1>
    </header>
    <section class="auth">
        <div class="message">
            <?= $error ?>
        </div>
        <section>
            <div>
                <h2>Login</h2>
                <form action="#" method="post">
                    <label>User</label>
                    <input type="text" name="username" required><br>
                    <label>Password</label>
                    <input type="password" name="password" required><br>
                    <input type="submit" value="Login">
                </form>
            </div>

            <div>
                <h2>Logout</h2>
                <form action="#" method="post">
                    <input type="submit" name="Logout" value="Logout">
            </div>
        </section>
    </section>
    <footer>
        <h4>Puesta en producción segura</h4>
        <Please <a href="http://www.donate.co?amount=100&amp;destination=ACMEScouting/"> donate</a> >
    </footer>
    </body>
    </html>
<?php
    exit(0);
}

setcookie('user', $_COOKIE['user']);
setcookie('password', $_COOKIE['password']);
?>
