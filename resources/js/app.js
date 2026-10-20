

import Alpine from 'alpinejs';

window.Alpine = Alpine;

function resumirFormulario(formulario) {
    return [...formulario.querySelectorAll('input, select, textarea')]
        .filter(campo => !campo.matches(':disabled') && (campo.name || campo.dataset.resumen)
            && !['hidden', 'password', 'submit', 'button'].includes(campo.type)
            && !/password|clave/i.test(campo.name)
            && (campo.type !== 'radio' || campo.checked))
        .map(campo => ({
            etiqueta: campo.labels?.[0]?.textContent.trim() || campo.name.replaceAll('_', ' '),
            valor: campo.type === 'file' ? ([...campo.files].map(archivo => archivo.name).join(', ') || 'Sin adjuntar')
                : campo.type === 'checkbox' ? (campo.checked ? 'Sí' : 'No')
                : campo.tagName === 'SELECT' ? campo.selectedOptions[0]?.textContent.trim()
                : campo.value || 'Sin indicar',
        }));
}

function validarRut(campo) {
    const compacto = campo.value.replace(/[.\s-]/g, '').toUpperCase();
    let valido = !campo.required && compacto === '';
    if (/^\d{7,8}[\dK]$/.test(compacto)) {
        const cuerpo = compacto.slice(0, -1);
        let suma = 0;
        [...cuerpo].reverse().forEach((digito, indice) => suma += Number(digito) * (2 + indice % 6));
        const resto = 11 - suma % 11;
        valido = (resto === 11 ? '0' : resto === 10 ? 'K' : String(resto)) === compacto.slice(-1);
        const normalizado = cuerpo + '-' + compacto.slice(-1);
        if (campo.value !== normalizado) {
            campo.value = normalizado;
            campo.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
    const mensaje = valido ? '' : 'Ingresa un RUT válido, por ejemplo 12345678-5.';
    campo.setCustomValidity(mensaje);
    campo.setAttribute('aria-invalid', String(!valido));
    let aviso = document.getElementById(campo.id + '-rut-error');
    if (!aviso) {
        aviso = document.createElement('p');
        aviso.id = campo.id + '-rut-error';
        aviso.className = 'mt-2 text-sm text-red-600 dark:text-red-400';
        aviso.setAttribute('aria-live', 'polite');
        campo.insertAdjacentElement('afterend', aviso);
        campo.setAttribute('aria-describedby', [campo.getAttribute('aria-describedby'), aviso.id].filter(Boolean).join(' '));
    }
    aviso.textContent = mensaje;
}

function prepararValidacion(campos) {
    [...campos].filter(campo => !campo.matches(':disabled')).forEach(campo => {
        if (['rut', 'rut_solicitante', 'responsable_rut', 'representante_rut', 'solicitante_rut'].includes(campo.name)) validarRut(campo);
        campo.dispatchEvent(new Event('validar-fecha'));
    });
}

Alpine.data('appShell', () => ({
    sidebarOpen: false,
    desktop: window.innerWidth >= 1280,
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
}));

Alpine.data('grupoWizard', (estado, personas) => ({
    ...estado,
    personas,
    guardando: false,
    resumen: [],
    copiarContacto() {
        this.contacto = { ...this.representante };
    },
    elegirRepresentante() {
        if (this.origen !== 'manual') {
            const persona = this.personas[this.origen];
            this.representante = {
                nombre: persona?.nombre ?? '', rut: persona?.rut ?? '', correo: persona?.correo ?? '',
                telefono: persona?.telefono ?? '', direccion: persona?.direccion ?? '',
            };
        }
        this.usarContacto = true;
        this.copiarContacto();
    },
    validar(campos) {
        prepararValidacion(campos);
        const invalido = [...campos].find(campo => campo.willValidate && !campo.checkValidity());
        if (!invalido) return true;
        this.paso = Number(invalido.closest('[data-paso]').dataset.paso);
        this.$nextTick(() => invalido.reportValidity());
        return false;
    },
    avanzar() {
        const panel = this.$refs.formulario.querySelector(`[data-paso="${this.paso}"]`);
        if (this.validar(panel.querySelectorAll('input, select'))) {
            this.paso = Math.min(3, this.paso + 1);
            if (this.paso === 3) this.resumen = resumirFormulario(this.$refs.formulario);
            this.$nextTick(() => this.$refs.tituloPaso.focus());
        }
    },
    confirmar(evento) {
        if (this.paso < 3) {
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
    resumen: [],
    init() {
        this.formulario = this.$el.querySelector('form');
    },
    enfocarPaso() {
        this.$nextTick(() => this.$refs.tituloPaso.focus());
    },
    validar(campos) {
        prepararValidacion(campos);
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
            if (this.paso === this.pasos.length - 1) this.resumen = resumirFormulario(this.formulario);
            this.enfocarPaso();
        }
    },
    cancelar() {
        this.formulario.reset();
        const panel = this.$el.closest('details');
        if (panel) { panel.open = false; panel.querySelector('summary')?.focus(); }
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
    generarClave() {
        const bytes = crypto.getRandomValues(new Uint8Array(12));
        this.clave = 'Aa1!' + [...bytes].map(byte => byte.toString(16).padStart(2, '0')).join('');
        this.confirmacion = this.clave;
    },
}));

Alpine.data('arriendoFormulario', estado => ({
    ...estado,
    opciones() { return this.personas.filter(p => this.tipoActividad === 'actividad' ? p.dirigente : this.tipoActividad === 'grupo' ? !!p.grupo_id : p.activo); },
    cambiarActividad() { this.seleccion = ''; this.grupoId = ''; this.persona = { nombre: '', rut: '', direccion: '', telefono: '' }; },
    elegirSolicitante() { const p = this.personas.find(p => p.clave === this.seleccion); this.persona = { nombre: p?.nombre || '', rut: p?.rut || '', direccion: p?.direccion || '', telefono: p?.telefono || '' }; },
}));

// La validación del servidor se mantiene; el navegador la anticipa al salir del campo.
document.addEventListener('focusout', evento => {
    const campo = evento.target;
    if (!(campo instanceof HTMLInputElement) || !['rut', 'rut_solicitante', 'responsable_rut', 'representante_rut', 'solicitante_rut'].includes(campo.name)) return;
    validarRut(campo);
});

document.addEventListener('invalid', evento => {
    const campo = evento.target;
    if (campo.validity.customError) return;
    const validez = campo.validity;
    campo.setCustomValidity(validez.valueMissing ? 'Completa este campo para continuar.'
        : validez.typeMismatch ? 'Ingresa un valor válido para este campo.'
        : validez.rangeUnderflow ? `El valor mínimo es ${campo.min}.`
        : validez.rangeOverflow ? `El valor máximo es ${campo.max}.`
        : 'Revisa el formato y el valor de este campo.');
    campo.dataset.errorHtml = '1';
}, true);
document.addEventListener('input', evento => {
    const campo = evento.target;
    if (campo.dataset.errorHtml) {
        campo.setCustomValidity('');
        delete campo.dataset.errorHtml;
    }
    if (['rut', 'rut_solicitante', 'responsable_rut', 'representante_rut', 'solicitante_rut'].includes(campo.name) && campo.getAttribute('aria-invalid') === 'true') {
        campo.setCustomValidity('');
    }
}, true);

Alpine.data('fechaEspanol', (valor, minimo, maximo) => ({
    iso: valor,
    texto: '',
    minimo,
    maximo,
    abierto: false,
    mes: 0,
    ano: 0,
    init() {
        this.texto = this.mostrar(this.iso);
        const base = this.iso || this.maximo || this.minimo;
        const fecha = base ? new Date(base + 'T12:00:00') : new Date();
        this.mes = fecha.getMonth();
        this.ano = fecha.getFullYear();
    },
    mostrar(iso) {
        return /^\d{4}-\d{2}-\d{2}$/.test(iso) ? iso.split('-').reverse().join('/') : iso;
    },
    actualizar() {
        const partes = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(this.texto);
        this.iso = '';
        if (partes) {
            const [, dia, mes, ano] = partes;
            const fecha = new Date(Number(ano), Number(mes) - 1, Number(dia));
            if (Number(ano) >= 1000 && fecha.getFullYear() === Number(ano) && fecha.getMonth() === Number(mes) - 1 && fecha.getDate() === Number(dia)) {
                this.iso = `${ano}-${mes}-${dia}`;
                this.mes = Number(mes) - 1;
                this.ano = Number(ano);
            }
        }
        this.validar();
    },
    validar() {
        const campo = this.$refs.campo;
        let mensaje = '';
        if (this.texto && !this.iso) mensaje = 'Ingresa una fecha válida con formato dd/mm/aaaa.';
        else if (this.iso && this.minimo && this.iso < this.minimo) mensaje = `La fecha mínima es ${this.mostrar(this.minimo)}.`;
        else if (this.iso && this.maximo && this.iso > this.maximo) mensaje = `La fecha máxima es ${this.mostrar(this.maximo)}.`;
        campo.setCustomValidity(mensaje);
        campo.setAttribute('aria-invalid', String(Boolean(mensaje)));
    },
    elegir(iso) {
        this.iso = iso;
        this.texto = this.mostrar(iso);
        this.abierto = false;
        this.$nextTick(() => { this.validar(); this.$refs.campo.focus(); });
    },
    moverMes(cambio) {
        const fecha = new Date(this.ano, this.mes + cambio, 1);
        this.ano = fecha.getFullYear();
        this.mes = fecha.getMonth();
    },
    get anos() {
        const actual = new Date().getFullYear();
        const desde = this.minimo ? Number(this.minimo.slice(0, 4)) : Math.min(actual - 120, this.ano);
        const hasta = this.maximo ? Number(this.maximo.slice(0, 4)) : Math.max(actual + 10, this.ano);
        return Array.from({ length: Math.max(0, hasta - desde + 1) }, (_, i) => desde + i);
    },
    get dias() {
        const inicio = (new Date(this.ano, this.mes, 1).getDay() + 6) % 7;
        const cantidad = new Date(this.ano, this.mes + 1, 0).getDate();
        return Array.from({ length: 42 }, (_, indice) => {
            const numero = indice - inicio + 1;
            return numero < 1 || numero > cantidad ? { numero: null, iso: '' } : {
                numero,
                iso: `${this.ano}-${String(this.mes + 1).padStart(2, '0')}-${String(numero).padStart(2, '0')}`,
            };
        });
    },
}));

Alpine.data('prestamoFormulario', (estado, contactos, existencias) => ({
    ...estado,
    contactos,
    existencias,
    get disponibles() {
        return this.existencias[this.itemId] ?? 0;
    },
    elegirGrupo() {
        const contacto = this.contactos[this.grupoId];
        this.nombre = contacto?.nombre ?? '';
        this.telefono = contacto?.telefono ?? '';
    },
    cambiarDestino() {
        this.grupoId = '';
        this.nombre = '';
        this.telefono = '';
    },
}));

Alpine.start();
