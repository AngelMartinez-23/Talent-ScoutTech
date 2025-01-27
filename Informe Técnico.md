# Informe Técnico - Talent ScoutTech 

# Índice 

[**Parte 1. SQLi**](#parte-1-sqli)

[**Parte 2. XSS**](#parte-2-xss)

[**Parte 3. Control de acceso, autenticación y sesiones de usuarios.**](#parte-3-control-de-acceso-autenticacion-y-sesiones-de-usuarios)

[**Parte 4. Servidores web**](#parte-4-servidores-web)

[**Parte 5. CSRF**](#parte-5-csrf)



<br>
<br>

# Parte 1. SQLi 

 A) Dad un ejemplo de combinación de usuario y contraseña que provoque un error en la consulta SQL generada por este formulario. A partir del mensaje de error obtenido, decide cuál es la consulta SQL que se ejecuta, cuál de los campos introducidos al formulario utiliza y cuál no.

| Escribe los valores… | “ |
| :---- | :---- |
| En el campo… | El campo en el formulario web que representa el “username”. |
| Del formulario de la página…. | La página correspondiente al formulario de autenticación, probablemente **login.php** o similar. |
| La consulta SQL que se ejecuta es… | SELECT userId, password FROM users WHERE username \= """ |
| Campos del formulario web utilizados en la consulta SQL…. | username |
| Campos del formulario web no utilizados en la consulta SQL… | Cualquier otro campo del formulario que no se pase en esta consulta (ejemplo: password, token). |

<br>

B) Gracias a la SQL Injection del apartado anterior, sabemos que este formulario es vulnerable y conocemos el nombre de los campos de la tabla “users”. Para tratar de impersonar a un usuario, nos hemos descargado un diccionario que contiene algunas de las contraseñas más utilizadas (se listan a continuación):

   1) password  
   2) 123456  
   3) 12345678  
   4) 1234  
   5) qwerty  
   6) 12345678  
   7) dragon

   Dad un ataque que, utilizando este diccionario, nos permita impresionar a un usuario de esta aplicación y acceder en nombre suyo. Tened en cuenta que no sabéis ni cuántos usuarios hay registrados en la aplicación, ni los nombres de estos.

| **Descripción**                           | **Valor**                        |
|-------------------------------------------|----------------------------------|
| **Explicación del ataque**                | El ataque consiste en explotar una vulnerabilidad de inyección SQL en el campo de usuario del formulario de inicio de sesión. Al introducir `" or password="1234" -- -`, se manipula la consulta SQL para ignorar la validación del nombre de usuario y verificar únicamente si la contraseña coincide con `1234`. Posteriormente, se realizan intentos con diferentes contraseñas del diccionario proporcionado. |
| **Campo de usuario con que el ataque ha tenido éxito** | `" or password="1234" -- -`      |
| **Campo de contraseña con que el ataque ha tenido éxito** | `1234`                            |

<br>

C) Si vais a *private/auth.php*, veréis que en la función areUserAndPasswordValid, se utiliza “*SQLite3::escapeString()*”, pero, aun así, el formulario es vulnerable a SQL Injections, explicar cuál es el error de programación de esta función y como lo podéis corregir.

| **Descripción**                                 | **Valor**|
|-------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Explicación del error**                       | La función `SQLite3::escapeString()` solo escapa caracteres especiales, pero la consulta aún es vulnerable porque se sigue construyendo con concatenación de cadenas, permitiendo inyecciones SQL.                                                |
| **Solución: Cambiar la línea con el código…**   | `$query = SQLite3::escapeString('SELECT userId, password FROM users WHERE username = "' . $user . '"');`|
| **…por la siguiente línea…**                    | `$stmt = $db->prepare('SELECT userId, password FROM users WHERE username = :username'); $stmt->bindValue(':username', $user, SQLITE3_TEXT); $result = $stmt->execute();`                                                                          |

<br>

D) Si habéis tenido éxito con el apartado b), os habéis autenticado utilizando el usuario **luis** (si no habéis tenido éxito, podéis utilizar la contraseña **1234** para realizar este apartado). Con el objetivo de mejorar la imagen de la jugadora *Candela Pacheco*, le queremos escribir un buen puñado de comentarios positivos, pero no los queremos hacer todos con la misma cuenta de usuario.

Para hacer esto, en primer lugar habéis hecho un ataque de fuerza bruta sobre el directorio del servidor web (por ejemplo, probando nombres de archivo) y habéis encontrado el archivo **add_comment.php\~**. Estos archivos seguramente se han creado como copia de seguridad al modificar el archivo “.*php*” original directamente al servidor. En general, los servidores web no interpretan (ejecuten) los archivos .php\~ sino que los muestran como archivos de texto sin interpretar.

Esto os permite estudiar el código fuente de **add_comment.php** y encontrar una vulnerabilidad para publicar mensajes en nombre de otros usuarios. ¿Cuál es esta vulnerabilidad, y cómo es el ataque que utilizar para explotarla?

| **Descripción**                                 | **Valor** |
|-------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Vulnerabilidad detectada**                   | La vulnerabilidad es la falta de validación y escape adecuado de las entradas del usuario, ya que los datos del comentario (`$_POST['body']`) se insertan directamente en la base de datos después de un simple escape con `SQLite3::escapeString()`. Esto podría permitir la inyección de código malicioso, como JavaScript o HTML. A pesar de que se usa `SQLite3::escapeString()`, esto no previene completamente los riesgos si se insertan datos no controlados en la base de datos. |
| **Descripción del ataque**                     | El ataque consiste en enviar un comentario malicioso en el formulario de comentarios, que al ser almacenado y luego mostrado, permite la ejecución de scripts o la manipulación de contenido en el navegador de otros usuarios. Esto puede incluir ataques como Cross-Site Scripting (XSS), donde se puede inyectar código HTML o JavaScript para robar información o hacer que el usuario realice acciones no deseadas.                         |
| **¿Cómo podemos hacer que sea segura esta entrada?** | Para hacer esta entrada segura, se debe sanear el contenido de los comentarios antes de almacenarlo y mostrarlo. Se puede implementar lo siguiente:                                           

<br>
<br>

# Parte 2. XSS

A) Para ver si hay un problema de XSS, crearemos un comentario que muestre una alerta de Javascript siempre que alguien consulte el/los comentarios de aquel jugador (show_comments.php). Dad un mensaje que genere un «alert»de Javascript al consultar el listado de mensajes.

| **Descripción**                                 | **Valor**                                                                                                                                                                     |
|-------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Introducir el mensaje…**                     | `<script>alert('XSS Vulnerability Test');</script>`                                                                                                                         |
| **En el formulario de la página…**             | En el formulario de la página donde se introduce el comentario, específicamente en el campo de texto para el cuerpo del comentario (`<textarea name="body">`).              |

<br>

B) ¿Por qué dice **\&amp**; cuando miráis un link (como el que aparece a la portada de esta aplicación pidiendo que realices un donativo) con parámetros GET dentro de código html si en realidad el link es sólo con "&" ?

| **Descripción**       | **Valor**                                                                                                                                                                      |
|-----------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **Explicación**       | El carácter `&` es especial en HTML y se utiliza para iniciar entidades HTML. Para evitar que el navegador lo interprete como el comienzo de una entidad, se debe escapar como `&amp;`. Esto asegura que el `&` se muestre correctamente en el navegador. Aunque se escribe como `&amp;` en el código, el navegador lo renderiza como un `&` normal. |

<br>

C) Explicar cuál es el problema de **show_comments.php**, y cómo lo arreglaría. Para resolver este apartado, podéis mirar el código fuente de esta página.

| **Descripción**                      | **Valor**                                                                                                                                                      |
|--------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------|
| **¿Cuál es el problema?**            | El problema es que los comentarios no son sanitizados antes de mostrarse en la página, lo que permite la inyección de código JavaScript (XSS). El contenido de los comentarios puede ejecutar scripts en el navegador del usuario. |
| **Sustituyo el código de la/las líneas…** | `echo $row['body'];`                                                                                                                                           |
| **…por el siguiente código…**        | `echo htmlspecialchars($row['body'], ENT_QUOTES, 'UTF-8');`                                                                                                   |

<br>

D) Descubrid si hay alguna otra página que esté afectada por esta misma vulnerabilidad. En caso positivo, explicar cómo lo habéis descubierto.

| Otras páginas afectadas… | buscador.html, insert\_player.php |
| :---- | :---- |
| ¿Cómo lo he descubierto? | 1\. **Revisión del código**: El formulario recibe datos del usuario (nombre y equipo) sin validación ni escape adecuado. 2\. **Probar un ataque XSS**: Introduce un payload como *\<script\>alert('XSS Test');\</script\>* en los campos y verifica si se ejecuta al mostrar los datos. |

<br>
<br>

# Parte 3. Control de acceso, autenticación y sesiones de usuarios.

1) **Medidas para asegurar el registro de usuario en register.php**  
     
   **Medidas necesarias:**

    - **Validación de datos de entrada**: Comprobar que el **username** y **password** no contengan caracteres peligrosos como **\<**, **\>**, etc. para prevenir ataques de XSS e inyecciones SQL.  
    - **Hash de contraseñas**: Las contraseñas no deben guardarse en texto claro. Es necesario aplicar un algoritmo de hash como bcrypt para almacenar las contraseñas de forma segura.  
    - **Validación de usuario único**: Comprobar que el **username** no exista previamente en la base de datos para evitar registros duplicados.


	**Justificación de las medidas:**

    La validación de entrada es importante para evitar que los datos maliciosos sean insertados en la base de datos o ejecutados en el navegador de los usuarios, lo cual podría generar vulnerabilidades como inyecciones SQL o ataques de XSS. 

    Al usar un algoritmo de hash seguro como bcrypt para las contraseñas, se garantiza que, en caso de que la base de datos sea comprometida, las contraseñas no puedan ser fácilmente descifradas. 

    La validación del nombre de usuario también es importante para prevenir la creación de cuentas duplicadas con el mismo **username**, lo que podría afectar la integridad y seguridad de la aplicación.

    **Implantación:**

        if (isset($_POST['username']) && isset($_POST['password'])) {  
            $username = $_POST['username'];  
            $password = $_POST['password'];

        // Validar que el username no contiene caracteres peligrosos  
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {  
            die("Invalid username format.");  
        }

        // Verificar que el username no exista ya en la base de datos  
        $query = "SELECT * FROM users WHERE username = '$username'";  
        $result = $db->query($query);  
        if ($result->fetchArray()) {  
            die("Username already exists.");  
        }

        // Hash de la contraseña  
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar en la base de datos  
        $query = "INSERT INTO users (username, password) VALUES ('$username', '$passwordHash')";  
        $db->query($query) or die("Invalid query");  
        header("Location: list_players.php");  
        }

<br>

2) **Medidas para asegurar el login en la aplicación**  
     
   **Medidas necesarias:**

    - **Uso de HTTPS**: Asegurarse de que la aplicación utilice HTTPS para cifrar las credenciales enviadas a través del formulario.  
    - **Validación de contraseña segura**: Comprobar la validez de la contraseña utilizando password\_verify() con el hash de la base de datos.  
    - **Protección contra ataques de fuerza bruta:** Implementar un sistema de protección como límites de intentos de inicio de sesión fallidos y temporizadores de bloqueo.  
    - **Uso de sesiones seguras:** Usar sesiones PHP con una gestión adecuada de cookies para asegurar la autenticidad y validez de la sesión.

    **Justificación de las medidas:**

    El uso de HTTPS es importante para evitar que los datos se transmiten en texto claro y sean interceptados por atacantes en la red. **password_verify()** permite validar las contraseñas de manera segura sin almacenar las contraseñas en texto claro. 

    La protección contra ataques de fuerza bruta es importante para evitar que un atacante pueda adivinar la contraseña mediante un ataque de diccionario o de prueba y error. La gestión adecuada de sesiones, que incluye el uso de cookies seguras, ayuda a evitar que las sesiones sean vulnerables a ataques como el secuestro de sesión.

    **Implementación**:

        if (isset($_POST['username']) && isset($_POST['password'])) {  
            $username = $_POST['username'];  
            $password = $_POST['password'];

            // Consultar el usuario en la base de datos  
            $query = "SELECT userId, password FROM users WHERE username = '$username'";  
            $result = $db->query($query);  
            $row = $result->fetchArray();

            // Verificar si la contraseña es correcta  
            if (password_verify($password, $row['password'])) {  
                $_COOKIE['userId'] = $row['userId'];  
                setcookie('userId', $row['userId'], time() + 3600, '/');  
                header("Location: list_players.php");  
            } else {  
                echo "Invalid credentials.";  
            }  
        }

<br>

3) **Medidas para restringir el acceso  a la página de registro**  
     
   **Medidas necesarias:**

    - **Requiere autenticación**: Solo permite el acceso a la página de registro a usuarios no autenticados.  
    - **Redirección:** Si el usuario está autenticado, redirigirlo a la página principal o de jugadores en lugar de permitir el acceso al registro.

    **Justificación de las medidas:**

    Es importante que la aplicación bloquee el acceso al registro de nuevos usuarios solo a aquellos que aún no están autenticados. Permitir que usuarios autenticados accedan al registro puede generar problemas de seguridad y violar los requisitos de la aplicación. Al redirigir automáticamente a los usuarios autenticados, se evita que accedan a la página de registro.

    **Implementación:**

        if (isset($\_COOKIE\['userId'\])) {  
            header("Location: list\_players.php");  
            exit();  
        }

<br>

4) **Medidas para proteger la carpeta private**  
     
   **Medidas necesarias:**

    - **Desactivar la navegación de directorios**: Asegurarse de que el servidor no permite listar los archivos de la carpeta private.  
    - **Restricción de acceso con .htaccess:** Utilizar un archivo .htaccess para evitar el acceso directo a la carpeta private.

    **Justificación de las medidas:**  
	  
    La carpeta **private** contiene archivos sensibles como configuraciones y scripts que no deben ser accesibles desde el navegador. Al desactivar la navegación de directorios y usar **.htaccess**, se previene que los atacantes puedan explorar y acceder a archivos internos de la aplicación.

    **Implementación** (agregar archivo .**htaccess** en la carpeta **private**):

        *Options -Indexes*

<br>

5) **Análisis de la seguridad de la sesión del usuario**  
     
   **Medidas necesarias:**

    - **Usar cookies seguras**: Asegurarse de que las cookies de sesión sean seguras, usando las banderas *HttpOnly*, *Secure* y *SameSite*.  
    - **Regenerar el ID de sesión**: Regenerar el ID de sesión tras un inicio de sesión exitoso para evitar ataques de fijación de sesión.  
    - **Tiempo de expiración de la sesión**: Implementar un tiempo de expiración de la sesión y una funcionalidad para que la sesión caduque después de un tiempo de inactividad.

    **Justificación de las medidas:**

    El uso de cookies seguras es esencial para evitar que las cookies sean accesibles desde el lado del cliente y reducir el riesgo de robo de sesión. Regenerar el ID de sesión minimiza los riesgos de ataques de fijación de sesión, en los cuales un atacante podría secuestrar una sesión válida. La caducidad de la sesión protege contra el abuso de sesiones activas, asegurando que una sesión no quede abierta indefinidamente.

    **Implementación:**  
		  
        // Iniciar sesión  
        session_start();  
        session_regenerate_id(true);

        // Configuración de cookies seguras  
        ini_set('session.cookie_secure', 1);  
        ini_set('session.cookie_httponly', 1);  
        ini_set('session.cookie_samesite', 'Strict');

        // Configuración de expiración de la sesión  
        if (!isset($_SESSION['LAST_ACTIVITY']) || (time() - $_SESSION['LAST_ACTIVITY']) > 1800) {  
            session_unset();     // Eliminar todas las variables de sesión  
            session_destroy();   // Destruir la sesión  
        }  
        $_SESSION['LAST_ACTIVITY'] = time();

<br>
<br>

# Parte 4. Servidores web

**¿Qué medidas de seguridad se implementarán en el servidor web para reducir el riesgo a ataques?**

**Configuración segura del servidor**

Es fundamental deshabilitar módulos y servicios innecesarios, ocultar información sobre la versión del servidor y desactivar la navegación por directorios. Estas acciones reducen la superficie de ataque y dificultan la identificación de vulnerabilidades por parte de posibles atacantes.

**Uso de HTTPS**

Implementar HTTPS con certificados TLS válidos protege el tráfico entre el cliente y el servidor, garantizando la confidencialidad y la integridad de los datos transmitidos. Esto es especialmente relevante para proteger información sensible como credenciales de usuario.

**Firewall de aplicaciones web (WAF)**

El uso de un WAF permite inspeccionar y filtrar tráfico malicioso en tiempo real, bloqueando amenazas comunes como inyecciones SQL, XSS y otras vulnerabilidades explotables en aplicaciones web.

**Manejo de permisos de archivos y directorios**

Es crucial configurar permisos estrictos para limitar el acceso a archivos y directorios. El servidor web debe ejecutarse con los menores privilegios posibles, asegurando que los usuarios no autorizados no puedan modificar archivos sensibles.

**Protección contra ataques de fuerza bruta**

Limitar el número de intentos fallidos de autenticación y bloquear temporalmente las IP que realicen intentos repetidos fallidos ayuda a prevenir accesos no autorizados al sistema.

**Seguridad del sistema operativo**

Mantener el sistema operativo actualizado, usar herramientas de detección de malware y configurar reglas de firewall son medidas esenciales para mantener un entorno seguro y evitar la explotación de vulnerabilidades conocidas.

**Seguridad de las cookies**

Configurar las cookies con opciones como HttpOnly, Secure y SameSite protege la información de sesión contra ataques como el secuestro de cookies y XSS, dificultando que sean interceptadas o manipuladas por terceros.

**Implementación de políticas de seguridad**

El uso de cabeceras HTTP, como Content-Security-Policy, Strict-Transport-Security o X-Frame-Options, permite establecer políticas de seguridad que limitan el acceso a recursos externos, refuerzan el uso de HTTPS y previenen ataques como el clickjacking.

**Registro y monitoreo**

Configurar el servidor para registrar actividades sospechosas y realizar monitoreo continuo permite detectar posibles intentos de ataque. Estas prácticas son fundamentales para identificar y responder rápidamente a comportamientos anómalos.

**Protección contra ataques DDoS**

El uso de servicios de mitigación DDoS y la configuración de límites de velocidad en las conexiones ayudan a evitar la sobrecarga del servidor y aseguran su disponibilidad frente a ataques de denegación de servicio.

**Despliegue de software seguro**

Separar los entornos de desarrollo y producción, junto con la realización de auditorías de seguridad periódicas, garantiza que solo se despliegue código seguro en el servidor, minimizando riesgos de explotación.

**Autenticación y control de acceso**

Implementar autenticación de múltiples factores y limitar el acceso a herramientas de administración mediante listas blancas de IP o VPN asegura que solo los usuarios autorizados puedan realizar cambios críticos en el servidor.

<br>
<br>

# Parte 5. CSRF

A) Editad un jugador para conseguir que, en el listado de jugadores **list_players.php** aparezca, debajo del nombre de su equipo y antes de **show/add** comments un botón llamado *Profile* que corresponda a un formulario que envíe a cualquiera que haga clic sobre este botón a esta dirección que hemos preparado.

| **En el campo…** | **URL a insertar** - http://web.pagos/donate.php?amount=100&receiver=attacker |
| :---- | :---- |
| **Introduzco…** | Un formulario con un botón llamado "Profile" que redirige a la URL maliciosa al hacer clic. |

B) Una vez lo tenéis terminado, pensáis que la eficacia de este ataque aumentaría si no necesitara que el usuario pulse un botón. Con este objetivo, cread un comentario que sirva vuestros propósitos sin levantar ninguna sospecha entre los usuarios que consulten los comentarios sobre un jugador (**show_comments.php**).

Podemos **inyectar** un enlace malicioso en un comentario que se cargue automáticamente cuando los usuarios visiten la página de comentarios. El enlace puede ser invisible para ellos y redirigir a la página de donación con los parámetros de ataque. **Ejemplo**:

   ```html
<a href="http://web.pagos/donate.php?amount=100&receiver=attacker" style="display:none;">Profile</a>
```

C) Pero web.pagos sólo gestiona pagos y donaciones entre usuarios registrados, puesto que, evidentemente, le tiene que restar los 100€ a la cuenta de algún usuario para poder añadirlos a nuestra cuenta.

La **donación** solo se realiza si el usuario está registrado en la plataforma de pagos. El sistema necesita deducir los 100€ de la cuenta de un usuario para poder asignarlos al atacante.

D) Si web.pagos modifica la página **donate.php** para que reciba los parámetros a través de **POST**, ¿quedaría blindada contra este tipo de ataques? En caso negativo, preparad un mensaje que realice un ataque equivalente al de la apartado b) enviando los parámetros “*amount*” i “*receiver*” por **POST**.

No, si se usa un formulario con método POST, el ataque seguiría siendo posible. Aunque los parámetros no estén en la URL, podríamos enviar los datos a través de un formulario oculto, que se enviaría automáticamente al cargar la página. **Ejemplo**:

   ```html
<form action="http://web.pagos/donate.php" method="POST" style="display:none;">
    <input type="hidden" name="amount" value="100">
    <input type="hidden" name="receiver" value="attacker">
    <input type="submit">
</form>
<script>
    document.forms[0].submit();
</script>
``` 
