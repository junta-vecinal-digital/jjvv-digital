@if (session('status'))
    <p role="status" class="ui-notice">{{ match(session('status')) {
        'ingreso-registrado' => 'Ingreso registrado.', 'ingreso-anulado' => 'Ingreso anulado.',
        'egreso-registrado' => 'Egreso registrado.', 'egreso-anulado' => 'Egreso anulado.',
        'devolucion-registrada' => 'Devolución registrada.', 'arriendo-registrado' => 'Registro financiero actualizado.',
        'garantia-resuelta' => 'Resolución de garantía registrada.', 'garantia-por-devolver' => 'Garantía marcada por devolver.',
        'retencion-registrada' => 'Ingreso por retención registrado nuevamente.', default => session('status')
    } }}</p>
@endif
@if ($errors->any())<div role="alert" class="ui-notice text-red-700">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
