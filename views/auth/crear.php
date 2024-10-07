<div class="contenedor crear">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Crear Cuenta</p>
        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>
        <form class="formulario" method="POST" action="/crear">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    placeholder="Ingresa tu nombre"
                    name="nombre"
                    value="<?php echo $usuario->nombre; ?>"
                >
            </div>
            <div class="campo">
                <label for="apellidos">Apellidos</label>
                <input
                    type="text"
                    id="apellidos"
                    placeholder="Ingresa tu apellidos"
                    name="apellidos"
                    value="<?php echo $usuario->apellidos; ?>"
                >
            </div>
            <div class="campo">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    placeholder="Ingresa email"
                    name="email"
                    value="<?php echo $usuario->email; ?>"
                >
            </div>
            <div class="campo">
                <label for="telefono">Telefono</label>
                <input
                    type="number"
                    id="telefono"
                    placeholder="Ingresa teléfono"
                    name="telefono"
                    value="<?php echo $usuario->telefono; ?>"
                >
            </div>
            <div class="campo">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    placeholder="Ingresa password"
                    name="password"
                >
            </div>
            <div class="campo">
                <label for="password2">Confirmar Password</label>
                <input
                    type="password"
                    id="password2"
                    placeholder="Ingresa nuevamente tu password"
                    name="password2"
                >
            </div>

            <input type="submit" class="boton" value="Crear cuenta">
        </form>

        <div class="acciones">
            <a href="/">¿Ya tienes una cuenta? Inicia sesión</a>
            <a href="/olvide">¿Olvidaste tu password?</a>
        </div>
    </div> <!-- .contenedor-sm -->
</div> <!-- .contenedor -->