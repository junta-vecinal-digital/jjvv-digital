@php
    $esValido = $c->estado === 'emitido';
    $marca = match ($c->estado) {
        'solicitado' => 'BORRADOR - SIN VALIDEZ',
        'rechazado' => 'RECHAZADO',
        'anulado' => 'ANULADO',
        default => null,
    };
    $fechaEmision = ($c->fecha_emision ?? now());
    $titulo = $c->tipo === 'socio' ? 'CERTIFICADO DE SOCIO' : 'CERTIFICADO DE RESIDENCIA';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 60px 70px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12.5px; color: #111; line-height: 1.7; }
        .encabezado { text-align: center; border-bottom: 2px solid #333; padding-bottom: 12px; margin-bottom: 28px; }
        .encabezado .org { font-size: 17px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        h1 { text-align: center; font-size: 18px; margin: 0 0 6px 0; letter-spacing: 2px; }
        .folio { text-align: center; font-size: 12px; margin-bottom: 30px; color: #333; }
        p { text-align: justify; margin: 0 0 16px 0; }
        .firma { margin-top: 90px; text-align: center; }
        .firma .linea { width: 260px; border-top: 1px solid #111; margin: 0 auto 4px auto; }
        .verificacion { margin-top: 60px; border: 1px solid #999; padding: 8px 12px; font-size: 10.5px; color: #333; }
        .marca { position: fixed; top: 38%; left: 0; width: 100%; text-align: center; font-size: 46px;
                 font-weight: bold; color: #cc0000; opacity: 0.18; transform: rotate(-25deg); }
    </style>
</head>
<body>
    @if ($marca)
        <div class="marca">{{ $marca }}</div>
    @endif

    <div class="encabezado">
        <div class="org">{{ $junta }}</div>
    </div>

    <h1>{{ $titulo }}</h1>
    <div class="folio">Folio N° {{ $c->folio ?? 'sin asignar' }}</div>

    @if ($c->tipo === 'socio')
        <p>
            La directiva de la {{ $junta }} certifica que el/la vecino/a
            <strong>{{ $c->nombre_solicitante }}</strong>, cédula de identidad N° <strong>{{ $c->rut_solicitante }}</strong>,
            domiciliado/a en {{ $c->direccion }}, es <strong>socio/a activo/a</strong> de esta organización
            @if ($c->socio?->fecha_ingreso)
                desde el {{ $c->socio->fecha_ingreso->translatedFormat('d \d\e F \d\e Y') }}.
            @else
                y se encuentra inscrito/a en el libro de registro de socios.
            @endif
        </p>
    @else
        <p>
            La directiva de la {{ $junta }} certifica que el/la vecino/a
            <strong>{{ $c->nombre_solicitante }}</strong>, cédula de identidad N° <strong>{{ $c->rut_solicitante }}</strong>,
            es <strong>residente</strong> de la jurisdicción de esta organización, con domicilio en {{ $c->direccion }}.
        </p>
    @endif

    <p>
        Se extiende el presente certificado a petición del/de la interesado/a, para ser presentado ante
        <strong>{{ $c->dirigido_a }}</strong>.
    </p>

    @if ($esValido)
        <p>
            Emitido el {{ $fechaEmision->translatedFormat('d \d\e F \d\e Y') }}.
            Válido hasta el {{ $c->fecha_vencimiento->translatedFormat('d \d\e F \d\e Y') }}.
        </p>
    @else
        <p>Fecha de revisión: {{ now()->translatedFormat('d \d\e F \d\e Y') }}.</p>
    @endif

    <div class="firma">
        <div class="linea"></div>
        @if ($c->otorgante_nombre)
            <strong>{{ $c->otorgante_nombre }}</strong><br>
            {{ $c->otorgante_cargo }} - {{ $junta }}
        @else
            Firma y timbre de la directiva
            @if ($c->fecha_emision)<br><small>Identidad histórica del otorgante no registrada.</small>@endif
        @endif
    </div>

    @if ($esValido)
        <div class="verificacion">
            Verifique la autenticidad de este certificado en <strong>{{ url('/verificar') }}</strong>
            ingresando el código <strong>{{ $c->codigoFormateado() }}</strong>,
            o directamente en: {{ $c->urlVerificacion() }}
        </div>
    @endif
</body>
</html>
