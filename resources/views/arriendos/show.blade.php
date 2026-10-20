<x-app-layout>
    <x-slot name="header"><x-page-heading :title="($reserva->aporte_variable ? 'Reserva de sede' : 'Arriendo').' #'.$reserva->id" description="Consulta el horario, el solicitante y el estado de la reserva." /></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        @if ($volverReservas)
            <a class="underline" href="{{ route('reservas.index', ['semana' => $reserva->inicio->copy()->startOfWeek()->toDateString()]) }}">Volver a reservas</a>
        @else
            <a class="ui-link" href="{{ route($volverFinanzas ? 'finanzas.arriendos.index' : 'arriendos.index') }}">{{ $volverFinanzas ? 'Volver al registro de garantías' : 'Volver a reservas' }}</a>
        @endif
        @if (session('status'))
            <p role="status" class="ui-panel p-4">{{ match(session('status')) { 'arriendo-solicitado' => 'Solicitud registrada. El horario quedó reservado dentro del plazo de confirmación.', 'aporte-definido' => 'Aporte definido. El plazo de confirmación se mantiene.', 'comprobante-adjuntado' => 'Comprobante adjuntado para revisión.', 'arriendo-confirmado' => $reserva->aporte_variable ? 'Reserva confirmada.' : 'Arriendo confirmado y garantía registrada como recibida.', 'reserva-cancelada' => 'Reserva cancelada y horario liberado.', 'arriendo-registrado' => $reserva->aporte_variable ? 'Aporte registrado en ingresos.' : 'Aporte de sede registrado en ingresos y garantía registrada por separado.', 'garantia-por-devolver' => 'Garantía marcada como por devolver.', 'garantia-resuelta' => 'Resolución de garantía registrada.', 'retencion-registrada' => 'Ingreso por retención registrado nuevamente.', default => '' } }}</p>
        @endif
        <x-input-error :messages="$errors->get('arriendo')" />
        <x-input-error :messages="$errors->get('reserva')" />
        <x-input-error :messages="$errors->get('finanzas')" />
        @include('arriendos._detalle')
    </div>
</x-app-layout>
