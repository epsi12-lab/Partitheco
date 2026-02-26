### Plan de Refonte Liturgique - Projet Partitheco (Détails Techniques)

Ce document complète le plan de refonte en précisant les structures de données pour les moments de la messe et les types de chants.

#### 1. Structure des Moments de la Messe
Les chants seront classés selon les moments liturgiques suivants :
- **Entrée**
- **Aspersion**
- **Kyrie** (Ordinaire de la messe)
- **Gloria** (Ordinaire de la messe)
- **Psaume**
- **Aclammation** (Alléluia)
- **Credo** (Ordinaire de la messe)
- **Prière Universelle**
- **Offrande / Offertoire**
- **Sanctus** (Ordinaire de la messe)
- **Agnus Dei** (Ordinaire de la messe)
- **Communion**
- **Action de grâce**
- **Antienne**
    - Antienne d'ouverture
    - Antienne de communion
- **Envoi**

#### 2. Classification Spécifique
- **Ordinaire de la messe** : Regroupe Kyrie, Gloria, Credo, Sanctus, Agnus Dei.
- **Antiennes** : Regroupe Antienne d'ouverture et Antienne de communion.
- **Chants Religieux Non-Liturgiques** : Champ dédié pour les chants de dévotion, veillées, ou concerts qui ne s'inscrivent pas dans la liturgie eucharistique.

#### 3. Évolutions de la Base de Données (Table `projects`)
- Ajout de `liturgical_moment` (VARCHAR) : Stocke le moment précis (ex: 'psaume', 'kyrie').
- Ajout de `is_liturgical` (BOOLEAN) : Pour distinguer les chants liturgiques des chants religieux simples.
- Ajout de `liturgical_period` (VARCHAR) : Temps liturgique (Avent, Carême, etc.).
- Ajout de `voice_formation` (VARCHAR) : Chœur SATB, Unisson, etc.

*Note : La Cote SECLI est retirée du projet comme demandé.*
