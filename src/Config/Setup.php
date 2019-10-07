<?php

namespace Bedrox\Config;

class Setup
{
    public static function PostInstall(): void
    {
        self::generateSecurity();
    }

    public static function generateSecurity(int $length = 48): void
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/../config/security.yaml';
        $real = realpath($file);
        if (file_exists($real)) {
            $content = file_get_contents($file);
            if (preg_match('/(secretKey)/', $content)) {
                $chars = 'AZERTYUIOPQSDFGHJKLMWXCVBNazertyuiopqsdfghjklmwxcvbn,;:!?./§ù*%µ^$¨£¤&é#{([-|è`_\ç^à@)]=}0123456789';
                $charsLength = strlen($chars);
                $secret = '';
                for ($i=0; $i < $length; $i++) {
                    $char = $chars[rand(0, $charsLength - 1)];
                    if ($char != ' ') {
                        $secret .= utf8_encode($char);
                    } else {
                        $i--;
                    }
                }
                $content = str_replace('secretKey', $secret, $content);
                file_put_contents($file, $content);
            }
        }
    }
}
