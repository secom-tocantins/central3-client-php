<?php

namespace Secom\Central3\Client;

class Helper
{
    public static function eventosToJson($eventos, $app)
    {
        foreach ($eventos as $evento) {
            $array[] = [
                'title' => $evento->titulo,
                'start' => $evento->inicio,
                'end' => $evento->fim,
                'url' => $app->request->basePath . $evento->uri
            ];
        }
        $json = json_encode($array);
        return $json;
    }

    public static function toBRL($get_valor, $reverse = false)
    {
        if ($reverse) {
            $source = array('.', ',');
            $replace = array('', '.');
        } else {
            $source = array(',', '.');
            $replace = array('', ',');
        }
        $valor = str_replace($source, $replace, $get_valor);
        return $valor;

    }

    public static function normalizeEvento($evento, $app)
    {
        $normalize = $evento;
        $normalize->valor = Helper::normalizeValor($evento);
        $normalize->inicio = Helper::normalizeData($evento);
        return $normalize;
    }

    public static function normalizeValor($evento)
    {
        if ($evento->valor > 0) {
            $valor = "Inscrição: R$ " . Helper::toBRL($evento->valor);
        } else {
            $valor = 'Entrada franca.';
        }
        return $valor;
    }

    public static function normalizeData($evento)
    {
        $data=preg_replace("@^([0-9]{4})-([0-9]{2})-([0-9]{2}) (.*)@", "$3/$2/$1", $evento->inicio);
        return $data;
    }

}