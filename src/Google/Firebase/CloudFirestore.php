<?php

namespace Bedrox\Google\Firebase;

use Bedrox\Google\Firebase\Firestore\Collection;
use Bedrox\Google\Firebase\Firestore\Collections;
use Bedrox\Google\Firebase\Firestore\Document;
use Bedrox\Google\Firebase\Firestore\Documents;

class CloudFirestore extends Firebase
{
    protected $con;

    public $collections;
    public $collection;
    public $documents;
    public $document;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->collections = new Collections();
        $this->collection = new Collection();
        $this->documents = new Documents();
        $this->document = new Document();
    }

    /**
     * @return CloudFirestore
     */
    public function getDatabase(): self
    {
        try {
            if ($this->connect($this->config)) {
                // TODO: implements database connexion
            } else {
                throw new FirebaseException('Impossible de se connecter à la base Firebase Cloud Firestore. Vérifier votre fichier "./firebase.conf.json".');
            }
        } catch (FirebaseException $e) {
            dd($e);
        }
        return $this;
    }
}