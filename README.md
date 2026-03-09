# Alertes Licences - Plugin GLPI

Plugin GLPI pour le suivi et les alertes d'expiration des licences logicielles.

## Fonctionnalités

- **Tableau de bord** : carte affichant les licences avec dates d'expiration, colorées selon leur statut :
  - Rouge : licence expirée
  - Orange : licence expirant bientôt (seuil configurable)
  - Vert : licence valide
- **Notifications email** : alertes automatiques pour les licences expirées ou bientôt expirées
- **Configuration** : page de paramétrage accessible depuis Configuration > Alertes licences
  - Seuil d'alerte (jours avant expiration)
  - Activation/désactivation des notifications
  - Fréquence d'envoi des rappels
  - Email supplémentaire destinataire
  - Couleurs personnalisables (fond et texte)

## Prérequis

- GLPI >= 11.0

## Installation

1. Copier le dossier `licenseexpiry` dans `/marketplace/` ou `/plugins/` de votre installation GLPI
2. Aller dans **Configuration > Plugins**
3. Installer puis activer le plugin **Alertes licences**
4. Ajouter la carte "Expiration des licences" sur votre tableau de bord central

## Auteur

Jean-Christophe KUBIACZYK

## Licence

GPLv2+
