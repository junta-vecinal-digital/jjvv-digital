@props(['monto'])
<div class="space-y-2 text-sm">
    <p>Estimado vecino, favor realizar transferencia bancaria por ${{ number_format($monto, 0, ',', '.') }} utilizando los siguientes datos:</p>
    <ul class="list-disc pl-5">
        <li>Banco: {{ \App\Models\Parametro::valor('transferencia_banco') }}</li>
        <li>Titular: {{ \App\Models\Parametro::valor('transferencia_titular') }}</li>
        <li>Cuenta: {{ \App\Models\Parametro::valor('transferencia_cuenta') }}</li>
        <li>RUT: {{ \App\Models\Parametro::valor('transferencia_rut') }}</li>
        <li>Email: {{ \App\Models\Parametro::valor('transferencia_email') }}</li>
    </ul>
</div>
