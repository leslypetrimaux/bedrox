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
                    'Vous devez définir au moins une route pour informer de l\'accès privé de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
                );
            }
            switch ($firewall[self::TYPE]) {
                case self::TOKEN:
                    (new Token())->defineToken($firewall);
                    break;
                case self::ENTITY:
                    BedroxException::render(
                        'ERR_FIREWALL_ENTITY',
                        'Impossible de configurer le firewall de l\'Application pour l\'authentification avec un token & une entité. Veuillez vérifier votre fichier "./security.yaml".'
                    );
                    break;
                default:
                    BedroxException::render(
                        'ERR_FIREWALL_PARSING',
                        'Impossible de configurer le type du firewall de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
                    );
                    break;
            }
        } else {
            BedroxException::render(
                'ERR_FIREWALL_TYPE',
                'Impossible de configurer le firewall de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
            );
        }
        if (empty($_SESSION['APP_TOKEN'])) {
            BedroxException::render(
                'ERR_SESSION',
                'Une erreur s\'est produite lors de la lecture/écriture de la session courante. Merci de supprimer le cache de l\'Application.'
            );
        }
        return $firewall;
    }
}
