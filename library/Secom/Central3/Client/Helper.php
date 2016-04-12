<?php

namespace Secom\Central3\Client;

class Helper
{
    public static function eventosToJson($eventos)
    {
        foreach ($eventos as $evento) {
            if (strtotime($evento->fim) > strtotime($evento->inicio))
                $evento->fim = date('Y-m-d', strtotime("+1 days", strtotime($evento->fim)));
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

    public static function normalizeEvento($evento)
    {
        $normalize = $evento;
        $normalize->valor = Helper::normalizeValor($evento);
        $normalize->data = Helper::normalizeData($evento);
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
        $data['inicio'] = preg_replace("@^([0-9]{4})-([0-9]{2})-([0-9]{2}) (.*)@", "$3/$2/$1", $evento->inicio);
        if (strtotime($evento->fim) > strtotime($evento->inicio))
            $data['fim'] = preg_replace("@^([0-9]{4})-([0-9]{2})-([0-9]{2}) (.*)@", "$3/$2/$1", $evento->fim);
        return $data;
    }

}
