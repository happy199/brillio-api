# Limitations de Personnalisation : Emails et Notifications

Ce document détaille pourquoi les **emails** et les **notifications système** générés par Brillio ne sont pas couverts par la passe de personnalisation "Marque Blanche" actuelle, et comment aborder cette problématique à l'avenir.

## Le problème architectural

L'application Brillio utilise Laravel pour générer et envoyer des communications via des classes `Mailable` (Emails) et `Notification` (Notifications in-app/Emails).
La grande majorité de ces communications sont traitées **de manière asynchrone** (en arrière-plan) via le système de Queues (Redis, Database).

### 1. Perte du contexte HTTP
Lorsque la plateforme est utilisée via l'URL d'une organisation (ex: `https://organisation.brillio.africa`), le middleware `ResolveOrganizationByDomain` détecte le domaine et injecte la variable globale `$current_organization` dans l'application pour que toutes les vues (HTML) puissent l'utiliser.
Cependant, lorsqu'un email est mis en file d'attente pour être envoyé plus tard, ce processus d'envoi s'exécute dans un "Job" en tâche de fond (CLI). **Le contexte de la requête web, le domaine, et donc `$current_organization` n'existent plus.**

### 2. Le défi de la résolution dynamique
Pour que chaque email reflète correctement le nom de l'organisation :
1. Il faudrait que chaque classe `Mailable` ou `Notification` détermine l'organisation à partir de l'utilisateur qui **reçoit** l'email. Par exemple : `$notifiable->organizations->first()` ou `$notifiable->sponsoringOrganization`.
2. Il faudrait ensuite injecter explicitement cette donnée dans chaque vue d'email (plus de 80 templates existants).
3. Il faudrait mettre à jour le template de base des emails (`resources/views/emails/layouts/base.blade.php`) pour ne pas hardcoder "Brillio" mais plutôt afficher la valeur calculée et injectée depuis les classes PHP.

### 3. Risques de régression
- **Sérialisation des Jobs** : Injecter dynamiquement des modèles Eloquent (comme `Organization`) dans les Jobs peut engendrer des bugs de sérialisation difficiles à débugger.
- **Complexité des cas** : Tous les utilisateurs ne sont pas liés à une seule organisation. De plus, certains emails (ex: Notification Admin d'un achat) sont envoyés au Staff Brillio, et ne doivent pas forcément prendre la marque de l'organisation. Il faut un audit précis pour chaque type d'email.

## Recommandations pour une future itération

Si la personnalisation totale des emails s'avère indispensable, voici la feuille de route recommandée :

1. **Création d'un Service ou Trait de Résolution d'Organisation** : Un composant qui prend un User en entrée et retourne l'Organisation principale à utiliser pour la communication.
2. **Refonte de `emails.layouts.base`** : Permettre au template d'accepter une variable `$displayOrg` (optionnelle). Si elle est fournie, afficher le nom et le logo de l'organisation, sinon fallback sur Brillio.
3. **Mise à jour progressive des classes Mailables** : Commencer par les emails les plus critiques (Welcome Email, Confirmations de Séances, Rapports) en injectant ce nouveau contexte, puis tester rigoureusement l'envoi en file d'attente (`QUEUE_CONNECTION=redis`).
4. **Mise à jour de `config/seo.php` ou Mail config dynamique** : Modifier l'expéditeur de l'email ("Brillio" vs "L'équipe [Organisation]") dynamiquement au moment de l'envoi.

> **En conclusion** : Toucher aux envois asynchrones a un impact trop large (risque de casser les workflows de validation, relances et facturation) pour être intégré dans une passe purement "front-end" de personnalisation. Cela demande une tâche backend dédiée.
