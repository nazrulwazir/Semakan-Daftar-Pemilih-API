<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SemakPemilihController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function semak(Request $request){

        return $this->ApiSpr($request->ic);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $ic
     * @return \Illuminate\Http\Response
     */
    public function ApiSpr($ic = null){

        $url = 'https://pengundi.spr.gov.my/';
        $testCurl = $this->get_web_page($url);

        if($testCurl['http_code'] == 200){

            $dom = new \DOMDocument();

            # Parse the HTML
            # The @ before the method call suppresses any warnings that
            # loadHTML might throw because of invalid HTML in the page.
            @$dom->loadHTML($testCurl['content']);

            // dd($testCurl['content']);

            # Iterate over all the <input> tags
            foreach($dom->getElementsByTagName('input') as $input) {
                    # Show the attribute value
                    $getToken = $input->getAttribute('value');
            }

            if(!empty($getToken)){

                $data = array(
                            'token' => $getToken,
                            'ic'    => $ic,
                        );

                $getData = $this->get_web_page($url,$data);
                $retrieve_data =  $this->retrieve_data($getData['content']);

                return $retrieve_data;

            }
            return $this->return_400();

        }
        return $this->return_502();
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $url , string $hasData
     * @return \Illuminate\Http\Response
     */
    public function get_web_page( $url ,$hasData = null ){

        $strCookie = 'PHPSESSID=' . 'SPR2018' . '; path=/';

        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle compressed
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_POSTFIELDS     => 1,
            CURLOPT_COOKIE         => $strCookie,
        );

        $ch = curl_init( $url );
        curl_setopt_array( $ch, $options);
        if(!empty($hasData)){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $hasData);
        }

        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        
        return $header;
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $content
     * @return \Illuminate\Http\Response
     */
    public function retrieve_data($content){

        try {
            
            $DOM = new \DOMDocument();
            @$DOM->loadHTML($content);
            
            $Header = @$DOM->getElementsByTagName('th');
            $Detail = @$DOM->getElementsByTagName('td');

            //#Get header name of the table
            foreach($Header as $NodeHeader) 
            {
                $aDataTableHeaderHTML[] = trim($NodeHeader->textContent);
            }

            //#Get row data/detail table without header name as key
            $i = 0;
            $j = 0;
            $data = array();

            foreach($Detail as $sNodeDetail) 
            {
                $aDataTableDetailHTML[$j][] = trim($sNodeDetail->textContent);
                $i = $i + 1;
                $j = $i % count($aDataTableHeaderHTML) == 0 ? $j + 1 : $j;
            }

            $data['name'] = strip_tags($aDataTableDetailHTML[0][0]);
            $data['ic_no'] = strip_tags($aDataTableDetailHTML[0][1]);
            $data['dob'] = strip_tags($aDataTableDetailHTML[0][2]);
            $data['gender'] = strip_tags($aDataTableDetailHTML[0][3]);

            $data['lokaliti'] = strip_tags($aDataTableDetailHTML[0][4]);
            $data['daerah'] = strip_tags($aDataTableDetailHTML[0][5]);
            $data['dun'] = strip_tags($aDataTableDetailHTML[0][6]);
            $data['parlimen'] = strip_tags($aDataTableDetailHTML[0][7]);
            $data['negeri'] = strip_tags($aDataTableDetailHTML[0][8]);
            $data['info'] = strip_tags($aDataTableDetailHTML[0][9]);

            return $this->return_200($data);

        } catch (\Exception $e) {
            return $this->return_204();
        }
    }

    public function return_204(){
        return ['code'=>204,'msg'=>'record not found'];
    }

    public function return_400(){
        return ['code'=>400,'msg'=>'wrong request'];
    }

    public function return_502(){
        return ['code'=>502,'msg'=>'bad request'];
    }

    public function return_200($data){
        return ['code'=>200,'msg'=>'success','data'=>$data];
    }

}
