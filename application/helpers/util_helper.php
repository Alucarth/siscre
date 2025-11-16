<?php 
class Util
{
    public static $PIE = 1;
    public static $MILIMETRO = 2;
    public static $CENTIMETRO = 3;
    public static $METRO = 4;

    public static function removeSpaces($text)
    {
        $re = '/\s+/';
        $subst = ' ';
        $result = preg_replace($re, $subst, $text);
        return $result ? trim($result) : null;
    }
    public static function formatMoney($value, $prefix = false)
    {
        if ($value) {
            $value = number_format($value, 2, '.', ',');
            if ($prefix) {
                return 'Bs' . $value;
            }
            return $value;
        }
        return 0;
    }
    public static function parseMoney($value)
    {
        $value = str_replace("Bs", "", $value);
        $value = str_replace(",", "", $value);
        return floatval(self::removeSpaces($value));
    }
   
    public static function verifyBarDate($value)
    {
        $re = $re = '/^\d{1,2}\/\d{1,2}\/\d{4}$/m';
        preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
        return (sizeOf($matches) > 0);
    }
    public static function verifyDashDate($value)
    {
        $re = $re = '/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/m';
        preg_match_all($re, $value, $matches, PREG_SET_ORDER, 0);
        return (sizeOf($matches) > 0);
    }

    public static function parseNumber($input)
    {
        $val = str_replace(',', '', $input);
        return $val;
        // $next_val = str_replace('.', '', $input);
    }
    public static function twoDecimals($value)
    {
        $val = number_format((float)$value, 2);
        return $val;
    }
    public static function fiveDecimals($value)
    {
        $val = number_format((float)$value, 5);
        return $val;
    }

    public static function zeroDecimals($value)
    {
        $val = number_format((float)$value);
        return $val;
    }


    public static function convertirNumeroLetra($numero,$currency){

        $decimal = explode(".",$numero); //dividendo la parte entera de la fraccionaria

        $numf = self::milmillon($decimal[0]);

        if(sizeof($decimal)>1)
        {
            return $numf." con ".$decimal[1]."/100 ".$currency;
        }
        return $numf." ".$currency;
    }

    public static function milmillon($nummierod){
        if ($nummierod >= 1000000000 && $nummierod <2000000000){
            $num_letrammd = "Mil ".(self::cienmillon($nummierod%1000000000));
        }
        if ($nummierod >= 2000000000 && $nummierod <10000000000){
            $num_letrammd = self::unidad(Floor($nummierod/1000000000))." mil ".(self::cienmillon($nummierod%1000000000));
        }
        if ($nummierod < 1000000000)
            $num_letrammd = self::cienmillon($nummierod);

        return $num_letrammd;
    }

    public static function cienmillon($numcmeros){
        if ($numcmeros == 100000000)
            $num_letracms = "Cien millones";
        if ($numcmeros >= 100000000 && $numcmeros <1000000000){
            $num_letracms = self::centena(Floor($numcmeros/1000000))." millones ".(self::millon($numcmeros%1000000));
        }
        if ($numcmeros < 100000000)
            $num_letracms = self::decmillon($numcmeros);
        return $num_letracms;
    }

    public static function decmillon($numerodm){
        if ($numerodm == 10000000)
            $num_letradmm = "Diez millones";
        if ($numerodm > 10000000 && $numerodm <20000000){
            $num_letradmm = self::decena(Floor($numerodm/1000000))."millones ".(self::cienmiles($numerodm%1000000));
        }
        if ($numerodm >= 20000000 && $numerodm <100000000){
            $num_letradmm = self::decena(Floor($numerodm/1000000))." millones ".(self::millon($numerodm%1000000));
        }
        if ($numerodm < 10000000)
            $num_letradmm = self::millon($numerodm);

        return $num_letradmm;
    }

    public static function millon($nummiero){
        if ($nummiero >= 1000000 && $nummiero <2000000){
            $num_letramm = "Un millon ".(self::cienmiles($nummiero%1000000));
        }
        if ($nummiero >= 2000000 && $nummiero <10000000){
            $num_letramm = self::unidad(Floor($nummiero/1000000))." millones ".(self::cienmiles($nummiero%1000000));
        }
        if ($nummiero < 1000000)
            $num_letramm = self::cienmiles($nummiero);

        return $num_letramm;
    }

    public static function cienmiles($numcmero){
        if ($numcmero == 100000)
            $num_letracm = "Cien mil";
        if ($numcmero >= 100000 && $numcmero <1000000){
            $num_letracm = self::centena(Floor($numcmero/1000))." mil ".(self::centena($numcmero%1000));
        }
        if ($numcmero < 100000)
            $num_letracm = self::decmiles($numcmero);
        return $num_letracm;
    }


    public static function decmiles($numdmero){
        if ($numdmero == 10000)
            $numde = "Diez mil";
        if ($numdmero > 10000 && $numdmero <20000){
            $numde = self::decena(Floor($numdmero/1000))."mil ".(self::centena($numdmero%1000));
        }
        if ($numdmero >= 20000 && $numdmero <100000){
            $numde = self::decena(Floor($numdmero/1000))." mil ".(self::miles($numdmero%1000));
        }
        if ($numdmero < 10000)
            $numde = self::miles($numdmero);

        return $numde;
    }

    public static function miles($nummero){
        $numm="";
        if ($nummero >= 1000 && $nummero < 2000){
            $numm = "Mil ".(self::centena($nummero%1000));
        }
        if ($nummero >= 2000 && $nummero <10000){
            $numm = self::unidad(Floor($nummero/1000))." mil ".(self::centena($nummero%1000));
        }
        if ($nummero < 1000)
            $numm = self::centena($nummero);

        return $numm;
    }

    public static function centena($numc){
        $numce="";
        if ($numc >= 100)
        {
            if ($numc >= 900 && $numc <= 999)
            {
                $numce = "Novecientos ";
                if ($numc > 900)
                    $numce = $numce.(self::decena($numc - 900));
            }
            else if ($numc >= 800 && $numc <= 899)
            {
                $numce = "Ochocientos ";
                if ($numc > 800)
                    $numce = $numce.(self::decena($numc - 800));
            }
            else if ($numc >= 700 && $numc <= 799)
            {
                $numce = "Setecientos ";
                if ($numc > 700)
                    $numce = $numce.(self::decena($numc - 700));
            }
            else if ($numc >= 600 && $numc <= 699)
            {
                $numce = "Seiscientos ";
                if ($numc > 600)
                    $numce = $numce.(self::decena($numc - 600));
            }
            else if ($numc >= 500 && $numc <= 599)
            {
                $numce = "Quinientos ";
                if ($numc > 500)
                    $numce = $numce.(self::decena($numc - 500));
            }
            else if ($numc >= 400 && $numc <= 499)
            {
                $numce = "Cuatrocientos ";
                if ($numc > 400)
                    $numce = $numce.(self::decena($numc - 400));
            }
            else if ($numc >= 300 && $numc <= 399)
            {
                $numce = "Trecientos ";
                if ($numc > 300)
                    $numce = $numce.(self::decena($numc - 300));
            }
            else if ($numc >= 200 && $numc <= 299)
            {
                $numce = "Docientos ";
                if ($numc > 200)
                    $numce = $numce.(self::decena($numc - 200));
            }
            else if ($numc >= 100 && $numc <= 199)
            {
                if ($numc == 100)
                    $numce = "Cien ";
                else
                    $numce = "Ciento ".(self::decena($numc - 100));
            }
        }
        else
            $numce = self::decena($numc);

        return $numce;
    }

    public static function decena($numdero){
        $numd="";
        if ($numdero >= 90 && $numdero <= 99)
        {
            $numd = "Noventa ";
            if ($numdero > 90)
                $numd = $numd."y ".(self::unidad($numdero - 90));
        }
        else if ($numdero >= 80 && $numdero <= 89)
        {
            $numd = "Ochenta ";
            if ($numdero > 80)
                $numd = $numd."y ".(self::unidad($numdero - 80));
        }
        else if ($numdero >= 70 && $numdero <= 79)
        {
            $numd = "Setenta ";
            if ($numdero > 70)
                $numd = $numd."y ".(self::unidad($numdero - 70));
        }
        else if ($numdero >= 60 && $numdero <= 69)
        {
            $numd = "Sesenta ";
            if ($numdero > 60)
                $numd = $numd."y ".(self::unidad($numdero - 60));
        }
        else if ($numdero >= 50 && $numdero <= 59)
        {
            $numd = "Cincuenta ";
            if ($numdero > 50)
                $numd = $numd."y ".(self::unidad($numdero - 50));
        }
        else if ($numdero >= 40 && $numdero <= 49)
        {
            $numd = "Cuarenta ";
            if ($numdero > 40)
                $numd = $numd."y ".(self::unidad($numdero - 40));
        }
        else if ($numdero >= 30 && $numdero <= 39)
        {
            $numd = "Treinta ";
            if ($numdero > 30)
                $numd = $numd."y ".(self::unidad($numdero - 30));
        }
        else if ($numdero >= 20 && $numdero <= 29)
        {
            if ($numdero == 20)
                $numd = "Veinte ";
            else
                $numd = "Veinti".(self::unidad($numdero - 20));
        }
        else if ($numdero >= 10 && $numdero <= 19)
        {
            switch ($numdero){
            case 10:
            {
                $numd = "Diez ";
                break;
            }
            case 11:
            {
                $numd = "Once ";
                break;
            }
            case 12:
            {
                $numd = "Doce ";
                break;
            }
            case 13:
            {
                $numd = "Trece ";
                break;
            }
            case 14:
            {
                $numd = "Catorce ";
                break;
            }
            case 15:
            {
                $numd = "Quince ";
                break;
            }
            case 16:
            {
                $numd = "Dieciseis ";
                break;
            }
            case 17:
            {
                $numd = "Diecisiete ";
                break;
            }
            case 18:
            {
                $numd = "Dieciocho ";
                break;
            }
            case 19:
            {
                $numd = "Diecinueve ";
                break;
            }
            }
        }
        else
            $numd = self::unidad($numdero);
    return $numd;
    }

    public static function unidad($numuero){
        $numu = "";
        switch ($numuero)
        {
            case 9:
            {
                $numu = "Nueve";
                break;
            }
            case 8:
            {
                $numu = "Ocho";
                break;
            }
            case 7:
            {
                $numu = "Siete";
                break;
            }
            case 6:
            {
                $numu = "Seis";
                break;
            }
            case 5:
            {
                $numu = "Cinco";
                break;
            }
            case 4:
            {
                $numu = "Cuatro";
                break;
            }
            case 3:
            {
                $numu = "Tres";
                break;
            }
            case 2:
            {
                $numu = "Dos";
                break;
            }
            case 1:
            {
                $numu = "Un";
                break;
            }
            case 0:
            {
                $numu = "";
                break;
            }
        }
        return $numu;
    }
}
if (!function_exists('monto_literal_bs')) {
    function monto_literal_bs(float $amount): string {
        // Aseguramos 2 decimales con punto
        $s = number_format($amount, 2, '.', '');
        // Usa la clase Util que ya tienes
        return Util::convertirNumeroLetra($s, 'BOLIVIANOS');
    }
}
