# Contenus Social Media Signalist - Phase 1 MVP

> **Phase:** MVP - Moteur RSS core, PostgreSQL schema, Dashboard UI
> **Ton:** Direct, cash, pas de filtre. On parle comme on pense.
> **Tutoiement:** Partout, Twitter comme LinkedIn.
> **Marque:** Signalist

---

## Principes d'écriture

**On ne sonne pas comme un LLM. Jamais.**

- **Pas de MAJUSCULES pour l'emphase.** Si tu dois crier pour qu'on comprenne, c'est que la phrase est mauvaise.
- **Pas de "Et toi ?" à la fin.** C'est le tic d'un robot qui simule la conversation.
- **Pas de listes symétriques.** "3 problèmes / 3 solutions / 3 résultats" ça pue le template.
- **Des détails concrets.** "J'ai passé 2h à debug un flux Atom qui renvoyait du XML pourri" > "les flux RSS sont variés".
- **Du vrai.** Si t'as pas de testeurs, tu parles pas de testeurs. Si t'as pas de chiffres, tu mets pas de chiffres.
- **La longueur varie.** Un thread peut faire 3 tweets ou 9. On s'arrête quand y a plus rien à dire.
- **2-3 hashtags max.** Les murs de hashtags c'est un signal "contenu automatisé".
- **On assume.** Des avis tranchés, du franc-parler. Si un truc est nul, on dit que c'est nul.

---

## Planning

| Jour | Plateforme | Type |
|------|-----------|------|
| Mardi | Twitter/X | Post ou thread |
| Jeudi | LinkedIn | Réflexion / retour d'XP |
| Samedi | Twitter/X | Thread technique |

Si t'as rien d'intéressant à dire une semaine, tu postes pas. Un silence vaut mieux qu'un post creux.

---

## Semaine 1 : Pourquoi ce projet

### 1.1 Twitter/X - Mardi

```
Je construis un lecteur RSS en 2025.

Oui, RSS. Le truc que tout le monde croit mort.

Sauf que moi ça fait des mois que j'ai viré mon scroll Twitter du matin
et que j'ai remplacé par 15 min de RSS.
Résultat : je suis mieux informé et j'ai récupéré 1h par jour.

Le souci c'est que les lecteurs RSS qui existent c'est soit moche,
soit abandonné, soit 10€/mois pour lire des flux XML.

Ça me saoule, alors je fais le mien. Signalist.
PHP, Symfony, PostgreSQL, FrankenPHP.

Je vais raconter le process ici au fil des semaines.

#buildinpublic #RSS
```

---

### 1.2 LinkedIn - Jeudi

```
Y a quelques mois j'ai chronométré le temps que je passais à "faire ma veille"
sur Twitter et LinkedIn.

45 min par jour. Pour trouver quoi ? 1 ou 2 articles corrects noyés dans
du bruit, des threads recycled et du contenu LinkedIn généré par ChatGPT.

Je me suis mis à RSS. Pas par nostalgie de Google Reader.
Par pragmatisme.

20 sources sélectionnées, 15 min le matin, c'est plié.
Pas d'algo qui décide pour moi, pas de "trending", pas de dopamine loop.

Le problème : les outils RSS sont restés bloqués en 2012.
Interfaces datées, recherche inexistante, zéro intelligence.

Du coup je construis Signalist.
Un lecteur RSS qui ne ressemble pas à un logiciel du siècle dernier.

Phase 1 : le moteur, la base de données, le dashboard.
La suite : de l'IA pour trier et résumer (mais chaque chose en son temps).

#RSS #buildinpublic
```

**Note :** Adapte "45 min" à ton vrai chiffre. Si c'est 25 min, dis 25 min.

---

### 1.3 Twitter/X - Samedi (Thread technique)

```
1/ Stack de Signalist pour les curieux :

PHP 8.5 + Symfony 8.x
API Platform 4.x
PostgreSQL + pgvector
FrankenPHP
React + TypeScript + MUI

2/ FrankenPHP : Caddy + PHP dans un binaire.
Worker mode, HTTPS auto, zéro config nginx.
J'ai viré nginx + php-fpm, aucun regret.

3/ PostgreSQL pour tout. Pas de SQLite, pas de Mongo, pas de Redis.
pgvector intégré = le jour où j'ajoute la recherche sémantique,
j'ai pas à migrer vers un truc externe.

4/ Archi CQRS + hexagonale.
Feed, Article, Bookmark, Newsletter : chaque domaine isolé.
Overkill pour un MVP ? Sûrement. Mais je sais que ça va grossir
et j'ai pas envie de tout refaire dans 6 mois.

5/ API Platform gère le REST.
OpenAPI auto-généré, validation stricte, erreurs RFC 7807.
Le front consomme l'API, point final.

#PHP #Symfony #buildinpublic
```

---

## Semaine 2 : L'information est cassée

### 2.1 Twitter/X - Mardi

```
Un constat en construisant Signalist :

Quasi tout le monde autour de moi a arrêté RSS quand Google Reader a fermé en 2013.
Ils sont passés sur Twitter et Facebook pour s'informer.

10 ans plus tard ces plateformes sont devenues inutilisables pour la veille.
L'algo montre ce qui génère du clic, pas ce qui informe.

RSS a pas bougé. Tu choisis tes sources, tu lis, c'est fini.
C'est pas sexy mais ça marche.

Parfois la bonne techno c'est celle qu'on a oubliée.

#RSS
```

---

### 2.2 LinkedIn - Jeudi

```
J'ai un rapport compliqué avec la veille.

D'un côté c'est indispensable dans mon métier.
De l'autre, la frontière entre "se tenir informé" et "scroller dans le vide" est très fine.
Et je suis honnête : je tombe du mauvais côté régulièrement.

Le fond du problème c'est pas le volume d'info.
C'est qu'il y a aucun filtre intentionnel.

Tu ouvres Twitter, c'est l'algo qui choisit pour toi.
Son objectif c'est pas de t'informer, c'est de te garder sur la plateforme le plus longtemps possible.
Et il est très bon à ça.

Avec RSS le filtre c'est toi. Tu choisis tes sources une fois, tu lis, terminé.
C'est moins excitant. C'est drastiquement plus efficace.

C'est pour ça que je construis Signalist.
Pas pour réinventer la roue, juste pour rendre ce workflow moins pénible à utiliser au quotidien.

#veille #productivité
```

---

### 2.3 Twitter/X - Samedi (Thread technique)

```
1/ Truc que j'ai appris cette semaine sur Signalist :

Les flux RSS c'est un bordel monstrueux.

2/ En théorie : un standard XML simple et propre.
En pratique : chaque site fait n'importe quoi.

RSS 2.0, Atom, RSS 1.0 (oui, ça existe encore).
Des dates dans 15 formats différents.
Du HTML brut dans des champs texte.
Du CDATA partout.

3/ J'ai passé une soirée entière sur un flux qui renvoyait
des entités HTML non-échappées dans les titres.
Le parser crashait en silence. Magnifique.

4/ Solution : un pipeline de normalisation.
Le flux entre en brut, passe par plusieurs étapes de nettoyage,
sort dans un format interne propre.

C'est 80% du vrai boulot d'un agrégateur RSS.
C'est aussi 0% glamour.

#buildinpublic #PHP
```

---

## Semaine 3 : On montre les tripes

### 3.1 Twitter/X - Mardi

```
Signalist, semaine 3.

Fait :
- Dashboard qui tourne (basique, mais ça marche)
- Parsing RSS qui gère les flux pourris
- Catégories de flux

En cours :
- Import OPML
- Recherche basique

Pas encore fait :
- Dark mode
- Export

Screenshot en reply. C'est encore moche par endroits.

#buildinpublic
```

**Note :** Poste un vrai screenshot. "C'est encore moche" désarme la critique et sonne humain. Pas de faux témoignages de testeurs.

---

### 3.2 LinkedIn - Jeudi

```
Un truc que je sous-estimais en me lançant dans le building in public :
c'est pas le code qui est dur, c'est d'en parler.

Coder, je sais faire. C'est mon métier.
Écrire chaque semaine sur ce que j'ai fait, honnêtement, sans survendre ?
Beaucoup plus compliqué.

La tentation permanente c'est de raconter une version embellie.
"Dashboard magnifique", "architecture élégante", "retours incroyables".

La réalité c'est plutôt : "ça marche mais c'est pas beau",
"j'ai sur-architecturé ce truc et je sais pas si c'était nécessaire",
"personne a encore testé à part moi".

Mais je m'y tiens parce que ça me force à livrer chaque semaine.
Si j'ai rien à montrer le mardi, j'ai honte.
La honte comme moteur de productivité, ça a le mérite de fonctionner.

#buildinpublic #sideproject
```

---

### 3.3 Twitter/X - Samedi (Thread technique)

```
1/ Pourquoi PostgreSQL pour tout dans Signalist
alors que SQLite aurait suffi pour un MVP :

2/ En un mot : pgvector.

La phase 1 c'est du RSS classique.
La phase 2 c'est de la recherche sémantique avec des embeddings.

Si je pars sur SQLite maintenant, je migre plus tard. Flemme.
PostgreSQL me donne tout au même endroit dès le départ.

3/ Bonus :
- JSONB pour les métas flexibles
- Full-text search natif (largement suffisant pour la phase 1)
- C'est ce que je maîtrise le mieux

4/ Le contre-argument : c'est plus lourd qu'un fichier SQLite.
Vrai. Mais avec Docker c'est 3 lignes de config.

Pari sur l'avenir. Si la phase 2 arrive jamais, j'aurais over-engineered.
Si elle arrive, j'aurais évité une migration pénible.
Je prends le risque.

#buildinpublic #PostgreSQL
```

---

## Semaine 4 : On cause avec les gens

### 4.1 Twitter/X - Mardi

```
Signalist avance. Dashboard fonctionnel, OPML en cours.

Question pour ceux qui utilisent RSS :
qu'est-ce qui vous manque le plus dans vos outils actuels ?

- Résumés auto des articles ?
- Regroupement par thème ?
- Détection de doublons ?
- Autre ?

Je préfère demander maintenant que coder un truc dont tout le monde se fout.

#buildinpublic
```

---

### 4.2 LinkedIn - Jeudi

```
1 mois de building in public sur Signalist.

Ce que j'en retiens : c'est pas du marketing.
C'est une méthode de travail.

Chaque semaine je suis obligé de me demander :
qu'est-ce que j'ai vraiment fait ?
Est-ce que ça va dans le bon sens ?
Qu'est-ce que je fais ensuite ?

Sans cette contrainte, je me connais.
Je partirais dans tous les sens, j'ajouterais des features inutiles,
je refactoriserais du code qui marche très bien.

Là je suis forcé de prioriser. Parce que si mardi j'ai rien à montrer, ça se voit.

C'est inconfortable. C'est efficace.

#buildinpublic #sideproject
```

---

### 4.3 Twitter/X - Samedi (Thread technique)

```
1/ Comment j'organise mes flux RSS.
La vraie version, pas la version Instagram.

2/ J'ai 3 catégories :

"Quotidien" : 5-6 flux. Symfony blog, 2-3 newsletters tech, un flux sécu.
Je les lis chaque matin.

"Hebdo" : une dizaine. Blogs longs, contenus de fond.
En théorie je lis le weekend. En pratique, une fois sur deux.

"Vrac" : tout le reste.
Honnêtement la plupart du temps je marque tout comme lu sans lire.

3/ Règle que j'essaie de tenir : si j'ajoute un flux, j'en vire un.
Sinon le compteur non-lu explose et ça génère de l'anxiété.
Ce qui est exactement le contraire de l'objectif.

4/ Y a des semaines où j'ouvre même pas le lecteur.
Et c'est ok. C'est ça l'avantage de RSS sur les réseaux :
personne te juge, y a pas d'algo qui te punit.

#RSS #productivité
```

---

## Semaines 5-8 : Contenus complémentaires

### Thread A : API Platform, retour concret

```
1/ Un truc que j'apprécie avec API Platform sur Signalist :
je déclare mes ressources, j'ai l'API REST + la doc OpenAPI. C'est plié.

2/ Pour ajouter un endpoint "créer un flux" concrètement :
- Un InputDTO avec les contraintes
- Un StateProcessor qui crée la commande
- Un Handler qui fait le boulot
- Pas de controller. Rien d'autre.

3/ Les erreurs sortent en RFC 7807.
Un standard HTTP pour les réponses d'erreur structurées.
Infiniment plus propre que {"error": "oops"}.

4/ Le temps que je gagne sur le boilerplate,
je le réinvestis direct dans le parsing RSS.
Parce que c'est là que c'est vraiment galère.

#PHP #Symfony
```

---

### Thread B : L'honnêteté du side project

```
1/ J'ai commencé Signalist parce que l'interface de mon lecteur RSS m'énervait.

C'est tout. Y a pas de grande vision derrière.
C'est "ce bouton me saoule, je vais faire mieux".

2/ Je pense que 80% des side projects naissent comme ça.
Pas d'un pitch deck, d'une frustration.

La vraie question c'est : est-ce que cette fois je finis ?
J'espère. On verra.

3/ Le scope creep c'est mon ennemi.
Chaque semaine j'ai une idée "et si j'ajoutais X".
La semaine dernière c'était les tags IA.

Non. Pas maintenant. Phase 2.
C'est dur mais c'est nécessaire sinon je finis rien.

#buildinpublic
```

---

### Thread C : Performance (template)

```
1/ J'ai passé [X]h cette semaine à optimiser le chargement du dashboard Signalist.

Avant : [X]ms. Après : [X]ms.

2/ [Ce que tu as fait concrètement, étape 1]

3/ [Étape 2]

4/ Le gain le plus gros c'est souvent le truc le plus bête.
Chez moi c'était [truc spécifique].

#buildinpublic
```

**Note :** À remplir avec tes vrais chiffres. Publie pas de données inventées.

---

### LinkedIn D : Knowledge management

```
Un problème que Signalist résout pas encore :
je lis un article le matin, le soir j'ai oublié ce qu'il racontait.

RSS c'est bien pour collecter. Pour retenir, c'est une autre histoire.

Mon système actuel c'est un fichier texte où je copie les 2-3 points clés
quand un article m'intéresse vraiment.
C'est artisanal et un peu chiant, mais ça marche.

La phase 2 de Signalist inclut des résumés auto via IA.
Pas pour remplacer la lecture — pour garder une trace.

Est-ce que l'IA fera ça correctement ? J'en sais rien.
J'ai des doutes. Je vais tester et on verra.

#veille #productivité
```

---

### LinkedIn E : RSS vs newsletters

```
"Pourquoi RSS et pas juste des newsletters ?"

Question légitime. Réponse courte : les deux.

Les newsletters c'est bien. Mais j'en reçois une quarantaine.
Elles atterrissent dans ma boîte mail, mélangées avec les factures,
les relances et les spams.
Je les lis rarement au bon moment.

RSS c'est un espace dédié. J'ouvre le lecteur, je suis en mode veille.
Pas de notifs, pas de mails urgents qui clignotent à côté.

Ce que je fais : les bonnes newsletters, je les convertis en flux RSS
(kill-the-newsletter.com) et je lis tout dans Signalist.

Un endroit, un moment, une interface. C'est aussi simple que ça.

#RSS #productivité
```

---

## Sujets par Cycle

### Cycle 1 (Semaines 1-4) : Pourquoi et comment
- [ ] Pourquoi construire un lecteur RSS en 2025
- [ ] Le problème du filtre informationnel
- [ ] Stack technique
- [ ] La galère du parsing RSS
- [ ] Premier screenshot (même moche)
- [ ] PostgreSQL vs SQLite
- [ ] 1 mois de building in public : bilan honnête
- [ ] Mon organisation de flux (version honnête)

### Cycle 2 (Semaines 5-8) : Le concret
- [ ] API Platform, retour terrain
- [ ] Side project : frustrations et scope creep
- [ ] Performance avec vrais chiffres
- [ ] RSS vs newsletters
- [ ] Le problème de la rétention d'info
- [ ] Import OPML, pourquoi c'est important
- [ ] Dark mode (quand c'est livré, pas avant)
- [ ] Premiers retours (quand y en a, pas avant)

### Cycle 3 (Semaines 9-12) : Vers le lancement
- [ ] Comparaison honnête avec Feedly / Inoreader (avec les trucs où ils sont meilleurs)
- [ ] Phase 2 IA : pourquoi, comment, avec quoi
- [ ] RGPD : comment je gère les données
- [ ] Ce que ça coûte de faire tourner le truc (infra réelle)
- [ ] Pricing : mes réflexions à voix haute
- [ ] Beta ouverte (quand c'est prêt)

---

## Hashtags

### Twitter/X (2-3 max)
- `#buildinpublic`
- `#RSS`
- `#PHP` / `#Symfony` pour les posts techniques
- `#sideproject`

### LinkedIn (1-2 max)
- `#veille`
- `#productivité`
- `#buildinpublic`
- `#RSS`

### Interdit
- `#AI` / `#IA` (trop hype, trop vague)
- `#growthhacking` (non)
- `#disruptive` / `#innovative` (buzzwords creux)
- 8+ hashtags sur un post (signal de robot)

---

## Réponses aux commentaires

- 1-2 phrases, naturel
- Pas de question systématique en retour
- Si c'est une vraie question technique : prends le temps de répondre correctement
- Si c'est un "cool" : "merci" suffit
- Jamais de "Great question!" ou "Excellente remarque!"

---

## KPIs réalistes

| Métrique | Mois 1 | Mois 3 |
|----------|--------|--------|
| Twitter followers | +30-50 | +100-200 |
| Engagement / post | 3-10 likes | 10-30 likes |
| LinkedIn impressions | 200-500 | 1000-3000 |
| Early access signups | 5-10 | 30-50 |

C'est des estimations basses pour un compte sans audience existante. Si ça décolle plus vite tant mieux, mais faut pas se raconter des histoires.

---

## Checklist avant de poster

- [ ] Est-ce que je mens quelque part ? Même un peu ?
- [ ] Les chiffres sont réels ?
- [ ] Lu à voix haute : est-ce que je parlerais comme ça à un pote ?
- [ ] Y a des MAJUSCULES d'emphase ? → Les virer.
- [ ] Ça finit par "Et toi ?" → Trouver mieux ou rien mettre.
- [ ] Je peux ajouter un détail concret / une anecdote vraie ?
- [ ] Est-ce que ça sonne comme un post LinkedIn lambda ? Si oui, tout réécrire.

---

*Dernière mise à jour : Février 2025*
