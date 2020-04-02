<?php

/**
 * Class Here
 */
class Here
{
    /**
     * @var string token de acesso a api
     */
    private $apiKey;

    /**
     * @var string
     */
    private $geocoderUrlBase;
    /**
     * @var string
     */
    private $geocoderPath;
    /**
     * @var string
     */
    private $geocoderResource;
    /**
     * @var string
     */
    private $geocoderFormat;
    /**
     * @var int
     */
    private $geocoderGen;

    /**
     * @var string
     */
    private $routingUrlBase;
    /**
     * @var string
     */
    private $routingPath;
    /**
     * @var string
     */
    private $routingResource;
    /**
     * @var string
     */
    private $routingFormat;
    /**
     * Here constructor.
     * @param string $Token token de acesso a api
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        $this->geocoderUrlBase = "https://geocoder.ls.hereapi.com/";
        $this->geocoderPath = "6.2/";
        $this->geocoderResource = "geocode";
        $this->geocoderFormat = "json";
        $this->geocoderGen = 9;

        $this->routingUrlBase = "https://route.ls.hereapi.com/";
        $this->routingPath = "routing/7.2/";
        $this->routingResource = "calculateroute";
        $this->routingFormat = "json";
    }

    /**
     * @param string $address
     * @return stdClass
     */
    public function getLatLog(string $address): stdClass
    {
        $address = $this->Name($address);

        $param = [
            "searchtext" => $address,
            "gen" => $this->geocoderGen,
            "apiKey" => $this->apiKey,
            "country" => "BRA",
            "language" => "pt-br"
        ];

        $url = "{$this->geocoderUrlBase}{$this->geocoderPath}{$this->geocoderResource}.{$this->geocoderFormat}?" . http_build_query($param);
        $return = json_decode($this->file_get_contents_curl($url), true);

        $location = new stdClass();
        if(!empty($return['Response']['View'])):
            $location->type = $return['Response']['View'][0]['Result'][0]['Location']['LocationType'];
            $location->coordinates = new stdClass();
            $location->coordinates->lat = $return['Response']['View'][0]['Result'][0]['Location']['DisplayPosition']['Latitude'];
            $location->coordinates->lon = $return['Response']['View'][0]['Result'][0]['Location']['DisplayPosition']['Longitude'];
            $location->address = new stdClass();
            $location->address->label = $return['Response']['View'][0]['Result'][0]['Location']['Address']['Label'];
            $location->address->country = $return['Response']['View'][0]['Result'][0]['Location']['Address']['Country'];
            $location->address->state = $return['Response']['View'][0]['Result'][0]['Location']['Address']['State'];
            $location->address->city = $return['Response']['View'][0]['Result'][0]['Location']['Address']['City'];
            $location->address->district = $return['Response']['View'][0]['Result'][0]['Location']['Address']['District'];
            $location->address->street = $return['Response']['View'][0]['Result'][0]['Location']['Address']['Street'];
            $location->address->houseNumber = $return['Response']['View'][0]['Result'][0]['Location']['Address']['HouseNumber'];
            $location->address->postalCode = $return['Response']['View'][0]['Result'][0]['Location']['Address']['PostalCode'];
        endif;

        return $location;
    }

    /**
     * @param string $coordinatesA
     * @param string $coordinatesB
     * @return stdClass
     */
    public function getDirection(string $coordinatesA, string $coordinatesB): stdClass
    {
        $param = [
            "waypoint0" => $coordinatesA,
            "waypoint1" => $coordinatesB,
            "mode" => "fastest;car;traffic:disabled",
            "departure" => "now",
            "apiKey" => $this->apiKey,
            "country" => "BRA",
            "language" => "pt-br"
        ];

        $url = "{$this->routingUrlBase}{$this->routingPath}{$this->routingResource}.{$this->routingFormat}?" . http_build_query($param);
        $return = json_decode($this->file_get_contents_curl($url), true);

        $route = new stdClass();
        if(!empty($return['response']['route'])):
            $route->distance = new stdClass();
            $route->distance->meters = $return['response']['route'][0]['summary']['distance'];
            $route->distance->km = $route->distance->meters / 1000;
            $route->duration = new stdClass();
            $route->duration->seconds = $return['response']['route'][0]['summary']['trafficTime'];
            $route->duration->minute = $route->duration->seconds / 60;
            $route->duration->hours = $route->duration->seconds / 3600;
            $route->coordinates = new stdClass();
            $route->coordinates->pointA =  $coordinatesA;
            $route->coordinates->pointB =  $coordinatesB;
        endif;

        return $route;
    }

    /**
     * @param $url url para pesquisa
     * @return string retorno json
     */
    private function file_get_contents_curl($url): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * @param $Name Endereço para tirar toda acentuação
     * @return string Endereço sem acentuação
     */
    private function Name($Name) :string
    {
        $Format = array();
        $Format['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        $Format['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

        $Data = strtr(utf8_decode($Name), utf8_decode($Format['a']), $Format['b']);
        $Data = str_replace(' - ', ',', $Data);
        $Data = str_replace(',', '', $Data);
        $Data = str_replace(' ', ',', $Data);
        $Data = strip_tags(trim($Data));

        return strtolower(utf8_encode($Data));
    }
}