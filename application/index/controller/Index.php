<?php
namespace app\index\controller;

use Sunra\PhpSimple\HtmlDomParser;
use think\Db;

class Index
{
    protected $types = ['release_date','acceptor','money','expiration_date','every_hundred_thousand','flaw','seller','operation'];

    public function index()
    {
        $url = 'https://www.tcpjw.com/OrderList/TradingCenter';
        $html = $this->curl($url, 1);
        $data = $this->handleHtml($html);
        $this->saveToDb($data);
    }

    /*
     * 处理html
     *
     * */
    public function handleHtml( $html ){
        $data = [];
        $dom = HtmlDomParser::str_get_html( $html );
        $elemets = $dom->find('#tb tr');
        foreach ($elemets as $rowNum => $row){
            if ($rowNum >= count($elemets) - 1) {
                break;
            }
            $data[$rowNum]['order_sn'] = ($rowNum +=1);
            $tds = $row->find('td');
            foreach ($tds as $colNum => $col){
                $data[$rowNum][$this->types[$colNum]] = trim($col->plaintext);
            }
        }
        return $data;
    }

    public function saveToDb($data){
        $r = Db::name('order')->insertAll($data);
        dump($r);
    }

    public function curl($szUrl,$postData){
        $UserAgent = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; .NET CLR 3.0.04506; .NET CLR 3.5.21022; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $szUrl);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        return curl_exec($curl);
    }
}
