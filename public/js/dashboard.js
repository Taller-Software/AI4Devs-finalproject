// Gestión del dashboard
class Dashboard {
    constructor() {
        this.container = document.getElementById('dashboard');
        this.dashboardContent = document.getElementById('dashboardContent');
        this.ultimoRefresco = document.getElementById('ultimoRefresco');
        this.btnRefresh = document.getElementById('btnRefreshDashboard');
        this.updateInterval = 5 * 60 * 1000; // 5 minutos
        this.timer = null;
        this.setupEventListeners();
    }

    setupEventListeners() {
        if (this.btnRefresh) {
            this.btnRefresh.addEventListener('click', () => this.manualRefresh());
        }
    }

    async manualRefresh() {
        // Animación del botón
        if (this.btnRefresh) {
            this.btnRefresh.disabled = true;
            this.btnRefresh.classList.add('opacity-50', 'cursor-not-allowed');
            const svg = this.btnRefresh.querySelector('svg');
            if (svg) {
                svg.classList.add('animate-spin');
            }
        }

        try {
            await this.loadDashboard();
            showToast('Dashboard actualizado correctamente', 'success');
        } catch (error) {
            showToast('Error al actualizar el dashboard', 'error');
        } finally {
            // Restaurar el botón después de 1 segundo
            setTimeout(() => {
                if (this.btnRefresh) {
                    this.btnRefresh.disabled = false;
                    this.btnRefresh.classList.remove('opacity-50', 'cursor-not-allowed');
                    const svg = this.btnRefresh.querySelector('svg');
                    if (svg) {
                        svg.classList.remove('animate-spin');
                    }
                }
            }, 1000);
        }
    }

    async init() {
        await this.loadDashboard();
        this.startAutoRefresh();
    }

    async loadDashboard() {
        try {
            const response = await api.getDashboard();
            if (response.success) {
                console.log('=== DATOS DEL DASHBOARD ===');
                console.log('Total herramientas:', response.data.length);
                response.data.forEach((h, index) => {
                    console.log(`\nHerramienta ${index + 1}:`, {
                        nombre: h.nombre,
                        codigo: h.codigo,
                        ubicacion_actual: h.ubicacion_actual,
                        operario_actual: h.operario_actual,
                        fecha_inicio: h.fecha_inicio,
                        fecha_fin: h.fecha_fin,
                        'EN_USO': h.operario_actual != null ? 'SÍ' : 'NO'
                    });
                });
                console.log('===========================\n');
                
                this.renderHerramientas(response.data);
                this.updateRefreshTimestamp();
            } else {
                showToast(response.message, 'error');
            }
        } catch (error) {
            console.error('Error al cargar dashboard:', error);
            showToast('Error al cargar el dashboard', 'error');
        }
    }

    renderHerramientas(herramientas) {
        if (!this.dashboardContent) {
            console.error('Elemento dashboardContent no encontrado');
            return;
        }
        
        if (!herramientas || herramientas.length === 0) {
            this.dashboardContent.innerHTML = '<p class="text-gray-500 text-center">No hay herramientas registradas</p>';
            this.updateEstadisticas(0, 0, 0);
            return;
        }
        
        // Calcular estadísticas
        const total = herramientas.length;
        const ocupadas = herramientas.filter(h => h.operario_actual != null).length;
        const disponibles = total - ocupadas;
        
        // Actualizar los contadores en el DOM
        this.updateEstadisticas(total, disponibles, ocupadas);
        
        // Renderizar las tarjetas de herramientas
        this.dashboardContent.innerHTML = herramientas.map(h => this.createHerramientaCard(h)).join('');
    }

    updateEstadisticas(total, disponibles, ocupadas) {
        const totalElement = document.getElementById('totalHerramientas');
        const disponiblesElement = document.getElementById('herramientasDisponibles');
        const ocupadasElement = document.getElementById('herramientasEnUso');
        
        if (totalElement) totalElement.textContent = total;
        if (disponiblesElement) disponiblesElement.textContent = disponibles;
        if (ocupadasElement) ocupadasElement.textContent = ocupadas;
        
        console.log('Estadísticas actualizadas:', { total, disponibles, ocupadas });
    }

    createHerramientaCard(herramienta) {
        // INTERPRETACIÓN: operario_actual NULL = disponible, NOT NULL = ocupado
        // Una herramienta está en uso si el último movimiento tiene operario
        // (cuando se devuelve, se crea un nuevo movimiento SIN operario)
        const enUso = herramienta.operario_actual != null;
        const estado = enUso ? 'ocupada' : 'disponible';
        const bgColor = estado === 'disponible' 
            ? 'bg-gradient-to-br from-green-900 to-emerald-900 border border-green-600' 
            : 'bg-gradient-to-br from-red-900 to-rose-900 border border-red-600';
        
        // Debug: mostrar en consola el estado calculado
        console.log(`${herramienta.nombre} - Estado: ${estado}`, {
            operario_actual: herramienta.operario_actual,
            fecha_fin: herramienta.fecha_fin,
            enUso: enUso
        });
        
        return `
            <div class="${bgColor} p-6 rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                <h4 class="font-bold text-white mb-4 text-xl border-b border-slate-600 pb-2">${herramienta.nombre}</h4>
                <div class="space-y-3">
                    <p class="text-sm text-slate-300">
                        <span class="font-semibold">Código:</span> <span class="text-white">${herramienta.codigo || 'N/A'}</span>
                    </p>
                    <p class="text-sm text-slate-300">
                        <span class="font-semibold">Ubicación:</span> <span class="text-white">${herramienta.ubicacion_actual || 'Sin ubicación'}</span>
                    </p>
                    <p class="text-sm text-slate-300">
                        <span class="font-semibold">Estado:</span> 
                        <span class="${estado === 'disponible' ? 'text-green-400 font-bold text-lg' : 'text-red-400 font-bold text-lg'}">
                            ${estado === 'disponible' ? '✓ Disponible' : '⚠ En Uso'}
                        </span>
                    </p>
                    ${estado === 'ocupada' ? `
                        <div class="mt-4 pt-3 border-t border-slate-600">
                            <p class="text-sm text-slate-300">
                                <span class="font-semibold">👤 Operario:</span> 
                                <span class="text-blue-400 font-bold">${herramienta.operario_actual}</span>
                            </p>
                        </div>
                    ` : ''}
                    ${enUso ? `
                        <p class="text-sm text-slate-300">
                            <span class="font-semibold">📅 Desde:</span> <span class="text-white">${this.formatDate(herramienta.fecha_inicio)}</span>
                        </p>
                    ` : ''}
                </div>
            </div>
        `;
    }

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        try {
            const date = new Date(dateString);
            return date.toLocaleString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('Error al formatear fecha:', error);
            return dateString;
        }
    }

    updateRefreshTimestamp() {
        if (this.ultimoRefresco) {
            this.ultimoRefresco.textContent = this.formatDate(new Date());
        }
    }

    startAutoRefresh() {
        if (this.timer) clearInterval(this.timer);
        this.timer = setInterval(() => this.loadDashboard(), this.updateInterval);
    }

    stopAutoRefresh() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    show() {
        this.container.classList.remove('hidden');
        this.init();
    }

    hide() {
        this.container.classList.add('hidden');
        this.stopAutoRefresh();
    }
}