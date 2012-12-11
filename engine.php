<?php
// PHP TwinMee 0.1
// Forked by TiBounise (http://tibounise.com) based on the inital code of mGeek (http://mgeek.fr)

class TwinMee {
    private $grommo;
    private $cacheFolder;
    private $authcode = '398a392E323k912E12IU';
    public $voices = array('JeanJean','Melodine','electra','Murphy','bicool','Stallone','Sorciere','Tchang','Vampire','Mafioso','sidoo','SuperHeros','actrice','Willaxxx','John');

    public function __construct($grommo = false,$cacheFolder = 'cache') {
        if (is_bool($grommo)) {
            $this->grommo = $grommo;
        }
        if (is_string($cacheFolder)) {
            $this->cacheFolder = $cacheFolder;
        }
    }
    public function voiceSynthesis($voice,$text) {
        if (!in_array($voice,$this->voices)) {
            throw new Exception('This voice you\'ve selected is currently not implemented.');
        }
        if ($this->grommo) {
            $text = $this->grommoFilter($text);
        }
        if (!is_dir($this->cacheFolder)) {
            mkdir($this->cacheFolder);
        }
        $md5 = md5($voice.$text);
        $file = $this->cacheFolder.'/'.$md5.'.mp3';
        if (!file_exists($file)) {
            $post = 'KagedoSynthesis='.urlencode('<KagedoSynthesis><Identification><codeAuth>'.$this->authcode.'</codeAuth></Identification><Result><ResultCode/><ErrorDetail/></Result><MainData><DialogList><Dialog character="'.$voice.'">'.trim(stripslashes(strip_tags($text))).'</Dialog></DialogList></MainData></KagedoSynthesis>');
            $twinmeeXML = $this->curlJob($post);
            if (preg_match('/url="(.+?)"/',$twinmeeXML,$regexXML)) {
                $link = $regexXML[1];
                file_put_contents($file, file_get_contents($link));
            } else {
                throw new Exception('TwinMee has probably changed its APIs. We can\'t get a correct URL.');
            }
        }
        return $file;   
    }
    public function grommoFilter($text) {
        $text = ' '.$text.' ';
        $grommoDB = array(
            'bite' => 'bit',
            'cul' => 'ku',
            'putain' => 'puh tin',
            'shit' => 'shi ihte');
        foreach ($grommoDB as $normal => $equivalent) {
            $text = str_replace(' '.$normal.' ',' '.$equivalent.' ', $text);
        }
        return $text;
    }
    public function voiceList($voice = 'Agnes') {
        $list = '';
        foreach ($this->voices as $voice) {
            if (isset($_POST['voice']) AND $voice == $_POST['voice']) {
                $list .= '<option selected>'.$voice.'</option>';
            } else {
                $list .= '<option>'.$voice.'</option>';
            }
        }
        return $list;
    }
    private function curlJob($post) {
        $curlHandler = curl_init('http://webservice.kagedo.fr/nsynthesis/ws/makenewsound');
        curl_setopt($curlHandler, CURLOPT_HEADER, false);
        curl_setopt($curlHandler, CURLOPT_POST, true);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curlHandler);
        curl_close($curlHandler);
        return $output;
    }
}

?>