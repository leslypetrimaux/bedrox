<?php

namespace Bedrox\Config;

class Setup
{
    public static function PostInstall(): void
    {
        self::generateSecurity();
    }

    public static function generateSecurity(): void
    {
        $file = __DIR__ . '/../config/security.yaml';
        $content = file_get_contents($file);
        if (preg_match('/(secretKey)/', $content)) {
            $chars = 'AZERTYUIOPQSDFGHJKLMWXCVBNazertyuiopqsdfghjklmwxcvbn,;:!?./§ù*%µ^$¨£¤&é#{([-|è`_\ç^à@)]=}0123456789';
            $charsLength = strlen($chars);
            $secret = '';
            for ($i=0; $i < 48; $i++) {
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
