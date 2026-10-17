<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Reserva;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('arriendos:liberar-vencidos', function () {
    $this->info('Solicitudes vencidas liberadas: '.Reserva::liberarVencidos());
})->purpose('Liberar arriendos pendientes cuyo plazo de validación venció');

Schedule::command('arriendos:liberar-vencidos')->everyMinute()->withoutOverlapping();
