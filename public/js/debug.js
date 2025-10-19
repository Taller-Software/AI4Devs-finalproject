// Script de debugging para Railway
console.log('[DEBUG] debug.js cargado');

// Verificar que todos los scripts se cargaron
window.addEventListener('DOMContentLoaded', () => {
    console.log('[DEBUG] DOM Content Loaded');
    console.log('[DEBUG] API_BASE_URL:', typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : 'NO DEFINIDO');
    console.log('[DEBUG] api object:', typeof api !== 'undefined' ? 'DEFINIDO' : 'NO DEFINIDO');
    console.log('[DEBUG] Auth class:', typeof Auth !== 'undefined' ? 'DEFINIDO' : 'NO DEFINIDO');
    console.log('[DEBUG] window.auth:', typeof window.auth !== 'undefined' ? window.auth : 'NO DEFINIDO');
    
    // Verificar elementos del DOM
    console.log('[DEBUG] btnSendCode:', document.getElementById('btnSendCode') ? 'ENCONTRADO' : 'NO ENCONTRADO');
    console.log('[DEBUG] email input:', document.getElementById('email') ? 'ENCONTRADO' : 'NO ENCONTRADO');
    console.log('[DEBUG] emailForm:', document.getElementById('emailForm') ? 'ENCONTRADO' : 'NO ENCONTRADO');
    
    // Capturar errores globales
    window.addEventListener('error', (e) => {
        console.error('[DEBUG ERROR]', {
            message: e.message,
            filename: e.filename,
            lineno: e.lineno,
            colno: e.colno,
            error: e.error
        });
    });
    
    // Capturar promesas rechazadas
    window.addEventListener('unhandledrejection', (e) => {
        console.error('[DEBUG PROMISE REJECTION]', e.reason);
    });
});

// Log cuando se hace click en el botón
setTimeout(() => {
    const btnSendCode = document.getElementById('btnSendCode');
    if (btnSendCode) {
        console.log('[DEBUG] Añadiendo listener de test al botón');
        btnSendCode.addEventListener('click', () => {
            console.log('[DEBUG] ¡CLICK DETECTADO EN BOTÓN!');
            console.log('[DEBUG] Email value:', document.getElementById('email')?.value);
            console.log('[DEBUG] window.auth exists:', !!window.auth);
            if (window.auth && typeof window.auth.handleLoginSubmit === 'function') {
                console.log('[DEBUG] handleLoginSubmit es una función');
            } else {
                console.error('[DEBUG] handleLoginSubmit NO ES UNA FUNCIÓN o auth no existe');
            }
        }, true); // true = capture phase (se ejecuta antes)
    } else {
        console.error('[DEBUG] btnSendCode NO ENCONTRADO en setTimeout');
    }
}, 1000);
