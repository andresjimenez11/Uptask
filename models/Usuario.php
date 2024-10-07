<?php 

namespace Model;
class Usuario extends ActiveRecord {

    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'apellidos', 'email', 'telefono', 'password', 'token', 'confirmado'];

    public $id;
    public $nombre;
    public $apellidos;
    public $email;
    public $telefono;
    public $password;
    public $password_actual;
    public $password_nuevo;
    public $password2;
    public $confirmado;
    public $token;

    public function __construct($args = []){
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellidos = $args['apellidos'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->token = $args['token'] ?? '';
    }

    public function validarNuevaCuenta() : array {
        if(!$this->nombre) {
            self::$alertas['error'][] = "Debes añadir un nombre";
        }

        if(!$this->apellidos) {
            self::$alertas['error'][] = "Debes añadir tus apellidos";
        }

        if(!$this->email) {
            self::$alertas['error'][] = "Debes añadir un email";
        }

        if(!$this->telefono) {
            self::$alertas['error'][] = "Debes añadir un teléfono";
        }

        if(!preg_match('/[0-9]{10}/', $this->telefono)) {
            self::$alertas['error'][] = "Formato no válido";
        }

        if(!$this->password) {
            self::$alertas['error'][] = "Debes añadir un password";
        }

        if(strlen($this->password) < 8) {
            self::$alertas['error'][] = "El password debe tener mínimo 8 carácteres";
        }

        if(!$this->password2) {
            self::$alertas['error'][] = "Debes repetir el password"; 
        }

        if($this->password !== $this->password2) {
            self::$alertas['error'][] = "El password no coincide";
        }
        
        return self::$alertas;
    }

    public function nuevo_password() : array {
        if(!$this->password_actual) {
            self::$alertas['error'][] = 'El password actual no puede ir vacío';
        }
        if(!$this->password_nuevo) {
            self::$alertas['error'][] = 'El password nuevo no puede ir vacío';
        }
        if(strlen($this->password_nuevo) < 8) {
            self::$alertas['error'][] = 'El password nuevo debe tener al menos 8 carácteres';
        }

        return self::$alertas;
    }

    // Comprobar el password
    public function comprobar_password() : bool {
        return password_verify($this->password_actual, $this->password); // Compara el password nuevo con el actual para validar
    }

    public function hashearPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function validarPassword() : array {
        if(!$this->password) {
            self::$alertas['error'][] = "Debes añadir un password";
        }

        if(strlen($this->password) < 8) {
            self::$alertas['error'][] = "El password debe tener mínimo 8 carácteres";
        }

        return self::$alertas;
    }

    public function crearToken() : void {
        // $this->token = md5( uniqid() ); -- Recomendable si se necesita más seguridad, proyectos muy grandes
        $this->token = uniqid();
    }

    public function validarEmail() : array {
        if(!$this->email){
            self::$alertas['error'][] = "Debe ingresar un email";
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = "Email no válido";
        }

        return self::$alertas;
    }
    
    public function validarLogin() : array {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][] = 'El email no válido';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }
        return self::$alertas;
    }

    public function validarPerfil() : array {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][] = 'El email no válido';
        }
        return self::$alertas;
    }

    public function comprobarPasswordAndVerificado($password){
        
        $resultado = password_verify($password, $this->password);

        if(!$resultado || !$this->confirmado){
            self::$alertas['error'][] = 'Password Incorrecto o Tu Cuenta No Ha Sido Confirmada';
        } else {
            return true;
        }
    }
}