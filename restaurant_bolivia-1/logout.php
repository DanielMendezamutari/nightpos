<?php
// Inicializa la sesión
session_start();

// Destruye todas las variables de la sesión
$_SESSION = array();
 
//guardar el nombre de la sessión para luego borrar las cookies
$session_name = session_name();
 
//Para destruir una variable en específico
unset($_SESSION['usuario']);
unset($_SESSION['password']);
unset($_SESSION['nivel']);
//unset($_SESSION['hora']);
//unset($_SESSION['minutos']);
//unset($_SESSION['autorizacion']);
 
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruye la sesión
session_destroy();

// Para borrar las cookies asociadas a la sesión
// Es necesario hacer una petición http para que el navegador las elimine
if (isset($_COOKIE[ $session_name])) {
    if (setcookie(session_name(), '', time()-3600, '/')) {
        ?>
	<script type='text/javascript' language='javascript'>
	document.location.href='index'	 
	</script> 
	<?php
    exit();   
    }
}
?>