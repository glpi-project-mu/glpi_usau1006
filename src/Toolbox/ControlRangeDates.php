<?php
namespace Glpi\Toolbox;

Class ControlRangeDates {

    public static function controlEndDateMinValue($endIdNumber){
         //Controlamos la fecha minima que podrá seleccionar el EndDatefield cuando se
        //asigna fecha al BeginDatefield
        $controlMinDate = "
        const fpEndDate = document.querySelector('#showdate{$endIdNumber}')._flatpickr
        const newENDminDate = new Date(dateStr);
        newENDminDate.setDate(newENDminDate.getDate() + 1);
        fpEndDate.config.minDate = newENDminDate;
        ";

        return $controlMinDate;
    }

    public static function controlBeginDateMaxValue(int $beginIdNumber){
        //Controlamos la fecha máxima que podrá seleccionar el BeginDatefield cuando se
        //asigna fecha al EndDatefield
        $controlMaxDate = "
        const fpBeginDate = document.querySelector('#showdate{$beginIdNumber}')._flatpickr
        const newBEGINmaxDate = new Date(dateStr);
        
        fpBeginDate.config.maxDate = newBEGINmaxDate;
        ";

        return $controlMaxDate;
    }
}