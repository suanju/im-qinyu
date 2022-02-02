<?php

namespace app\common\business\lib;


class Str
{

    //生成token
    public function createToken($str){
        $tokenSalt = md5(uniqid(md5(microtime(true)), true));
        return sha1($tokenSalt . $str);
    }

    //生成盐
    public function salt($bit) {
        // 盐字符集
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i = 0; $i < $bit; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    /**
     * 将数组按字母A-Z排序
     * @return [type] [description]
     */
    public function chartSort($user){
        $user = $user;
        foreach ($user as $k => &$v) {
            $v['chart'] = $this->getFirstChart( $v['username'] );
        }
        $data=[];
        foreach ($user as $k => $v) {
            if ( empty( $data[ $v['chart'] ] ) ) {
                $data[ $v['chart'] ]=[];
            }
            $data[ $v['chart'] ][]=$v;
        }
        ksort($data);
        return $data;
    }
    /**
     * 返回取汉字的第一个字的首字母
     * @param  [type] $str [string]
     * @return [type]      [strind]
     */
    public function getFirstChart($str){
        if( empty($str) ){
            return '';
        }
        $char=ord($str[0]);
        if( $char >= ord('A') && $char <= ord('z') ){
            return strtoupper($str[0]);
        }
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s= $s2 == $str?$s1:$str;
        $asc=ord($s[0])*256+ord($s[1])-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return '#';
        return null;
    }
    /**
     * ws 无法使用公共函数引入需要毫秒时间戳
     */
   public function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }




}