const mobileMenuBtn = document.querySelector('#mobile-menu');
const cerrarMenu = document.querySelector('#cerrar-menu');
const sidebar = document.querySelector('.sidebar');

if(mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', function(){
        sidebar.classList.toggle('mostrar'); 
    });
}

if(cerrarMenu) {
    cerrarMenu.addEventListener('click', function() {

        sidebar.classList.add('ocultar');
        
        setTimeout(() => {
            sidebar.classList.remove('mostrar');
            sidebar.classList.remove('ocultar');
        }, 300);
    })
}

// Elimina la clase de mostrar en un tamaña de tablet o PC
window.addEventListener('resize', function() {
    const anchoPantalla = document.body.clientWidth;
    if(anchoPantalla >= 768) {
        sidebar.classList.remove('mostrar'); 
    }
})