<?php
  namespace Api\Controller;
  use Think\Controller;
class MCryptController {
     private $iv  = '0102030405060708'; 
     private $encryptKey = 'U1MjU1M0FDOUZ.Qz'; 
    public function __construct() {
      $this->iv=C('hex_iv');
      $this->encryptKey=C('quckpayment_aes_key');
      // $this->encryptKey = hash('sha256', $this->key, true);
      //echo $this->key.'<br/>';
    }
    //加密
    public function encrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
 
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
 
        //print "module = $module <br/>" ;
 
        mcrypt_generic_init($module, $encryptKey, $localIV);
 
        //Padding
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($encryptStr) % $block); //Compute how many characters need to pad
        $encryptStr .= str_repeat(chr($pad), $pad); // After pad, the str length must be equal to block or its integer multiples
 
        //encrypt
        $encrypted = mcrypt_generic($module, $encryptStr);
 
        //Close
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);
        return base64_encode($encrypted);
    }
 
    //解密
    public function decrypt($encryptStr) {
        $localIV = $this->iv;
        $encryptKey = $this->encryptKey;
 
        //Open module
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, $localIV);
 
        //print "module = $module <br/>" ;
 
        mcrypt_generic_init($module, $encryptKey, $localIV);
 
        $encryptedData = base64_decode($encryptStr);
        $encryptedData = mdecrypt_generic($module, $encryptedData);
 
        return $encryptedData;
    }
}
?>