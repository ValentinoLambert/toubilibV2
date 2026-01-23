# TD 2.3 : Communication par courtier de messages

## Exercice 1 : Architecture

---

### 1. Schéma des services concernés

#### Services fonctionnels :

**Service Producteur :**

- **Microservice RDV** (`app-rdv`) : détecte et produit les événements liés aux rendez-vous (création, annulation)

**Services Consommateurs :**

- **Service d'envoi de mail** (à créer) : consomme les événements et envoie des emails
- _Évolutions futures possibles :_
  - Service d'envoi de SMS
  - Service de notifications push mobile

#### Composant technique :

**Courtier de messages :**

- **RabbitMQ** : serveur de messages utilisant le protocole AMQP

#### Schéma simplifié :

```
┌─────────────────────┐
│  Microservice RDV   │ (Producteur)
│    (app-rdv)        │
└──────────┬──────────┘
           │
           │ Publie événements
           │ (RDV créé, RDV annulé)
           ▼
┌──────────────────────┐
│     RabbitMQ         │ (Courtier de messages)
│   (Exchange TOPIC)   │
└──────────┬───────────┘
           │
           │ Route vers les queues
           ├────────────────────┬────────────────────┐
           ▼                    ▼                    ▼
    ┌──────────┐        ┌──────────┐        ┌──────────┐
    │  Queue   │        │  Queue   │        │  Queue   │
    │  Email   │        │   SMS    │        │  Push    │
    └─────┬────┘        └─────┬────┘        └─────┬────┘
          │                   │                    │
          ▼                   ▼                    ▼
    ┌──────────┐        ┌──────────┐        ┌──────────┐
    │ Service  │        │ Service  │        │ Service  │
    │  Email   │        │   SMS    │        │  Push    │
    └──────────┘        └──────────┘        └──────────┘
   (Consommateur)     (Consommateur)     (Consommateur)
                       (future)           (future)
```

---

### 2. Configuration du courtier de messages

#### Type d'exchange :

**TOPIC exchange**

**Justification :**

- Permet un routage flexible basé sur des clés de routage (routing keys)
- Supporte l'extension vers de nouveaux types de notifications
- Permet aux consommateurs de s'abonner à des sous-ensembles d'événements spécifiques

#### Nombre de queues :

**3 queues** (pour anticiper les évolutions) :

1. **Queue "mail"** : pour les notifications email
2. **Queue "sms"** : pour les notifications SMS (future)
3. **Queue "push"** : pour les notifications push mobile (future)

#### Bindings (liaisons) :

**Clés de routage proposées :**

- `rdv.created.*` : événement de création de RDV
- `rdv.cancelled.*` : événement d'annulation de RDV

**Patterns de binding :**

| Queue | Binding Pattern | Description                    |
| ----- | --------------- | ------------------------------ |
| mail  | `rdv.*`         | Reçoit tous les événements RDV |
| sms   | `rdv.*`         | Reçoit tous les événements RDV |
| push  | `rdv.*`         | Reçoit tous les événements RDV |

**Exemples de clés de routage complètes :**

- `rdv.created.patient` : notification au patient d'un RDV créé
- `rdv.created.praticien` : notification au praticien d'un RDV créé
- `rdv.cancelled.patient` : notification au patient d'un RDV annulé
- `rdv.cancelled.praticien` : notification au praticien d'un RDV annulé

---

### 3. Composant producteur et évolutivité

#### Composant en charge de la détection et transmission :

Dans le microservice RDV, le composant responsable doit être situé au **niveau de la couche application** :

**Emplacement :** `ServiceRDV` (classe de service métier)

**Point d'émission des événements :**

- Après `$this->rdvRepository->save($rdv);` dans la méthode `creerRendezVous()`
- Après `$this->rdvRepository->save($rdv);` dans la méthode `annulerRendezVous()`

#### Architecture pour la portabilité (évolution du mode de transmission) :

Pour permettre de changer le protocole/serveur de messages sans impacter le producteur, il faut utiliser le **pattern Port/Adapter** :

**Solution proposée :**

1. **Créer un port (interface) dans la couche domaine/application :**

```php
interface EventPublisherInterface
{
    public function publish(string $eventType, array $data): void;
}
```

2. **Créer un adapter AMQP dans la couche infrastructure :**

```php
class AmqpEventPublisher implements EventPublisherInterface
{
    public function publish(string $eventType, array $data): void
    {
        // Code spécifique RabbitMQ/AMQP
    }
}
```

3. **Injecter le port dans ServiceRDV :**

```php
class ServiceRDV implements ServiceRDVInterface
{
    private EventPublisherInterface $eventPublisher;

    public function __construct(
        // ... autres dépendances
        EventPublisherInterface $eventPublisher
    ) {
        $this->eventPublisher = $eventPublisher;
    }

    public function creerRendezVous(InputRendezVousDTO $dto): RdvDTO
    {
        // ... logique métier
        $this->rdvRepository->save($rdv);

        // Publication de l'événement
        $this->eventPublisher->publish('rdv.created.patient', [
            'rdv_id' => $rdv->getId(),
            'patient_email' => $patient->email,
            'praticien_id' => $dto->praticienId,
            'date_heure' => $debut->format('Y-m-d H:i:s')
        ]);

        return $this->mapToDto($rdv);
    }
}
```

**Avantages de cette approche :**

- Le `ServiceRDV` ne connaît que l'interface `EventPublisherInterface`
- Pour changer de protocole (ex: Kafka, Redis Pub/Sub), il suffit de créer un nouvel adapter
- Le code métier reste inchangé
- Configuration via l'injection de dépendances (conteneur DI)

---

### Résumé

- **Exchange :** TOPIC
- **Queues :** 3 (mail, sms, push)
- **Bindings :** `rdv.*` pour chaque queue
- **Producteur :** ServiceRDV avec injection d'un `EventPublisherInterface`
- **Évolutivité :** Pattern Port/Adapter pour découpler la logique métier du protocole de messagerie
