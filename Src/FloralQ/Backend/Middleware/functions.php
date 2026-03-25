<?php
// FORMATAÇÃO DE TEMPO PARA VALOR QUE O USER PRECEBE
function formatLastReading($seconds)
{
    if ($seconds < 60) {
        return $seconds . " segundos";
    }

    $minutes = floor($seconds / 60);

    if ($minutes < 60) {
        return $minutes . " minutos";
    }

    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;

    if ($minutes == 0) {
        return $hours . " horas";
    }

    return $hours . " hora(s) e " . $minutes . " minuto(s)";
}
