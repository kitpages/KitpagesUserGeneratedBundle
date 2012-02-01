KitpagesUserGeneratedBundle
==================

Used for common user generated contents like comments, rating,...

For the moment there is only comments.

Current status
-------------
Alpha state

comming soon :
- a moderation system
- an administration
- events
- emails to the webmaster

Installation
------------

installation in vendors (throught deps for example) and add
it in app/autoload.php, app/appKernel.php, and routing.yml

Warning, it uses the KitpagesUtilBundle (which is standalone)

app/console doctrine:schema:update  to update your db schema

There is no configuration in config.yml

User's guide
------------

If you want to add a comment system in a given page, you should add this code
wherever you want in your twig template :

    <h3>Form to add a comment</h3>
    {% render 'KitpagesUserGeneratedBundle:Comment:newPost' with {
        'itemReference': 'myItem'
    } %}
    <h3>commentList</h3>
    {% render 'KitpagesUserGeneratedBundle:Comment:displayPostList' with {
        'itemReference': 'myItem'
    } %}

the "itemReference" parameter is a reference to a comment list. For example if you
want comments on the product #45 of your shop, you can use "product-45" as itemReference