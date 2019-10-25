<?php

namespace Bedrox\Config;

use Bedrox\Core\Env;

class Setup
{
    public const SECRET_KEY = 'secretKey';
    public const ENCODE_ALGO = 'encodeAlgo';

    public const TOKEN_CHARS = 'AZERTYUIOPQSDFGHJKLMWXCVBNazertyuiopqsdfghjklmwxcvbn,;:!?./§ù*%µ^$¨£¤&é#{([-|è`_\ç^à@)]=}0123456789';

    public const TOKEN_ACTIONS = array(
        self::SECRET_KEY,
        self::ENCODE_ALGO
    );

    /**
     * Install & configure components
     */
    public static function PostInstall(): void
    {
        self::print(PHP_EOL . 'Preparing your application...');
        self::setSecurity();
        self::print('You can now use your application. Enjoy ! :-)');
    }

    /**
     * Generate the Security Strategy
     */
    public static function setSecurity(): void
    {
        $algo = $key = false;
        self::print('Configuring security file "./config/security.yaml" :');
        self::print('Setting an encoding strategy...', false);
        $algo = self::generateToken(self::ENCODE_ALGO);
        if ($algo) {
            self::print('Generating your application secret key...', false);
            $key = self::generateToken(self::SECRET_KEY);
            if ($key) {
                self::print('Your new configuration is ready.');
            }
        }
        if (!$algo || !$key) {
            self::print('The process was unable to continue properly.');
        }
    }

    /**
     * Generate Token algo & secret key
     *
     * @param string $type
     * @param int $length
     * @return bool
     */
    public static function generateToken(string $type = self::SECRET_KEY, int $length = 48): bool
    {
        $res = false;
        $file = $_SERVER['DOCUMENT_ROOT'] . Env::FILE_SECURITY_ROOT;
        $real = realpath($file);
        if (file_exists($real)) {
            $content = file_get_contents($file);
            if (!empty($type) && in_array($type, self::TOKEN_ACTIONS)) {
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
                    if (file_put_contents($file, $content)) {
                        self::print(' OK.');
                        $res = true;
                    } else {
                        self::print(' KO.');
                        self::print('/!\ Unable to write in your file');
                    }
                } else {
                    self::print(' KO.');
                    self::print('/!\ The value must be "' . $type . '" to be reinitialize.');
                }
            }
        }
        return $res;
    }

    public static function print(string $text = '', bool $eol = true): void
    {
        $newLine = $eol ? PHP_EOL : '';
        print_r($text . $newLine);
    }
}
