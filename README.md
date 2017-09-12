# psr15-api-formatter

[![Build Status](https://travis-ci.org/ender9108/psr15-api-formatter.svg?branch=master)](https://travis-ci.org/ender9108/psr15-api-formatter)
[![Coverage Status](https://coveralls.io/repos/github/ender9108/psr15-api-formatter/badge.svg?branch=master)](https://coveralls.io/github/ender9108/psr15-api-formatter?branch=master)

## Description
Permet de formatter et uniformiser les paramètres passés pour une API REST.
Un attribut avec la clé "**_api**" est créé dans l'objet "**Request**".

```
GET /users/1?fields=firstname,lastname,address(city,street)&sort=firstname,lastname,age&desc=age&range=0-10&test=bidule

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

## Paramètres

### Réponses partielles

Les réponses partielles permettent au client de récupérer uniquement les informations dont il a besoin.

```
GET /users/1?fields=firstname,lastname
OR
GET /users/1?fields=firstname,lastname,address(city,street)
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