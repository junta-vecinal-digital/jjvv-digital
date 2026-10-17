@props(['options'])

<datalist id="destinatarios-certificado">
    @foreach ($options as $destinatario)
        <option value="{{ $destinatario }}"></option>
    @endforeach
</datalist>
