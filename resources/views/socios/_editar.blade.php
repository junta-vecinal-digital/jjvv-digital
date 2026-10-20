@php
    $operacion = 'socio_'.$socio->id;
    $reintento = old('operacion') === $operacion;
    $valor = fn ($campo, $defecto = null) => $reintento ? old($campo, $defecto) : $defecto;
    $errors = $reintento ? $errors : new \Illuminate\Support\ViewErrorBag;
@endphp
                <x-form-wizard :steps="['Datos del socio', 'Antecedentes']" :fields="[['nombre_completo','rut','email','direccion','telefono'],['residente_desde','carnet','boleta']]">
                    <form novalidate method="POST" action="{{ route('socios.actualizar', $socio) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf
<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="editar">
                        @method('PUT')

                        @include('socios._datos', ['socio' => $socio])

                        <x-wizard-actions :cancel="false" />
                    </form>
                </x-form-wizard>
