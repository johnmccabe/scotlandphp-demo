<?php

namespace App;

/**
 * Class Handler
 * @package App
 */
class Handler
{
    /**
     * @param $data
     * @return
     */
    public function handle($data)
    {
        $request = \Requests::get('https://www.belfastairport.com/FlightData.ashx?dir=dep&lang=EN');

        if ($request->status_code != 200) {
	    # Non-0 exit code returns 5xx
            exit($request->status_code);
        }

        $response = $request->body;

        $dom = new \DOMDocument;
        $dom->loadHTML($response);
        $xpath = new \DOMXPath($dom);

        $keys = [];

        foreach ($xpath->query('//thead/tr') as $thead) {
            $i = 0;
            foreach ($xpath->query("th", $thead) as $th) {
                $i++;
                $key = trim($th->textContent);
                switch ($key) {
                    case "Flight No":
                        $key = "flight_number";
                        break;
                    case "Airport":
                        $key = "airport";
                        break;
                    case "Scheduled":
                        $key = "scheduled";
                        break;
                    case "Flight Status":
                        $key = "status";
                        break;
                }
                $keys[$i] = $key;
            }
        }

        foreach ($xpath->query('//tbody/tr') as $tr) {
            $tmp = [];
            $i = 0;
            foreach ($xpath->query("td", $tr) as $td) {
                $i++;
                $tmp[$keys[$i]] = trim($td->textContent);
            }
            $result[] = $tmp;
        }
        return json_encode($result);
    }
}
