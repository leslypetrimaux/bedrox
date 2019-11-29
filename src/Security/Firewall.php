<?php

namespace Bedrox\Security;

use Bedrox\Core\Env;
use Bedrox\Core\Exceptions\BedroxException;

class Firewall extends Base
{

    /**
     * Return a Firewall array from Security file configuration.
     *
     * @return array|null
     */
    public function getFirewall(): ?array
    {
        $firewall = array(
            self::TYPE => $this->security[self::FIREWALL][self::TYPE],
            self::SECRET => !empty($this->security[self::FIREWALL][self::TOKEN][self::SECRET]) ? $this->security[self::FIREWALL][self::TOKEN][self::SECRET] : null,
            self::ENCODE => !empty($this->security[self::FIREWALL][self::TOKEN][self::ENCODE]) ? $this->security[self::FIREWALL][self::TOKEN][self::ENCODE] : null,
            self::ENTITY => !empty($this->security[self::FIREWALL][self::ENTITY]) ? $this->security[self::FIREWALL][self::ENTITY] : null,
            self::ANONYMOUS => array()
        );
        if (!empty($firewall[self::TYPE])) {
            if (!empty($this->security[self::FIREWALL][self::ANONYMOUS])) {
                if (is_array($this->security[self::FIREWALL][self::ANONYMOUS])) {
                    foreach ($this->security[self::FIREWALL][self::ANONYMOUS] as $key => $value) {
                        $firewall[self::ANONYMOUS][] = $value;
                    }
                } else {
                    $firewall[self::ANONYMOUS][] = $this->security[self::FIREWALL][self::ANONYMOUS];
                }
            } else {
                BedroxException::render(
                    'ERR_FIREWALL_ANONYMOUS',
                    'Your must declare at least one public route. Please check "' . $_SERVER['APP'][Env::SECURITY] . '".'
                );
            }
            switch ($firewall[self::TYPE]) {
                case self::NOAUTH:
                case self::TOKEN:
                    (new Token)->defineToken($firewall);
                    break;
                case self::ENTITY:
                    BedroxException::render(
                        'ERR_FIREWALL_ENTITY',
                        'Entity authentication is not available yet. Please check "' . $_SERVER['APP'][Env::SECURITY] . '".'
                    );
                    break;
                default:
                    BedroxException::render(
                        'ERR_FIREWALL_PARSING',
                        'Unable to configure your firewall application. Please check "' . $_SERVER['APP'][Env::SECURITY] . '".'
                    );
                    break;
            }
        } else {
            BedroxException::render(
                'ERR_FIREWALL_TYPE',
                'Unable to configure your firewall application. Please check "' . $_SERVER['APP'][Env::SECURITY] . '".'
            );
        }
        if (empty($_SESSION['APP_TOKEN'])) {
            BedroxException::render(
                'ERR_SESSION',
                'An error occurs while creating your session. Remove your application cache and try to reload.'
            );
        }
        return $firewall;
    }
}
