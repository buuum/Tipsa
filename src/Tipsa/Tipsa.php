<?php

namespace Buuum;

class Tipsa
{

    /**
     * @var string
     */
    private $url = 'http://webservices.tipsa-dinapaq.com:8099/SOAP?service=';

    /**
     * @var string
     */
    private $agencia;

    /**
     * @var string
     */
    private $cliente;

    /**
     * @var string
     */
    private $password;

    /**
     * @var null
     */
    private $session_id = null;

    /**
     * Tipsa constructor.
     * @param $agencia
     * @param $cliente
     * @param $password
     */
    public function __construct($agencia, $cliente, $password)
    {
        $this->agencia = $agencia;
        $this->cliente = $cliente;
        $this->password = $password;
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getIncidenciasByDate($date)
    {
        $parameters = [];
        $parameters['dtFecha'] = $date;

        if ($result = $this->call('WebServService', 'ConsEnvIncidenciasFecha', $parameters)) {
            return $result['ENV_INCIDENCIAS'];
        }

        return $result;

    }

    /**
     * @param $date
     * @return mixed
     */
    public function getEstadoEnviosByDate($date)
    {
        $parameters = [];
        $parameters['dtFecha'] = $date;

        if ($result = $this->call('WebServService', 'ConsEnvEstadosFecha', $parameters)) {
            return $result['ENV_ESTADOS'];
        }

        return $result;

    }

    /**
     * @param $date
     * @return int|mixed
     */
    public function getEnviosByDate($date)
    {
        $parameters = [];
        $parameters['dtFecha'] = $date;

        if ($result = $this->call('WebServService', 'InfEnvios', $parameters)) {
            return $result['INF_ENVIOS'];
        }

        return $result;

    }

    /**
     * @param $referencia
     * @return array|bool|mixed
     */
    public function getByReference($referencia)
    {
        $parameters = [];
        $parameters['strRef'] = $referencia;

        if ($result = $this->call('WebServService', 'ConsEnvEstadosRef', $parameters)) {
            return $this->parseEnvios($result);
        }

        return $result;

    }

    /**
     * @param $results
     * @return array
     */
    private function parseEnvios($results)
    {
        $sends = [];
        foreach ($results['ENV_ESTADOS_REF'] as $k => $result) {
            if ($k == '@attributes') {
                $sends[] = [
                    'date'      => empty($result['D_FEC_HORA_ALTA']) ? '' : $result['D_FEC_HORA_ALTA'],
                    'code_type' => empty($result['V_COD_TIPO_EST']) ? '' : $result['V_COD_TIPO_EST'],
                    'code'      => empty($result['V_COD_TIPO_EST']) ? '' : $this->getCode($result['V_COD_TIPO_EST']),
                ];
            } else {
                $sends[] = [
                    'date'      => empty($result['@attributes']['D_FEC_HORA_ALTA']) ? '' : $result['@attributes']['D_FEC_HORA_ALTA'],
                    'code_type' => empty($result['@attributes']['V_COD_TIPO_EST']) ? '' : $result['@attributes']['V_COD_TIPO_EST'],
                    'code'      => empty($result['@attributes']['V_COD_TIPO_EST']) ? '' : $this->getCode($result['@attributes']['V_COD_TIPO_EST']),
                ];
            }
        }

        usort($sends, function ($a, $b) {
            return $a['date'] >= $b['date'];
        });

        return $sends;
    }

    /**
     * @param $service
     * @param $method
     * @param $parameters
     * @return mixed
     */
    private function call($service, $method, $parameters)
    {
        $request = $this->buildRequest($service, $method, $parameters);

        if ($service == 'WebServService' && !$this->session_id) {
            $this->login();
        }

        $xml = $this->buildXml($request);
        $url = $this->url . $service;
        $result = $this->request($url, $xml);
        if ($service == 'WebServService') {
            $result = str_replace('&lt;', '<', $result);
            $result = str_replace('&gt;', '>', $result);

            $re = '@(<CONSULTA>.*</CONSULTA>)@ms';
            preg_match($re, $result, $matches);
            $result = false;
            if (!empty($matches[0])) {
                $xml = simplexml_load_string($matches[0], "SimpleXMLElement", LIBXML_NOCDATA);
                $xml = json_encode($xml);
                $result = json_decode($xml, true);
            }
        }
        return $result;
    }

    /**
     * @param $url
     * @param $xml
     * @return mixed
     */
    private function request($url, $xml)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, utf8_encode($xml));
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $request
     * @return string
     */
    private function buildXml($request)
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
				<soap:Envelope
				xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
				xmlns:xsd="http://www.w3.org/2001/XMLSchema">
				' . $this->buildXmlHeader() . '
				<soap:Body>
				' . $request . '
				</soap:Body>
			</soap:Envelope>
			';
        return $xml;
    }

    /**
     * @return string
     */
    private function buildXmlHeader()
    {
        $header = '';
        if (!empty($this->session_id)) {
            $header = '
				<soap:Header>
                    <ROClientIDHeader xmlns="http://tempuri.org/">
                        <ID>' . $this->session_id . '</ID>
                    </ROClientIDHeader>
                </soap:Header>
            ';
        }
        return $header;
    }

    /**
     * @param $service
     * @param $method
     * @param $parameters
     * @return string
     */
    private function buildRequest($service, $method, $parameters)
    {
        $res = '<' . $service . '___' . $method . '>';
        foreach ($parameters as $key => $value) {
            $res .= '<' . $key . '>' . $value . '</' . $key . '>';
        }
        $res .= '</' . $service . '___' . $method . '>';
        return $res;
    }

    /**
     * @param $response
     * @return array
     * @throws \Exception
     */
    private function setLogin($response)
    {

        $login = true;

        $re = '@<v1:(\w+)>([^<]+)<@ms';
        preg_match_all($re, $response, $matches);

        if (empty($matches[0])) {
            $login = false;
            $re = '@<fault(\w+)>([^>]+)<@ms';
            preg_match_all($re, $response, $matches);
        }

        $response = [];
        foreach ($matches[1] as $n => $match) {
            $response[$match] = $matches[2][$n];
        }

        if (!$login) {
            throw new \Exception(implode(' => ', $response));
        } else {
            $this->session_id = $response['strSesion'];
        }

        return $response;

    }

    /**
     * @return array
     */
    private function login()
    {
        $parameters = [];
        $parameters['strCodAge'] = $this->agencia;
        $parameters['strCod'] = $this->cliente;
        $parameters['strPass'] = $this->password;

        $result = $this->call('LoginWSservice', 'LoginCli', $parameters);

        return $this->setLogin($result);
    }

    /**
     * @param $code
     * @return mixed|string
     */
    private function getCode($code)
    {
        $messages_by_code = [
            '1'  => 'Tránsito',
            '2'  => 'Reparto',
            '3'  => 'Entregado',
            '4'  => 'Incidencia',
            '5'  => 'Devuelto',
            '6'  => 'Falta de expedición',
            '7'  => 'Recanalizado',
            '9'  => 'Falta de expedición administrativa',
            '10' => 'Destruído',
            '14' => 'Disponible',
            '15' => 'Entrega parcial'
        ];

        return (!empty($messages_by_code[$code])) ? $messages_by_code[$code] : 'Indeterminado';
    }

}
