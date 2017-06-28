<?php
namespace Home\Controller;
use Think\Controller;
class PageController extends Controller {

    public function test(){
        $detail['device_number'] = '12345';
        $detail['qrcode'] = '654321';

        if($detail){
            //if(!file_exists(APP_PATH."/../uploads/qrcode/".$detail['device_number'] .".png"))
            {
                if(!file_exists(APP_PATH."/../uploads/qrcode/")){
                    mkdir(APP_PATH."/../uploads/qrcode/",0755, true);
                }
                //
                $sign = encrypt_password($detail['qrcode'], $detail['id']);
                $value = C('base_url')."index.php?s=/index/index/type/1/gfrom/2/qr/{$detail['qrcode']}/sign/{$sign}.html";
                include APP_PATH."/../ThinkPHP/Library/Vendor/phpqrcode/phpqrcode.php";
                $errorCorrectionLevel = 'L';//容错级别
                $matrixPointSize = 12;//生成图片大小
                //生成二维码图片
                \QRcode::png($value, APP_PATH."/../uploads/qrcode/".$detail['device_number'] .".png", $errorCorrectionLevel, $matrixPointSize, 2);
            }



            $logo = APP_PATH . '/../Public/images/logo.png';//需要显示在二维码中的Logo图像
            $QR = APP_PATH."/../uploads/qrcode/".$detail['device_number'] .".png";
            if ($logo !== FALSE) {
                $QR = imagecreatefromstring ( file_get_contents ( $QR ) );
                $logo = imagecreatefromstring ( file_get_contents ( $logo ) );
                $QR_width = imagesx ( $QR );
                $QR_height = imagesy ( $QR );
                $logo_width = imagesx ( $logo );
                $logo_height = imagesy ( $logo );
                $logo_qr_width = $QR_width / 5;
                $scale = $logo_width / $logo_qr_width;
                $logo_qr_height = $logo_height / $scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;

                $font = APP_PATH ."../Public/fonts/msyhbd.ttf";
                $red = imagecolorallocate($QR, 250,0, 0);
                imagettftext($QR, 22, 0, $QR_width/2 - 30, $QR_height- 0, $red, $font,$detail['device_number']);

                imagecopyresampled ( $QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height );
            }


            imagepng ( $QR, 'ewmlogo.png' );//带Logo二维码的文件名

            echo "<img src='/ewmlogo.png' />";
            // return header("location: /uploads/qrcode/{$detail['device_number']}.png");
        }else{
            return $this->error("没找到设备信息~");
        }

    }
}