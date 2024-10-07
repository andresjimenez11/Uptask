<?php 

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;

class DashboardController {
    public static function index(Router $router) {
        
        session_start();
        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('/dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear(Router $router) {
        session_start();

        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            // Validación 
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)){
                // Generar una URL única
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                
                // Almacenar el creador del proyecto, le asignamos el Id
                $proyecto->propietarioId = $_SESSION['id'];

                // Guardar  
                $proyecto->guardar();

                // Reedirección, una vez guardado en DB, nos va a redireccinar al url del proyecto
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }

        $router->render('/dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);

    }

    public static function proyecto(Router $router) {
        session_start();
        
        isAuth();
        
        $token = $_GET['id'];

        if(!$token) header('Location: /dashboard');

        // Revisar que la persona que visita el proyecto es quien lo creo
        $proyecto = Proyecto::where('url', $token);
        
        if($proyecto->propietarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        }

        $router->render('/dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            
            $usuario->sincronizar($_POST);

            $alertas = $usuario->validarPerfil();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);
                if($existeUsuario && $existeUsuario->id !== $usuario->id) {
                    // Mensaje de error
                    Usuario::setAlerta('error', 'El correo ya está registrado');
                } else {
                    // Guardar Usuario
                    // Guardar el usuario
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado correctamente');

                    // Asignar el nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                    $_SESSION['email'] = $usuario->email;
                }
            }
        }

        $alertas = $usuario->getAlertas();
        
        $router->render('/dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_password(Router $router) {
        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            // Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nuevo_password();
            
            if(empty($alertas)) {

                $resultado = $usuario->comprobar_password();

                if($resultado) {

                    // Sincronizar el password
                    $usuario->password = $usuario->password_nuevo;

                    // Borrar la casilla temporal del password actual y password nuevo
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    
                    // Hashear el nuevo password
                    $usuario->hashearPassword();

                    // Actualizar password
                    $resultado = $usuario->guardar();
                    
                    if($resultado) {
                        Usuario::setAlerta('exito', 'Password actualizado correctamente');
                    }

                } else {
                    Usuario::setAlerta('error', 'Password incorrecto');
                }
            }
        }

        $alertas = $usuario->getAlertas();

        $router->render('dashboard/cambiar-password', [
            'titulo' => 'Cambiar password',
            'alertas' => $alertas
        ]);
    }
}