# psr15-api-formatter

[![Build Status](https://travis-ci.org/ender9108/psr15-api-formatter.svg?branch=master)](https://travis-ci.org/ender9108/psr15-api-formatter)
[![Coverage Status](https://coveralls.io/repos/github/ender9108/psr15-api-formatter/badge.svg?branch=master)](https://coveralls.io/github/ender9108/psr15-api-formatter?branch=master)

Norme de l'api est basé sur l'article [d'octo talks](https://blog.octo.com/designer-une-api-rest/)


## Description
Permet de formatter et uniformiser les paramètres passés pour une API REST.
Un attribut avec la clé "**_api**" est créé dans l'objet "**Request**".

```
GET /api/v1/users/1?fields=firstname,lastname,address(city,street)&sort=firstname,lastname,age&desc=age&range=0-10&test=bidule

[_api] => Array(
    [fields] => Array(
        [0] => firstname
        [1] => lastname
        [4] => Array(
            [address] => Array(
                [0] => city
                [1] => street
            )
        )
    )
    [sort] => Array(
        [asc] => Array(
            [0] => firstname
            [1] => lastname
        )
        [desc] => Array(
            [0] => age
        )
    )
    [range] => Array(
        [0] => 0
        [1] => 10
    )
    [filters] => Array(
        [test] => bidule
    )
)
```

## Paramètres

### Réponses partielles

Les réponses partielles permettent au client de récupérer uniquement les informations dont il a besoin.

```
GET /api/v1/users/1?fields=firstname,lastname
OR
GET /api/v1/users/1?fields=firstname,lastname,address(city,street)
```

ApiFormatterMiddleware retournera un tableau :
```
[_api] => Array(
    [fields] => Array(
        [0] => firstname
        [1] => lastname
        [4] => Array(
            [address] => Array(
                [0] => city
                [1] => street
            )
        )
    )
)
```

## Pagination

Le paramètre "**range**" permet de paginer les résultats.

Format : range={offset}-{limit}

Si "**range**" ne contient pas les deux paramètres {offset} et {limit}, une exception de type 
"**InvalidArgumentException**" est lancée.

```
GET /api/v1/users?range=0-10
```

ApiFormatterMiddleware retournera un tableau :
```
[_api] => Array(
    [range] => Array(
        [0] => 0    //offset
        [1] => 10   //limit
    )
)
```

## Tris

Pour les tris, deux paramètres sont pris en compte.
- sort : Contient la liste des champs séparés par des virgules
- desc : le tri est croissant par défaut. Pour filtrer de manière descendante, il faudra spécifier
ce paramètre (vide pour avoir un tri descendant sur tous les champs contenus dans "**sort**" ou 
vous pouvez définir certain champ contenu dans "**sort**")

```
// tri ascendant sur les champs firstname,lastname
GET /api/v1/users?sort=firstname,lastname

// tri descendant sur les champs firstname,lastname
GET /api/v1/users?sort=firstname,lastname&desc

// tri ascendant sur les champs firstname,lastname et descendant sur le champ age
GET /api/v1/users?sort=firstname,lastname,age&desc=age
```

## Filtres

```
// Ex : Récupération de tous les users dont le prénom est "john"
GET /api/v1/users?firstname=john

// Ex : Récupération de tous les users dont le prénom est "john" et "david"
GET /api/v1/users?firstname=john,david
```