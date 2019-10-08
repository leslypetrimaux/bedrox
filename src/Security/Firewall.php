<?php

namespace Bedrox\Security;

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
            self::SECRET => $this->core[self::FIREWALL][self::TOKEN][self::SECRET],
            self::ENCODE => $this->core[self::FIREWALL][self::TOKEN][self::ENCODE],
            self::TYPE => $this->core[self::FIREWALL][self::TYPE],
            self::ENTITY => $this->core[self::FIREWALL][self::ENTITY],
            self::ANONYMOUS => array()
        );
        if ($firewall[self::TYPE] === self::TOKEN) {
            if (empty($this->core[self::FIREWALL][self::ANONYMOUS])) {
                BedroxException::render(
                    'ERR_FIREWALL_ANONYMOUS',
                    'Vous devez définir au moins une route pour informer de l\'accès privé de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
                );
            }
            if (!empty($this->core[self::FIREWALL][self::ANONYMOUS])) {
                if (is_array($this->core[self::FIREWALL][self::ANONYMOUS])) {
                    foreach ($this->core[self::FIREWALL][self::ANONYMOUS] as $key => $value) {
                        $firewall[self::ANONYMOUS][] = $value;
                    }
                } else {
                    $firewall[self::ANONYMOUS][] = $this->core[self::FIREWALL][self::ANONYMOUS];
                }
            } else {
                BedroxException::render(
                    'ERR_FIREWALL_PARSING',
                    'Impossible de configurer le firewall de l\'Application avec des routes anonymes. Veuillez vérifier votre fichier "./security.yaml".'
                );
            }
        }
        (new Token())->defineToken($firewall[self::ENCODE], $firewall[self::SECRET]);
        if (empty($_SESSION['APP_TOKEN'])) {
            BedroxException::render(
                'ERR_SESSION',
                'Une erreur s\'est produite lors de la lecture/écriture de la session courante. Merci de supprimer le cache de l\'Application.'
            );
        }
        return $firewall;
    }
}
