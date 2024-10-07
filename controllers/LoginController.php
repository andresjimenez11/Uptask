<?php 

namespace Controllers;

use MVC\Router;
use Classes\Email;
use Model\usuario;

class LoginController {
    public static function login(Router $router) {
        
        $alertas = [];

        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                // Comprobar que exista el usuario 
                $usuario = Usuario::where('email', $auth->email);
                
                if(!$usuario || !$usuario->confirmado){
                    Usuario::setAlerta('error', 'El Usuario No Se Encuentra Registrado');
                } else {
                    // Verificar Password
                    if($usuario->comprobarPasswordAndVerificado($auth->password)){
                        // Autenticar el usuario
                        if(!isset($_SESSION)){
                            session_start();
                        };

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['apellidos'] = $usuario->apellidos;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionamiento según si es admin o cliente
                        header('Location: /dashboard');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();
        
        // Render a la vista
        $router->render('auth/login', [
            'alertas' => $alertas,
            'titulo' => 'Iniciar sesión',
            'usuario' => $usuario
        ]);
    }

    public static function logout(Router $router) {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router) {
        
        $alertas = [];
        
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST') { 
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            $existeUsuario = Usuario::where('email', $usuario->email);
            
            if(empty($alertas)) {
                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El usuario ya está registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear Password
                    $usuario->hashearPassword();

                    // Eliminar password2 del objeto
                    unset($usuario->password2);

                    // Generar Token
                    $usuario->crearToken();

                    // Crear un nuevo usuario
                    $resultado = $usuario->guardar();

                    // Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();
                    
                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }  
        }
        // Render a la vista
        $router->render('auth/crear', [
            'usuario' => $usuario,
            'titulo' => 'Crea tu cuenta',
            'alertas' => $alertas
        ]);
    }

    public static function olvide(Router $router) {
        
        //$token = s($_GET['token']);
        
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();
            if(empty($alertas)){   
                // Buscar usuario
                $usuario = Usuario::where('email', $usuario->email);
                
                if($usuario && $usuario->confirmado){
                    // Eliminar password2 del objeto
                    unset($usuario->password2);

                    // Generar token
                    $usuario->crearToken();

                    // Actualizar el usuario
                    $usuario->guardar();

                    // Enviar Email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // Imprimir alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no se encuentra registrado o no está confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide', [
            'titulo' => 'Reestablecer Password',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {

        // Obtenemos token de la URL
        $token = s($_GET['token']);

        $mostrar = true;

        // Si no hay token en la URL se devuelve a la pagina del inicio
        if(!$token) header('Location: /');

        // Encontrar el usuario con este token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)){
            // No se encuentro un usuario con ese token 
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir el nuevo password
            $usuario->sincronizar($_POST);
            
            // Validar el password
            $alertas = $usuario->validarPassword();
            
            if(empty($alertas)){
                // Eliminar password2 del objeto
                unset($usuario->password2);

                // Hashear el nuevo password
                $usuario->hashearPassword();

                // Eliminar el token
                $usuario->token = null;

                // Guardar
                $resultado = $usuario->guardar();
                
                if($resultado) {
                    header('Location: /');
                }
            }
        }   

        $alertas = Usuario::getAlertas();

        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Contraseña',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function confirmar(Router $router) {
        
        // Obtenemos token de la URL
        $token = s($_GET['token']);

        // Si no hay token en la URL se devuelve a la pagina del inicio
        if(!$token) header('Location: /');

        // Encontrar el usuario con este token
        $usuario = Usuario::where('token', $token);
        
        if(empty($usuario)){
            // No se encuentro un usuario con ese token 
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            // Confirmar la cuenta
            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);

            $usuario->guardar();
        }
 
        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar', [
            'titulo' => 'Cuenta Confirmada Exitosamente',
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        //$email = $_SERVER['email'];

        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta Confirmada'
        ]);
    }
}