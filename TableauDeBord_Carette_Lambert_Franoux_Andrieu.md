======================================================================
TABLEAU DE BORD FINAL – PROJET TOUBILIB (FONCTIONNALITÉS 1 → 13)
Robin Carette · Valentino Lambert · Noé Franoux · Paul Andrieu
Lien du repo : https://github.com/CaretteRobin/toubilib.git
======================================================================

1. Synthèse globale
-------------------
- Statut : toutes les fonctionnalités 1→13 du sujet détaillé sont implémentées et sécurisées (authn/authz JWT, politiques par rôle).
- Architecture : hexagonale stricte (ports/adapters), DI PHP-DI, middlewares Slim (CORS, auth, autorisation, validations), DTO/HATEOAS homogènes.
- Qualité : validations Respect\Validation, erreurs HTTP normalisées, séparation JWT (access/refresh), bases séparées (prat/rdv/patient/auth).

2. Couverture fonctionnelle (API REST)
--------------------------------------
- Praticiens : liste, détail, créneaux occupés, agenda (1,2,3,7) + recherche simple par spécialité/ville (9).
- Rendez-vous : consultation, création avec validations métier, annulation, agenda praticien, cycle de vie honoré/absent (4,5,6,7,10).
- Authentification : login JWT patient/praticien/admin + `/auth/me` (8).
- Historique patient : GET `/patients/{id}/historique` (11) avec contrôle patient propriétaire/admin.
- Inscription patient : POST `/patients` (validation, création user+patient, auto-login) (12).
- Indisponibilités praticien : GET/POST/DELETE `/praticiens/{id}/indisponibilites` avec détection chevauchement RDV/indispo (13).

3. Tests et données
-------------------
- Scénarios Bruno/HTTPie pour les routes clés (praticiens, rdv, auth, cycle de vie, indispos).
- Fixtures SQL multi-bases (praticiens, patients, rdv, auth) + schéma `indisponibilite` ajouté.
- Journaux Slim maîtrisés, validations inputs systématiques.

4. Répartition des réalisations
-------------------------------
Robin
  - Pilotage architecture (DI, middlewares CORS/Auth, harmonisation erreurs, HATEOAS) et refonte autorisations.
  - Métier RDV : création/annulation/agenda + cycle de vie honoré/absent (fonctionnalités 4,5,6,7,10).
  - Auth/JWT externalisé : `JwtManager`, provider, middlewares Optional/Authenticated/RequireRole.
  - Fonctionnalités 11→13 : inscription patient, historique patient, indisponibilités praticien, sécurisation des routes.
  - Coordination tests (Bruno/HTTPie), documentation et mise à jour du tableau de bord.

Valentino
  - Refonte `PDOPraticienRepository`, DTO praticiens, actions liste/détail/agenda/créneaux, REST/HATEOAS.
  - Relectures validations Respect\Validation, suivi des revues de code, ajustements API indisponibilités.

Noé
  - `PDORdvRepository` (chevauchements, persistences), optimisation SQL et fixtures.
  - Préparation environnements Docker/BDD, ajout schéma/table `indisponibilite`, contrôle overlaps RDV/indispos.

Paul
  - Modélisation domaine (Rdv, Praticien, motifs, moyens, structure) et DTO initiaux.
  - Scaffold Slim (routes, RequireRole), documentation architecture hexagonale et Docker Compose.
  - Relectures inscription/historique patient et cohérence DTO.

5. Points de vigilance / next steps
-----------------------------------
- Rejouer le schéma praticien pour créer la table `indisponibilite` en base.
- Tester les nouveaux endpoints (inscription, historique, cycle de vie RDV, indispos) avec tokens patient/praticien/admin.
- Vérifier CORS/vars d’env en prod et rotation des secrets JWT si déploiement.
