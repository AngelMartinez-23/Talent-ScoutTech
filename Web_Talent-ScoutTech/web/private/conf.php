<?php
// Ruta segura a la base de datos
$dbFile = dirname(__FILE__) . "/database.db";

// Asegúrate de que el archivo de la base de datos no sea accesible desde el navegador
if (!file_exists($dbFile)) {
    // Si la base de datos no existe, deberías crearla o manejar el error de alguna forma.
    // Por ejemplo, podría ser útil mostrar un mensaje personalizado o escribir en un archivo de log.
    error_log("Database file not found at: $dbFile", 0); // Registrar el error
    die("Database file not found.");
}

// Conexión a la base de datos SQLite
$db = new SQLite3($dbFile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);

// Manejo adecuado de errores sin exponer detalles
if (!$db) {
    // En lugar de usar 'die()', se recomienda un manejo adecuado de errores
    // Ejemplo de cómo podrías manejarlo de manera más segura:
    error_log("Unable to open database: $dbFile", 0);
    die("Error de conexión con la base de datos.");
}

// Opcional: Configuración de manejo de errores en SQLite
$db->enableExceptions(true); // Para lanzar excepciones en caso de errores en consultas

?>
