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
            $view->with('pendientesMenu', $pendientesMenu);
        });
    }
}
