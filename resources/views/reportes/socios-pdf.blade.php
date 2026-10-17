@extends('reportes.pdf-layout')
@section('titulo', 'Listado de socios activos')
@section('contenido')
    <p>Total: {{ $socios->count() }} socios activos.@if (filled($buscar)) Búsqueda: {{ $buscar }}.@endif</p>
    <p class="nota">Nombre, RUT, estado y fecha de ingreso. No incluye datos de contacto.</p>
    <table><thead><tr><th>Nombre</th><th>RUT</th><th>Estado</th><th>Fecha de ingreso</th></tr></thead><tbody>
        @forelse ($socios as $socio)<tr><td>{{ $socio->nombre_completo }}</td><td>{{ $socio->rut }}</td><td>Activo</td><td>{{ $socio->fecha_ingreso?->format('d/m/Y') ?? 'Sin fecha registrada' }}</td></tr>@empty<tr><td colspan="4">No hay socios activos para esta búsqueda.</td></tr>@endforelse
    </tbody></table>
@endsection
