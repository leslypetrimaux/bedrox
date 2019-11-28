<?php

namespace Bedrox\Security;

use Bedrox\Core\Env;
use Bedrox\Core\Exceptions\BedroxException;

class Token extends Base
{
    /**
     * Define Application Token.
     *
     * @param array $firewall
     */
    public function defineToken(array $firewall): void
    {
        if ((!empty($firewall[self::ENCODE]) || !empty($firewall[self::SECRET])) && in_array($firewall[self::ENCODE], hash_algos(), true)) {
            $app = strtr(ucwords($this->session->get('APP_NAME')), ' ', '');
            $token = $app . '-' . $firewall[self::SECRET];
            $this->session->set('APP_TOKEN', hash($firewall[self::ENCODE], $token));
        } else {
            BedroxException::render(
                'ERR_TOKEN_GEN',
                'Unable to generate your application token. Please check "' . $_SERVER['APP'][Env::SECURITY] . '".'
            );
        }
    }
}
