<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Models\Certificado;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        View::composer('layouts.navigation', function ($view) {
            $pendientesMenu = ['socios' => false, 'certificados' => false, 'arriendos' => false, 'insumos' => false, 'prestamos' => false];
            if (Auth::user()?->hasRole('presidente', 'secretario')) {
                $pendientesMenu = [
                    'socios' => Socio::where('estado', 'pendiente')->exists(),
                    'certificados' => Certificado::where('estado', 'solicitado')->exists(),
                    'arriendos' => Reserva::ocupan()->where('tipo', 'arriendo')->where('estado', 'pendiente')->exists(),
                    'insumos' => SolicitudInsumo::where('estado', 'pendiente')->exists(),
                    'prestamos' => SolicitudInsumo::where('estado', 'entregado')->whereDate('fecha_comprometida', '<', today())->exists(),
                ];
            }
            $pendientesMenu['finanzas'] = false;
            if (Auth::user()?->hasRole('presidente', 'tesorero')) {
                $pendientesMenu['finanzas'] = Reserva::where('tipo', 'arriendo')->whereNotNull('validado_en')
                    ->where(fn ($q) => $q->where(fn ($q) => $q->where('valor_arriendo', '>', 0)->whereDoesntHave('ingresos', fn ($q) => $q->vigentes()))
                        ->orWhereHas('garantia', fn ($q) => $q->where('monto', '>', 0)->whereNull('registrada_en')))->exists()
                    || SolicitudInsumo::whereNotNull('pago_validado_en')->whereIn('estado', ['aprobado', 'entregado', 'devuelto'])
                        ->where(fn ($q) => $q->where('monto', '>', 0)->orWhere('garantia', '>', 0))
                        ->where(fn ($q) => $q->whereNull('pago_registrado_en')
                            ->orWhere(fn ($q) => $q->where('monto', '>', 0)->whereDoesntHave('ingresos', fn ($q) => $q->vigentes())))->exists()
                    || Certificado::where('tipo', 'residencia')->where('estado', 'emitido')->where('aporte', '>', 0)->whereDoesntHave('ingresos')->exists();
            }
            $view->with('pendientesMenu', $pendientesMenu);
        });
    }
}
