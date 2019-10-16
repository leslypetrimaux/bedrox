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
        self::print(PHP_EOL . 'Préparation de la configuration de votre application...');
        self::print('Configuration du fichier "./config/security.yaml"');
        self::setSecurity();
        self::print('Configuration terminée.' . PHP_EOL . 'Vous pouvez maintenant utiliser votre application.');
    }

    /**
     * Generate the Security Strategy
     */
    public static function setSecurity(): void
    {
        self::print('Mise en place d\'une stratégie d\'encodage de sécurité...', false);
        self::generateToken(self::ENCODE_ALGO);
        self::print('Génération d\'une clé secrète pour l\'application...', false);
        self::generateToken(self::SECRET_KEY);
    }

    /**
     * Generate Token algo & secret key
     *
     * @param string $type
     * @param int $length
     */
    public static function generateToken(string $type = self::SECRET_KEY, int $length = 48): void
    {
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
                    } else {
                        self::print(' KO. Impossible d\'écrire dans votre fichier de sécurité.');
                    }
                } else {
                    self::print(' KO. La valeur doit être "' . $type . '" pour être réinitialisée.');
                }
            }
        }
    }

    public static function print(string $text = '', bool $eol = true): void
    {
        $newLine = $eol ? PHP_EOL : '';
        print_r($text . $newLine);
    }
}
