<?php

namespace Bedrox\Google\Firebase;

class RealtimeDatabase extends Firebase
{
    protected const BASE = '/';
    protected const FB_EOL = '.json';
    protected $uri;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->uri = $config['databaseURL'];
    }

    /**
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        return $this->uri . self::BASE;
    }

    /**
     * @param string $path
     * @return string|null
     */
    public function getUriPath(string $path): ?string
    {
        try {
            if (!empty($path)) {
                return $this->uri . self::BASE . $path . self::FB_EOL;
            }
            throw new FirebaseException('Impossible de récupérer le chemin demandé. Veuillez vérifier votre requête Firestore.');
        } catch (FirebaseException $e) {
            exit($e);
        }
    }

    /**
     * @param string $path
     * @param int $id
     * @return string|null
     */
    public function getUriPathWithId(string $path, int $id): ?string
    {
        try {
            if (!empty($path) && !empty($id)) {
                return $this->uri . self::BASE . $path . self::BASE . $id . self::FB_EOL;
            }
            throw new FirebaseException('Impossible de récupérer le chemin demandé. Veuillez vérifier votre requête Firestore.');
        } catch (FirebaseException $e) {
            exit($e);
        }
    }
}