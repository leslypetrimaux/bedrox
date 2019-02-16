<?php

namespace Bedrox\Google\Firebase;


class CloudFirestore extends Firebase
{
    protected const BASE = '/';

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        return self::BASE;
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getUriPath(string $path): ?string
    {
        try {
            if (!empty($path)) {
                return self::BASE . $path;
            }
            throw new FirebaseException('Impossible de récupérer le chemin demandé. Veuillez vérifier votre requête Firestore.');
        } catch (FirebaseException $e) {
            exit($e);
        }
    }
}