# Frontend de Gestion de Stock avec Symfony

Frontend PHP réalisé avec Symfony pour l'API de gestion de stock Java/Spring Boot.

## Description

Ce projet est un frontend simple développé avec PHP et Symfony pour interfacer avec l'API REST de gestion de stock. Il permet de visualiser, créer, modifier et supprimer des produits et des catégories, ainsi que de gérer les niveaux de stock.

## Technologies

- PHP 8.1+
- Symfony 6.3
- Twig (templates)
- Bootstrap 5 (UI)
- Axios (appels API)
- Webpack Encore

## Fonctionnalités

- Affichage de la liste des produits et des catégories
- Ajout, modification et suppression de produits
- Ajout, modification et suppression de catégories
- Gestion des stocks (ajout/retrait de quantités)
- Visualisation des produits à faible stock
- Recherche de produits
- Interface responsive

## Installation

1. Cloner le dépôt
```bash
git clone https://github.com/creach-t/stock-management-frontend.git
cd stock-management-frontend
```

2. Installer les dépendances PHP
```bash
composer install
```

3. Installer les dépendances JavaScript
```bash
npm install
npm run build
```

4. Configurer l'URL de l'API dans le fichier `.env` ou `.env.local`
```
API_URL=http://localhost:8080/api
```

5. Lancer le serveur Symfony
```bash
symfony server:start -d
```

## Structure du projet

```
src/
├── Controller/
│   ├── CategoryController.php
│   ├── DashboardController.php
│   └── ProductController.php
├── Form/
│   ├── CategoryType.php
│   ├── ProductType.php
│   └── StockUpdateType.php
├── Model/
│   ├── Category.php
│   ├── Product.php
│   └── StockUpdate.php
├── Service/
│   └── ApiService.php
templates/
├── base.html.twig
├── category/
│   ├── index.html.twig
│   ├── new.html.twig
│   └── edit.html.twig
├── dashboard/
│   └── index.html.twig
└── product/
    ├── index.html.twig
    ├── new.html.twig
    ├── edit.html.twig
    └── stock.html.twig
```

## Développement

Pour lancer le serveur de développement avec hot-reloading:

```bash
npm run watch
symfony server:start -d
```

## Intégration avec l'API

Ce frontend est conçu pour fonctionner avec l'API REST de gestion de stock disponible à l'adresse suivante: https://github.com/creach-t/stock-management-api

Assurez-vous que l'API est en cours d'exécution avant d'utiliser ce frontend.

## Captures d'écran

### Tableau de bord
![Tableau de bord](docs/dashboard.png)

### Liste des produits
![Liste des produits](docs/products.png)

### Gestion du stock
![Gestion du stock](docs/stock.png)

## Contribuer

Les contributions sont les bienvenues ! N'hésitez pas à soumettre des pull requests ou à signaler des problèmes.

## Licence

Ce projet est sous licence MIT.
