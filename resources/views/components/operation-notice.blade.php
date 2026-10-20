@if(session('status'))<p role="status" class="ui-notice">{{ str_contains(session('status'), ' ') ? session('status') : 'Operación completada.' }}</p>@endif
@if($errors->any())<div role="alert" class="ui-notice text-red-700">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
