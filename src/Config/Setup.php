<?php

namespace Bedrox\Config;

use Bedrox\Core\Env;

class Setup
{
    public const SECRET_KEY = 'secretKey';
    public const ENCODE_ALGO = 'encodeAlgo';
    public const TOKEN_CHARS = 'AZERTYUIOPQSDFGHJKLMWXCVBNazertyuiopqsdfghjklmwxcvbn,;:!?./§ù*%µ^$¨£¤&é#{([-|è`_\ç^à@)]=}0123456789';

    /**
     * Install & configure components
     */
    public static function PostInstall(): void
    {
        self::setSecurity();
    }

    /**
     * Generate the Security Strategy
     */
    public static function setSecurity(): void
    {
        self::generateToken();
    }

    /**
     * Generate Token algo & secret key
     *
     * @param string $type
     * @param int $length
     */
    public static function generateToken(string $type = self::SECRET_KEY, int $length = 48): void
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . Env::FILE_SECURITY;
        $real = realpath($file);
        if (file_exists($real)) {
            $content = file_get_contents($file);
            $action = array(
                self::SECRET_KEY,
                self::ENCODE_ALGO
            );
            if (!empty($type) && in_array($type, $action)) {
                if (preg_match('/(' . $type . ')/', $content)) {
                    $replace = '';
                    switch ($type) {
                        case self::ENCODE_ALGO:
                            $algo = hash_algos();
                            $algoLength = count($algo);
                            $replace = $algo[rand(0, $algoLength - 1)];
                            break;
                        case self::SECRET_KEY:
                        default:
                            $chars = self::TOKEN_CHARS;
                            $charsLength = strlen($chars);
                            for ($i=0; $i < $length; $i++) {
                                $char = $chars[rand(0, $charsLength - 1)];
                                if ($char != ' ') {
                                    $replace .= utf8_encode($char);
                                } else {
                                    $i--;
                                }
                            }
                            break;
                    }
                    $content = str_replace($type, $replace, $content);
                    file_put_contents($file, $content);
                }
            }
        }
    }
}
