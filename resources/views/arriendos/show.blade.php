<x-app-layout>
    <x-slot name="header"><x-page-heading :title="($reserva->aporte_variable ? 'Reserva de sede' : 'Arriendo').' #'.$reserva->id" description="Consulta el horario, el solicitante y el estado de la reserva." /></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        @if ($volverReservas)
            <a class="underline" href="{{ route('reservas.index', ['semana' => $reserva->inicio->copy()->startOfWeek()->toDateString()]) }}">Volver a reservas</a>
        @else
            <a class="underline" href="{{ route($volverFinanzas ? 'finanzas.arriendos.index' : 'arriendos.index') }}">{{ $volverFinanzas ? 'Volver a finanzas de sede' : 'Volver a reservas' }}</a>
        @endif
        @if (session('status'))
            <p role="status" class="ui-panel p-4">{{ match(session('status')) { 'arriendo-solicitado' => 'Solicitud registrada. El horario quedó reservado dentro del plazo de confirmación.', 'aporte-definido' => 'Aporte definido. El plazo de confirmación se mantiene.', 'comprobante-adjuntado' => 'Comprobante adjuntado para revisión.', 'arriendo-confirmado' => $reserva->aporte_variable ? 'Reserva confirmada.' : 'Arriendo confirmado y garantía registrada como recibida.', 'reserva-cancelada' => 'Reserva cancelada y horario liberado.', 'arriendo-registrado' => $reserva->aporte_variable ? 'Aporte registrado en ingresos.' : 'Aporte de sede registrado en ingresos y garantía registrada por separado.', 'garantia-por-devolver' => 'Garantía marcada como por devolver.', 'garantia-resuelta' => 'Resolución de garantía registrada.', 'retencion-registrada' => 'Ingreso por retención registrado nuevamente.', default => '' } }}</p>
        @endif
        <x-input-error :messages="$errors->get('arriendo')" />
        <x-input-error :messages="$errors->get('reserva')" />
        <x-input-error :messages="$errors->get('finanzas')" />
        @if ($esDirigente && $reserva->aporte_variable && $reserva->estado === 'pendiente')
            <aside class="rounded-xl border border-brand-400/40 bg-brand-50 p-4 text-sm text-brand-600 dark:bg-brand-500/10 dark:text-brand-400" aria-label="Siguiente paso">
                <h2 class="font-semibold">Siguiente paso</h2>
                <p class="mt-1">{{ $reserva->valor_arriendo === null ? 'Define el aporte y la garantía acordados en el formulario de abajo.' : (($reserva->valor_arriendo > 0 || $reserva->valor_garantia > 0) && ! $reserva->comprobante_arriendo_path ? 'Falta el comprobante del aporte. Revisa el canal de la solicitud para saber quién debe adjuntarlo.' : 'Revisa los antecedentes y utiliza el formulario de confirmación de más abajo.') }}</p>
            </aside>
        @endif
        <section class="ui-panel p-6 space-y-4">
            <p>Estado: <x-status-badge :state="$reserva->estado" :label="\App\Models\Reserva::ESTADOS[$reserva->estado] ?? $reserva->estado" /></p>
            <p>Solicitante: {{ $reserva->responsable_nombre ?? $reserva->socio?->nombre_completo }}</p>
            @if ($reserva->responsable_rut)<p>RUT: {{ $reserva->responsable_rut }}</p>@endif
            @if ($reserva->responsable_direccion)<p>Domicilio: {{ $reserva->responsable_direccion }}</p>@endif
            @if ($reserva->responsable_telefono)<p>Teléfono: {{ $reserva->responsable_telefono }}</p>@endif
            @if ($reserva->canal)<p>Canal: {{ $reserva->canal === 'ventanilla' ? 'Ventanilla' : 'En línea' }}</p>@endif
            @if ($reserva->grupo)<p>Grupo: {{ $reserva->grupo->nombre }}</p>@endif
            <p>{{ $reserva->inicio->format('d/m/Y') }}, {{ $reserva->inicio->format('H:i') }} a {{ $reserva->fin->format('H:i') }}</p>
            @if ($reserva->aporte_variable)
                <p class="font-semibold">Aporte voluntario: {{ $reserva->valor_arriendo === null ? 'Pendiente de definir por presidente o secretario' : ($reserva->valor_arriendo == 0 ? '$0 · Reserva sin aporte' : '$'.number_format($reserva->valor_arriendo, 0, ',', '.')) }}</p>
            @else
                <p>Arriendo histórico: ${{ number_format($reserva->valor_arriendo, 0, ',', '.') }} · Garantía: ${{ number_format($reserva->valor_garantia, 0, ',', '.') }}</p>
                <p class="font-semibold">Total: ${{ number_format($reserva->valor_arriendo + $reserva->valor_garantia, 0, ',', '.') }}</p>
            @endif
            @if ($reserva->aporte_variable && $reserva->valor_garantia > 0)<p>Garantía acordada: ${{ number_format($reserva->valor_garantia, 0, ',', '.') }}</p>@endif
            @if ($reserva->estado === 'pendiente')
                <p>Plazo para validar: {{ $reserva->vence_en->format('d/m/Y H:i') }}. Sin validación, el horario se libera automáticamente.</p>
            @elseif ($reserva->estado === 'vencida')
                <p>La solicitud venció sin validación. El horario quedó liberado.</p>
            @endif
            @if ($reserva->garantia)
                <p>Garantía: {{ \App\Models\Garantia::ESTADOS[$reserva->garantia->estado] }}</p>
            @endif
            @if ($reserva->comprobante_arriendo_path)
                <a class="underline" href="{{ route('arriendos.comprobante', $reserva) }}" target="_blank" rel="noopener">Ver comprobante del total</a>
            @endif
        </section>
        @if ($esDirigente && $reserva->aporte_variable && $reserva->estado === 'pendiente' && $reserva->valor_arriendo === null)
            <form class="ui-panel p-6 space-y-4" method="POST" action="{{ route('arriendos.aporte', $reserva) }}">
                @csrf
                <h3 class="text-lg font-semibold">Definir aporte y garantía</h3>
                <p>Ingresa el monto acordado con el solicitante. $0 corresponde a una reserva sin aporte. El monto se fija una sola vez.</p>
                <x-input-label for="valor_arriendo" value="Aporte voluntario acordado (CLP)" />
                <x-text-input id="valor_arriendo" name="valor_arriendo" type="number" :value="old('valor_arriendo', 0)" min="0" max="4294967295" step="1" required />
                <x-input-error :messages="$errors->get('valor_arriendo')" />
                <x-input-label for="garantia_reserva" value="Garantía acordada (CLP)" />
                <x-text-input id="garantia_reserva" name="garantia_reserva" type="number" :value="old('garantia_reserva', 0)" min="0" max="4294967295" step="1" required />
                <x-input-error :messages="$errors->get('garantia_reserva')" />
                <x-primary-button>Definir aporte y garantía</x-primary-button>
            </form>
        @endif
        @if ($reserva->aporte_variable && $reserva->estado === 'pendiente' && ($reserva->valor_arriendo > 0 || $reserva->valor_garantia > 0) && ! $reserva->comprobante_arriendo_path && ($reserva->canal === 'ventanilla' ? $esDirigente : (auth()->user()->esSocioActivo() && $reserva->socio_id === auth()->user()->socio->id)))
            <form class="ui-panel p-6 space-y-4" method="POST" enctype="multipart/form-data" action="{{ route('arriendos.comprobante.store', $reserva) }}">
                @csrf
                <h3 class="text-lg font-semibold">{{ $reserva->canal === 'ventanilla' ? 'Registrar comprobante recibido en ventanilla' : 'Adjuntar comprobante del aporte' }}</h3>
                <x-input-label for="comprobante" value="Comprobante del total recibido" />
                <x-file-input id="comprobante" name="comprobante" accept="application/pdf,image/jpeg,image/png" required />
                <p>PDF, JPG o PNG, máximo 5 MB. El plazo de confirmación se mantiene.</p>
                <x-input-error :messages="$errors->get('comprobante')" />
                <x-primary-button>Adjuntar comprobante</x-primary-button>
            </form>
        @endif
        @if ($volverFinanzas) @include('finanzas._garantia', ['garantia' => $reserva->garantia, 'esFinanciero' => $esFinanciero]) @endif
        @if ($esDirigente && $reserva->estado === 'pendiente' && (! $reserva->aporte_variable || ($reserva->valor_arriendo !== null && (($reserva->valor_arriendo == 0 && $reserva->valor_garantia <= 0) || $reserva->comprobante_arriendo_path))))
            <form class="ui-panel p-6 space-y-4" method="POST" action="{{ route('arriendos.confirmar', $reserva) }}">
                @csrf
                <h3 class="text-lg font-semibold">Confirmar reserva</h3>
                @if (! $reserva->aporte_variable || $reserva->valor_arriendo > 0)
                    <p>Revisa el comprobante y verifica el aporte recibido antes de confirmar.</p>
                    <label class="flex gap-2"><input type="checkbox" name="pago_verificado" value="1" required @checked(old('pago_verificado'))> Aporte verificado</label>
                    <x-input-error :messages="$errors->get('pago_verificado')" />
                @else
                    <p>La reserva puede confirmarse sin aporte.</p>
                @endif
                @if (! $reserva->aporte_variable || $reserva->valor_garantia > 0)
                    <label class="flex gap-2"><input type="checkbox" name="garantia_verificada" value="1" required @checked(old('garantia_verificada'))> Pago de la garantía verificado</label>
                    <x-input-error :messages="$errors->get('garantia_verificada')" />
                @endif
                <x-primary-button>Confirmar reserva</x-primary-button>
            </form>
        @endif
        @if ($esDirigente && in_array($reserva->estado, \App\Models\Reserva::ESTADOS_OCUPAN, true))
            <form method="POST" action="{{ route('reservas.cancelar', $reserva) }}" onsubmit="return confirm('¿Cancelar la reserva y liberar el horario? Si hay garantía recibida, quedará por devolver.')">
                @csrf
                <x-secondary-button type="submit">Cancelar reserva</x-secondary-button>
            </form>
        @endif
    </div>
</x-app-layout>
