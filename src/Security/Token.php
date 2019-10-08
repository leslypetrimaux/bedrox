<?php

namespace Bedrox\Security;

use Bedrox\Core\Exceptions\BedroxException;

class Token extends Base
{
    /**
     * Define Application Token.
     *
     * @param string $encode
     * @param string $token
     */
    public function defineToken(string $encode, string $token): void
    {
        if ((!empty($encode) || !empty($token)) && in_array($encode, hash_algos(), true)) {
            $app = str_replace(' ', '', ucwords($this->session->get('APP_NAME')));
            $token = $app . '-' . $token;
            $this->session->set('APP_TOKEN', hash($encode, $token));
        } else {
            BedroxException::render(
                'ERR_TOKEN',
                'Impossible de générer le token de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
            );
        }
    }
}
