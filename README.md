# middle-earth-api-formatter

[![Build Status](https://travis-ci.org/ender9108/middle-earth-api-formatter.svg?branch=master)](https://travis-ci.org/ender9108/middle-earth-api-formatter)
[![Coverage Status](https://coveralls.io/repos/github/ender9108/middle-earth-api-formatter/badge.svg?branch=master)](https://coveralls.io/github/ender9108/middle-earth-api-formatter?branch=master)
[![Latest Stable Version](https://poser.pugx.org/enderlab/middle-earth-api-formatter/v/stable)](https://packagist.org/packages/enderlab/middle-earth-api-formatter)
[![Total Downloads](https://poser.pugx.org/enderlab/middle-earth-api-formatter/downloads)](https://packagist.org/packages/enderlab/middle-earth-api-formatter)
[![License](https://poser.pugx.org/enderlab/middle-earth-api-formatter/license)](https://packagist.org/packages/enderlab/middle-earth-api-formatter)

API Standard based on article [d'octo talks](https://blog.octo.com/designer-une-api-rest/)

[**Tradction en cours**]


## Installation

```
composer require enderlab/middle-earth-api-formatter
```


## ApiRequestFormatter

### Description
Format and standardize parameters for a REST API. An attribute "**_ api**" is created in the "**Request**" object.

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

### Parameters

#### Partial response

Partial response allow clients to retrieve only the information they need

```
GET /api/v1/users/1?fields=firstname,lastname
OR
GET /api/v1/users/1?fields=firstname,lastname,address(city,street)
```

ApiFormatterMiddleware return an array :
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

### Pagination

The parameter "**range**" lets paginate the results.

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

### Tris

Pour les tris, deux paramètres sont pris en compte.
- sort : Contient la liste des champs séparés par des virgules
- desc : le tri est croissant par défaut. Pour filtrer de manière descendante, il faudra spécifier
ce paramètre (vide pour avoir un tri descendant sur tous les champs contenus dans "**sort**" ou 
vous pouvez définir certain champ contenu dans "**sort**")

```
// tri ascendant sur les champs firstname,lastname
GET /api/v1/users?sort=firstname,lastname

[_api] => Array(
    [sort] => Array(
        [asc] => Array(
            [0] => firstname
            [1] => lastname
        )
    )
)

// tri descendant sur les champs firstname,lastname
GET /api/v1/users?sort=firstname,lastname&desc

[_api] => Array(
    [sort] => Array(
        [desc] => Array(
            [0] => firstname
            [1] => lastname
        )
    )
)

// tri ascendant sur les champs firstname,lastname et descendant sur le champ age
GET /api/v1/users?sort=firstname,lastname,age&desc=age

[_api] => Array(
    [sort] => Array(
        [asc] => Array(
            [0] => firstname
            [1] => lastname
        )
        [desc] => Array(
            [0] => age
        )
    )
)
```

### Filtres

```
// Ex : Récupération de tous les users dont le prénom est "john"
GET /api/v1/users?=john

[_api] => Array(
    [filters] => Array(
        [firstname] => john
    )
)

// Ex : Récupération de tous les users dont le prénom est "john" et "david"
GET /api/v1/users?firstname=john,david

[_api] => Array(
    [filters] => Array(
        [firstname] => [john,david]
    )
)
```


## ApiResponseFormatter