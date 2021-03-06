<?php
namespace Api\Controller;
use Think\Controller;
//优付
class FinePayController extends Controller{
    
    /**
     * 优付无卡快捷支付绑定
     */
    public function NoCardOpenServlet()
    {
        $org_id='YKF';
        $mid="YKF18666666666";
        $acctNo="6215590200003242971";
        $phone='17569615504';
        $systrace=uniqid();
        $secret="RSA";
        $arr=array(
            'acctNo'=>$acctNo,//信用卡号
            'app_notify_url'=>'http://xys.dev.com/',//前台返回地址
            'mid'=>$mid,//商户号
            'org_id'=>$org_id,//机构号
            'phone'=>$phone,//预留手机号
            'sp_txn_id'=>'4',//快捷开通
            'systrace'=>$systrace,//商户订单号
        );
        $arr['sign']=$this->getSign($arr);
        $url="http://www.557vip.cn/YkfQRCodeProxy/NoCardOpenServlet";
    }

    public function getSign($arr){
        $secret="RSA";
        #1去空
        $arr=$this->RemoveEmpty($arr);
        #2拼接成字符串
        $string=http_build_query($arr).$secret;
        #3RSA加密
        $RSA=$this->encrypt($string);
        var_dump($string);die;
    }
    public function encrypt($data){
        $private_key = '-----BEGIN RSA PRIVATE KEY-----  
        MIICXQIBAAKBgQC3//sR2tXw0wrC2DySx8vNGlqt3Y7ldU9+LBLI6e1KS5lfc5jl  
        TGF7KBTSkCHBM3ouEHWqp1ZJ85iJe59aF5gIB2klBd6h4wrbbHA2XE1sq21ykja/  
        Gqx7/IRia3zQfxGv/qEkyGOx+XALVoOlZqDwh76o2n1vP1D+tD3amHsK7QIDAQAB  
        AoGBAKH14bMitESqD4PYwODWmy7rrrvyFPEnJJTECLjvKB7IkrVxVDkp1XiJnGKH  
        2h5syHQ5qslPSGYJ1M/XkDnGINwaLVHVD3BoKKgKg1bZn7ao5pXT+herqxaVwWs6  
        ga63yVSIC8jcODxiuvxJnUMQRLaqoF6aUb/2VWc2T5MDmxLhAkEA3pwGpvXgLiWL  
        3h7QLYZLrLrbFRuRN4CYl4UYaAKokkAvZly04Glle8ycgOc2DzL4eiL4l/+x/gaq  
        deJU/cHLRQJBANOZY0mEoVkwhU4bScSdnfM6usQowYBEwHYYh/OTv1a3SqcCE1f+  
        qbAclCqeNiHajCcDmgYJ53LfIgyv0wCS54kCQAXaPkaHclRkQlAdqUV5IWYyJ25f  
        oiq+Y8SgCCs73qixrU1YpJy9yKA/meG9smsl4Oh9IOIGI+zUygh9YdSmEq0CQQC2  
        4G3IP2G3lNDRdZIm5NZ7PfnmyRabxk/UgVUWdk47IwTZHFkdhxKfC8QepUhBsAHL  
        QjifGXY4eJKUBm3FpDGJAkAFwUxYssiJjvrHwnHFbg0rFkvvY63OSmnRxiL4X6EY  
        yI9lblCsyfpl25l7l5zmJrAHn45zAiOoBrWqpM5edu7c  
        -----END RSA PRIVATE KEY-----';  
          
        $public_key = '-----BEGIN PUBLIC KEY-----  
        MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3//sR2tXw0wrC2DySx8vNGlqt  
        3Y7ldU9+LBLI6e1KS5lfc5jlTGF7KBTSkCHBM3ouEHWqp1ZJ85iJe59aF5gIB2kl  
        Bd6h4wrbbHA2XE1sq21ykja/Gqx7/IRia3zQfxGv/qEkyGOx+XALVoOlZqDwh76o  
        2n1vP1D+tD3amHsK7QIDAQAB  
        -----END PUBLIC KEY-----';  
          
        //echo $private_key;  
        // $pi_key =  openssl_pkey_get_private($private_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id  
        // $pu_key = openssl_pkey_get_public($public_key);//这个函数可用来判断公钥是否是可用的  
        // print_r($pi_key);echo "\n";  die;
        // print_r($pu_key);echo "\n";  
        
        $encrypted = "";   
        $decrypted = "";   
          
        // echo "source data:",$data,"\n";
          
        echo "private key encrypt:\n";  
          
        openssl_private_encrypt($data,$encrypted,$pi_key);//私钥加密  
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的  
        echo $encrypted,"\n";  
          
        echo "public key decrypt:\n";  
          
        openssl_public_decrypt(base64_decode($encrypted),$decrypted,$pu_key);//私钥加密的内容通过公钥可用解密出来  
        echo $decrypted,"\n";  
          
        echo "---------------------------------------\n";  
        echo "public key encrypt:\n";  
          
        openssl_public_encrypt($data,$encrypted,$pu_key);//公钥加密  
        $encrypted = base64_encode($encrypted);  
        echo $encrypted,"\n";  
          
        echo "private key decrypt:\n";  
        openssl_private_decrypt(base64_decode($encrypted),$decrypted,$pi_key);//私钥解密  
        echo $decrypted,"\n";  
    }
    /**
     * 去除空值
     * @param [type] $arr [description]
     */
    public function RemoveEmpty($arr){
        foreach ($arr as $k => $v) {
           if(!$v){
             unset($arr[$k]);
           }
        }
        return $arr;
    }
    /**
     * 数组按照ASCII码排序
     * @return [type] [description]
     */
    public function SortByASCII($arr){
        $keys=array_keys($arr);
        $newrr=[];
        foreach ($keys as $k => $v) {
            $newrr[$k]['asc']=ord($v);
            $newrr[$k]['key']=$v;
            $keys[$k]=ord($v);
        }
        array_multisort($keys, SORT_ASC, $newrr);
        $return=[];
        foreach ($newrr as $k => $v) {
           $return[$v['key']]=$arr[$v['key']];
        }
        return $return;
    }

}