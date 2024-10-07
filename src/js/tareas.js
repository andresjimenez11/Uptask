(function() {
    
    obtenerTareas();
    let tareas = [];
    let filtradas = [];
    
    // Boton para mostrar el Modal de agregar tareas
    const nuevaTareaBtn = document.querySelector('#agregar-tarea');
    nuevaTareaBtn.addEventListener('click', function() {
        mostrarFormulario();
    });

    // Filtros de búsqueda
    const filtros = document.querySelectorAll('#filtros input[type="radio"]');
    filtros.forEach( radio => {
        radio.addEventListener('input', filtrarTareas);
    } )

    function filtrarTareas(e) {
        const filtro = e.target.value;
        if(filtro !== '') {
            filtradas = tareas.filter(tarea => tarea.estado === filtro)
        } else {
            filtradas = [];
        }
        mostrarTareas();
    }

    async function obtenerTareas() {
        try {
            const id = obtenerProyecto();
            const url = `/api/tareas?id=${id}`;
            const respuesta = await fetch(url);
            const resultado = await respuesta.json();

            tareas = resultado.tareas;
            mostrarTareas();

        } catch (error) {
            console.log(error);
        }
    }

    function mostrarTareas() {

        limpiarTareas();
        totalPendientes();
        totalCompletadas();

        const arrayTareas = filtradas.length ? filtradas : tareas;

        if(arrayTareas.length === 0) {
            const contenedorTareas = document.querySelector('#listado-tareas');

            const textoNoTareas = document.createElement('LI');
            textoNoTareas.textContent = 'No Hay Tareas';
            textoNoTareas.classList.add('no-tareas');

            contenedorTareas.appendChild(textoNoTareas);
            return;
        }

        const estados = {
            0: 'Pendiente',
            1: 'Completa'
        }

        arrayTareas.forEach(tarea => {
            
            // Contenedor tareas
            const contenedorTarea = document.createElement('LI');
            contenedorTarea.dataset.tareaId = tarea.id;
            contenedorTarea.classList.add('tarea');

            // Nombres tareas
            const nombreTarea = document.createElement('P');
            nombreTarea.textContent = tarea.nombre;
            nombreTarea.onclick = function() {
                mostrarFormulario(true, {...tarea});
            }

            // Opciones
            const opcionesDiv = document.createElement('DIV');
            opcionesDiv.classList.add('opciones');
            
            // Botones
            const btnEstadoTarea = document.createElement('BUTTON');
            btnEstadoTarea.classList.add('estado-tarea');
            btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
            btnEstadoTarea.textContent = estados[tarea.estado];
            btnEstadoTarea.dataset.estadoTarea = tarea.estado;
            /*btnEstadoTarea.ondblclick = function() { // Doble click
                cambiarEstadoTarea({...tarea}); //Copia del objeto de memoria. Modificamos el objeto en memoria
            }*/
            btnEstadoTarea.onclick = function() { // Doble click
                cambiarEstadoTarea({...tarea}); //Copia del objeto de memoria. Modificamos el objeto en memoria
            }
            
            const btnEliminarTarea = document.createElement('BUTTON');
            btnEliminarTarea.classList.add('eliminar-tarea');
            btnEliminarTarea.dataset.idTarea = tarea.id;
            btnEliminarTarea.textContent = 'Eliminar';
            btnEliminarTarea.onclick = function() {
                confirmarEliminarTarea({...tarea});
            }

            opcionesDiv.appendChild(btnEstadoTarea);
            opcionesDiv.appendChild(btnEliminarTarea);   
            
            contenedorTarea.appendChild(nombreTarea);
            contenedorTarea.appendChild(opcionesDiv);
            
            const listadoTareas = document.querySelector('#listado-tareas');
            listadoTareas.appendChild(contenedorTarea);
        });
    }

    function totalPendientes() {
        const totalPendientes = tareas.filter(tarea => tarea.estado === "0");
        const pendientesRadio = document.querySelector('#pendientes');

        if(totalPendientes.length === 0){
            pendientesRadio.disabled = true;
        } else {
            pendientesRadio.disabled = false;
        }
    }

    function totalCompletadas() {
        const totalCompletadas = tareas.filter(tarea => tarea.estado === "1");
        const completadasRadio = document.querySelector('#completadas');

        if(totalCompletadas.length === 0){
            completadasRadio.disabled = true;
        } else {
            completadasRadio.disabled = false;
        }
    }

    function mostrarFormulario(editar = false, tarea = {}) {
        const nombre = tarea.nombre
        const modal = document.createElement('DIV');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>${editar ? 'Editar Tarea' : 'Añade una nueva tarea'}</legend>
                <div class="campo">
                    <label>Tarea</label>
                    <input
                        type="text"
                        name="tarea"
                        placeholder="${tarea.nombre ? 'Editar la tarea' : 'Ingresa nombre de la tarea'}"
                        id="tarea"
                        value="${tarea.nombre ? nombre.trim() : ''}"
                    >
                </div>
                <div class="opciones">
                    <input type="submit" class="submit-nueva-tarea" value="${editar ? 'Editar tarea' : 'Añadir tarea'}" />
                    <button type="button" class="cerrar-modal">Cancelar</button>
                </div>
            </form>
        `;

        setTimeout(() => {
            const formulario = document.querySelector('.formulario');
            formulario.classList.add('animar');
        }, 0);

        modal.addEventListener('click', function(e){
            e.preventDefault();

            if(e.target.classList.contains('cerrar-modal')) {
                const formulario = document.querySelector('.formulario');
                formulario.classList.add('cerrar');
                setTimeout(() => {
                    modal.remove();
                }, 100);
            }
            if(e.target.classList.contains('submit-nueva-tarea')) {
                const nombreTarea = document.querySelector('#tarea').value.trim();
            
                if(nombreTarea === '') {
                    // Mostrar alerta
                    mostrarAlerta('El nombre de la tarea es obligatorio', 'error', document.querySelector('.formulario legend'));
                    
                    return;
                }

                if(editar) {
                    tarea.nombre = nombreTarea;
                    actualizarTarea(tarea);
                    
                } else {
                    agregarTarea(nombreTarea);
                }
            }
        })

        document.querySelector('.dashboard').appendChild(modal);
    }
    
    function mostrarAlerta(mensaje, tipo, referencia) {

        // Previene la creación de múltiples alertas
        const alertaPrevia = document.querySelector('.alerta');
        if(alertaPrevia) {
            alertaPrevia.remove();
        }
        
        const alerta = document.createElement('DIV');
        alerta.classList.add('alerta', tipo);
        alerta.textContent = mensaje;
        
        // Mostrar la alerta en un lugar determinado, entre el legend y los siblings
        referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);
        
        // Eliminar la alerta después de un tiempo
        setTimeout(() => {
            alerta.remove();
        }, 1500);
    }
    
    // Consultar el servidor para añadir una nueva tarea al proyecto
    async function agregarTarea(tarea) {
        
        // const { id, nombre, estado, proyectoId } = tareas;
        // Construir la petición
        const datos = new FormData();
        datos.append('nombre', tarea);
        datos.append('proyectoId', obtenerProyecto());
   
        try {
            // Peticion hacia la api
            const url = 'http://localhost:3000/api/tarea';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            
            const resultado = await respuesta.json();
            
            mostrarAlerta(resultado.mensaje, resultado.tipo, document.querySelector('.formulario legend'));
            
            // Si el resultado es exitoso, se cierra el modal
            if(resultado.tipo === 'exito') {
                const modal = document.querySelector('.modal');
                setTimeout(() => {
                    modal.remove();
                }, 500);

                // Agregar el objeto de tarea al global de tareas
                const tareaObj = {
                    id: String(resultado.id),
                    nombre: tarea,
                    estado: "0",
                    proyectoId: resultado.proyectoId
                }

                tareas = [...tareas, tareaObj]; // Integrar al arreglo global, con spread operator permite acceder a una copia del arreglo
                filtroActivo();
                mostrarTareas(); // agregar las nuevas tareas sin recargar página
            }
        } catch (error) {
            console.log(error);
        }  
    }

    function cambiarEstadoTarea(tarea) {

        const nuevoEstado = tarea.estado === "1" ? "0" : "1";
        tarea.estado = nuevoEstado;
        actualizarTarea(tarea);
    }

    async function actualizarTarea(tarea) {

        const {estado, id, nombre} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea/actualizar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });
            
            const resultado = await respuesta.json();

            if(resultado.respuesta.tipo === 'exito') {

                Swal.fire(resultado.respuesta.mensaje, '','success').then(
                    setTimeout(() => {
                        swal.close();
                    }, 1500)
                )
                
                const modal = document.querySelector('.modal');
                if(modal) {
                    setTimeout(() => {
                        modal.remove();
                    }, 100);
                }

                tareas = tareas.map(tareaMemoria => { //Permite crear un nuevo arreglo a partir de otro
                    if(tareaMemoria.id === id) {
                        tareaMemoria.estado = estado;
                        tareaMemoria.nombre = nombre;
                    }
                    return tareaMemoria;
                });

                filtroActivo();

                mostrarTareas();
            }

        } catch (error) {
            console.log(error);
        }

        // NOTA: Ver datos que se enviarán como petición
        /* for(let valor of datos.values()) {
            console.log(valor);
        } */
    }

    function confirmarEliminarTarea(tarea) {
        Swal.fire({
            title: 'Se eliminará está tarea...',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#7C3AED',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if(result.isConfirmed) {
                eliminarTarea(tarea);
            }
        })
    }

    async function eliminarTarea(tarea) {

        const {estado, id, nombre} = tarea;

        const datos = new FormData();
        datos.append('id', id);
        datos.append('nombre', nombre);
        datos.append('estado', estado);
        datos.append('proyectoId', obtenerProyecto());

        try {
            const url = 'http://localhost:3000/api/tarea/eliminar';
            const respuesta = await fetch(url, {
                method: 'POST',
                body: datos
            });

            const resultado = await respuesta.json();

            if(resultado.resultado.tipo === 'exito') {
                /* mostrarAlerta(
                    resultado.resultado.mensaje, 
                    resultado.resultado.tipo, 
                    document.querySelector('.contenedor-nueva-tarea')
                ); */
                
                Swal.fire('¡Eliminado!', resultado.mensaje, 'success').then(
                    setTimeout(() => {
                        swal.close();
                    }, 1500)
                );

                tareas = tareas.filter(tareaMemoria => tareaMemoria.id !== tarea.id); // Filter Permite crear un nuevo arreglo y sacar los elementos que cumplan cierta condición
                
                filtroActivo();    

                mostrarTareas();
            }

        } catch (error) {
            Swal.fire('No se pudo eliminar', resultado.mensaje, 'error').then(
                setTimeout(() => {
                    swal.close();
                }, 2000)
            );
        }
    }

    function obtenerProyecto() {
        // Obtener el ID de la URL
        const proyectoParams = new URLSearchParams(window.location.search);

        // Obtener los datos del objeto
        const proyecto = Object.fromEntries(proyectoParams.entries());
        
        // Obtener la URL
        return proyecto.id;
    }

    function limpiarTareas() {
        const listadoTareas = document.querySelector('#listado-tareas');
        
        // Va evaluar si hay contenido, si lo hay elimina el primer elemento, es más rápido que poner innerHTML = '';
        while(listadoTareas.firstChild) {
            listadoTareas.removeChild(listadoTareas.firstChild);
        }
    }

    function filtroActivo() {
        // Revisar si hay un filtro activo...
        const filtroActivo = document.querySelector('input[name="filtro"]:checked').value;

        if(filtroActivo) {
            filtradas = tareas.filter(tarea => tarea.estado === filtroActivo);
            if(!filtradas.length) {
                radiobtn = document.getElementById("todas");
                radiobtn.checked = true;
            }
        }
    }
})();

