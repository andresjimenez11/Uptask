<div class="contenedor reestablecer">
    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Reestablecer Contraseña</p>

        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

        <?php if($mostrar){?>

            <form class="formulario" method="POST">

                <div class="campo">
                    <label for="password">Nueva Password</label>
                    <input
                        type="password"
                        id="password"
                        placeholder="Ingresa Tu Nueva Password"
                        name="password"
                    >
                </div>

                <input type="submit" class="boton" value="Guardar Password">
            </form>
        <?php }; ?>
        <div class="acciones">
            <a href="/">¿Ya tienes una cuenta? Inicia sesión</a>
            <a href="/crear">¿Aún no tienes una cuenta? Registrate</a>
        </div>
    </div> <!-- .contenedor-sm -->
</div> <!-- .contenedor -->