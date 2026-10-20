<x-app-layout>
    <x-slot name="header"><x-page-heading title="Mi perfil" description="Actualiza tus datos de acceso y tu contraseña." /></x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 ui-panel">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

<details class="ui-panel p-6" @if($errors->updatePassword->any()) open @endif><summary class="cursor-pointer font-semibold">Cambiar contraseña</summary><div class="mt-6">@include('profile.partials.update-password-form')</div></details>

            @if (! $user->esDirigente() && ! $user->hasRole('admin'))
                <div class="p-4 sm:p-8 ui-panel">
                    <div class="max-w-xl">@include('profile.partials.delete-user-form')</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
