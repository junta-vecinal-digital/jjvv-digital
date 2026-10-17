

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('appShell', () => ({
    sidebarOpen: false,
    desktop: window.innerWidth >= 1280,
    dark: document.documentElement.classList.contains('dark'),
    onResize: null,
    init() {
        this.onResize = () => {
            this.desktop = window.innerWidth >= 1280;
            if (this.desktop) this.sidebarOpen = false;
        };
        window.addEventListener('resize', this.onResize);
    },
    destroy() {
        window.removeEventListener('resize', this.onResize);
    },
    toggleTheme() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try {
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        } catch (_) {}
    },
}));

Alpine.data('grupoWizard', (estado, socios) => ({
    ...estado,
    socios,
    guardando: false,
    copiarContacto() {
        const socio = this.socios[this.socioId];
        this.contacto = {
            nombre: socio?.nombre ?? '',
            correo: socio?.correo ?? '',
            telefono: socio?.telefono ?? '',
            direccion: socio?.direccion ?? '',
        };
    },
    elegirRepresentante() {
        this.usarContacto = true;
        this.copiarContacto();
    },
    validar(campos) {
        const invalido = [...campos].find(campo => campo.willValidate && !campo.checkValidity());
        if (!invalido) return true;
        this.paso = Number(invalido.closest('[data-paso]').dataset.paso);
        this.$nextTick(() => invalido.reportValidity());
        return false;
    },
    avanzar() {
        const panel = this.$refs.formulario.querySelector(`[data-paso="${this.paso}"]`);
        if (this.validar(panel.querySelectorAll('input, select'))) {
            this.paso = Math.min(2, this.paso + 1);
            this.$nextTick(() => this.$refs.tituloPaso.focus());
        }
    },
    confirmar(evento) {
        if (this.paso < 2) {
            evento.preventDefault();
            this.avanzar();
            return;
        }
        if (this.guardando || !this.validar(this.$refs.formulario.querySelectorAll('input, select'))) {
            evento.preventDefault();
            return;
        }
        this.guardando = true;
    },
}));

Alpine.data('formWizard', (pasos, primerPaso) => ({
    pasos,
    paso: primerPaso,
    formulario: null,
    guardando: false,
    init() {
        this.formulario = this.$el.querySelector('form');
    },
    enfocarPaso() {
        this.$nextTick(() => this.$refs.tituloPaso.focus());
    },
    validar(campos) {
        const invalido = [...campos].find(campo => campo.willValidate && !campo.checkValidity());
        if (!invalido) return true;
        this.paso = Number(invalido.closest('[data-paso]').dataset.paso);
        this.$nextTick(() => invalido.reportValidity());
        return false;
    },
    avanzar() {
        const panel = this.formulario.querySelector(`[data-paso="${this.paso}"]`);
        if (this.validar(panel.querySelectorAll('input, select, textarea'))) {
            this.paso = Math.min(this.pasos.length - 1, this.paso + 1);
            this.enfocarPaso();
        }
    },
    retroceder() {
        this.paso = Math.max(0, this.paso - 1);
        this.enfocarPaso();
    },
    confirmar(evento) {
        if (this.paso < this.pasos.length - 1) {
            evento.preventDefault();
            this.avanzar();
        } else if (this.guardando || !this.validar(this.formulario.querySelectorAll('input, select, textarea'))) {
            evento.preventDefault();
        } else {
            this.guardando = true;
        }
    },
}));

Alpine.data('claveCuenta', () => ({
    clave: '',
    confirmacion: '',
    mostrarClave: false,
    generarClave() {
        const bytes = crypto.getRandomValues(new Uint8Array(12));
        this.clave = 'Aa1!' + [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
        this.confirmacion = this.clave;
        this.mostrarClave = true;
    },
}));

// Acepta RUT con o sin puntos y guion; el servidor sigue validando el dígito verificador.
document.addEventListener('focusout', evento => {
    const campo = evento.target;
    if (!(campo instanceof HTMLInputElement) || !['rut', 'rut_solicitante'].includes(campo.name)) return;
    const rut = campo.value.replace(/[.\s-]/g, '').toUpperCase();
    if (!/^\d{7,8}[\dK]$/.test(rut)) return;
    campo.value = rut.slice(0, -1) + '-' + rut.slice(-1);
    campo.dispatchEvent(new Event('input', { bubbles: true }));
});

Alpine.start();
