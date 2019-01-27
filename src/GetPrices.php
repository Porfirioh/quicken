<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 22/10/17
 * Time: 13:08
 */
namespace Simon77\Quicken;

use GuzzleHttp\Client;

require '../vendor/autoload.php';

class GetPrices
{
    public function __construct()
    {
        $symbols = array(
            'EME' => 'GB0007906794:GBX',
            'EQU' => 'GB0007828709:GBX',
            'PAC' => 'GB0007801532:GBX',
            'GBL' => 'GB0007906687:GBX',
            'PP2' => 'GB00B09CD637:GBX',
            'MAN' => 'GB00B1VNF546:GBX',
            'GB00BYW6SY38' => 'GB00BYW6SY38:GBX',
            'GB00BYNYY264' => 'GB00BYNYY264:GBX',
            'GB00BV9FRD45' => 'GB00BV9FRD45:GBX',
            'GB00BV9FRG75' => 'GB00BV9FRG75:GBX',
            'GB00BYM58175' => 'GB00BYM58175:GBX'
        );

        //Tuesday, September 05, 2017Tue, Sep 05, 2017
        $ftFormat = 'l, F d, YD, M d, Y';

        $client = new Client();

        $endDate = new \DateTime();
//        $startDate = clone $endDate;
//        $startDate->modify('-1 month');

        $priceFile = fopen("../prices/{$endDate->format('Y-m-d')}.txt", "w") or die("Unable to open file!");

        foreach ($symbols as $key => $symbol) {
            $query = "s={$symbol}";
            $response = $client->get("https://markets.ft.com/data/funds/tearsheet/historical?{$query}");

            $html = $response->getBody()->getContents();
            $dom = new \DOMDocument();

            @$dom->loadHTML($html);

            $finder = new \DomXPath($dom);
            $classname="mod-tearsheet-overview__quote__bar";
            $quoteBar = $finder->query("//*[contains(@class, '$classname')]");

            foreach ($quoteBar as $node) {
                foreach ($node->childNodes as $child) {
                    $priceUnit = substr($child->firstChild->textContent, 7, 3);
                    break;
                }

            }

            foreach ($dom->getElementsByTagName('tbody') as $tbody) {

                foreach ($tbody->childNodes as $rows) {
                    $nodeArray = array();
                    foreach ($rows->childNodes as $nodes) {
                        $nodeArray[] = $nodes->nodeValue;
                    }

                    $data = array(
                        'date' => \DateTime::createFromFormat($ftFormat, $nodeArray[0]),
                        'price' => sprintf("%01.2f", floatval(str_replace(',', '', $nodeArray[1])))
                    );
                    if ($priceUnit === 'GBX') {
                        $data['price'] = $data['price']/100;
                    }

                    fwrite($priceFile, "{$key}, {$data['price']}, {$data['date']->format('d/m/y')}\r\n");
                }
            }

        }
        fclose($priceFile);
    }
}

