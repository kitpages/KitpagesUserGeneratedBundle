KitpagesUserGeneratedBundle
===========================

Used for common user generated contents like comments, rating,...

For the moment there is only comments with the following features :

* moderation
* email to webmaster

URL : http://www.kitpages.fr/fr/cms/108/kitpagesusergeneratedbundle (in french)

Current status
-------------
Beta state

comming soon :

* an administration

Installation
------------

If you are using `DEPS` :
    
    [gedmo-doctrine-extensions]
        git=http://github.com/l3pp4rd/DoctrineExtensions.git
        target=/gedmo-doctrine-extensions
    
    [StofDoctrineExtensionsBundle]
        git=https://github.com/stof/StofDoctrineExtensionsBundle.git
        target=/bundles/Stof/DoctrineExtensionsBundle
    
    [KitpagesUtilBundle]
        git=https://github.com/kitpages/KitpagesUtilBundle.git
        target=/bundles/Kitpages/UtilBundle
        
    [KitpagesUserGeneratedBundle]
        git=https://github.com/kitpages/KitpagesUserGeneratedBundle.git
        target=/bundles/Kitpages/UserGeneratedBundle

Add `Kitpages` namespace to your autoloader :

``` php
<?php // app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Gedmo'     => __DIR__.'/../vendor/gedmo-doctrine-extensions/lib',
    'Stof'      => __DIR__.'/../vendor/bundles',
    'Kitpages'  => __DIR__.'/../vendor/bundles',
));
```

Enable the bundles in your kernel :

``` php 
<?php // app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
        new Kitpages\UtilBundle\KitpagesUtilBundle(),
        new Kitpages\UtilBundle\KitpagesUserGeneratedBundle(),
    );
}
```

Import the routing file :

``` yaml
# app/config/routing.yml
KitpagesUserGeneratedBundle:
    resource: "@KitpagesUserGeneratedBundle/Resources/config/routing.xml"
```

Configure KitpagesUserGenerated :

``` yaml
# app/config/config.yml
kitpages_user_generated:
    comment:
        default_status: "validated"
        from_email: "webmaster@mywebsite.fr"
        admin_email_list: ["admin@mywebsite.fr"]
```

Configure StofDoctrineExtensionsBundle :

``` yaml
# app/config/config.yml
stof_doctrine_extensions:
    default_locale: en_US
    orm:
        default:
            timestampable: true
            sortable: true
            sluggable: true
            tree: true
``` 

And then update your database schema :

``` bash
php app/console doctrine:schema:update
```

User's guide
------------

If you want to add a comment system in a given page, you should add this code
wherever you want in your twig template :

``` html
<h3>Form to add a comment</h3>
{% render 'KitpagesUserGeneratedBundle:Comment:newPost' with {
    'itemReference': 'myItem'
} %}
<h3>commentList</h3>
{% render 'KitpagesUserGeneratedBundle:Comment:displayPostList' with {
    'itemReference': 'myItem'
} %}
```

The "itemReference" parameter is a reference to a comment list. For example if you
want comments on the product #45 of your shop, you can use "product-45" as itemReference