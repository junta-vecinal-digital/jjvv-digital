<x-public-layout>
<section class="ui-panel p-6 sm:p-8">
<h1 class="text-3xl font-semibold tracking-tight">Junta de vecinos</h1>
<p class="my-5 max-w-2xl text-base leading-7 text-gray-500">Consulta cuándo atendemos y verifica la autenticidad de un certificado, sin cuenta.</p>
<a href="{{ route('verificar.formulario') }}" class="ui-button ui-button-primary">Verificar un certificado</a>
</section>
<x-public-hours :horarios="$horariosAtencion" />
</x-public-layout>
