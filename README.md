UserBundle
==========

Directly inspired from [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle), and migration of [c975L/UserFilesBundle](https://github.com/975L/UserFilesBundle/tree/master), UserBundle does the following:

- Ensure **respect of GDPR rules** such as giving agreement to store data, exporting data,
- Stores users in database **(using doctrine)**,
- Provides multiples types of entities for User (see below),
- Allows extending those entities to add your own fields,
- Allows users to manage their profile and data,
- Validates data such as Siret, VAT number,
- Displays a "challenge" for sign up (no Captcha, etc.),
- Allows the possibility to disable sign up (for registering only one or more users and then no more),
- Provides forms for Sign in, Sign up, Modify profile, Change password and Reset password,
- Provides public profile (you can disabled it in config),
- Allows extending those forms,
- Sends email about sign up and password reset to the user via [c975LEmailBundle](https://github.com/975L/EmailBundle) as `c975LEmailBundle` provides the possibility to save emails in a database, there is an option to NOT do so via this Bundle,
- Integrates with [c975LToolbarBundle](https://github.com/975L/ToolbarBundle),
- Allows to connect with social networks via [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle),
- Provides a sql script to migrate from FOSUserBundle,
- Allows to display Gravatar's image linked to the email address,
- Allows to display Social network's image linked to the account,
- Provides a divData to allows access user's data from javascript,
- Allows easy overridding of templates or parts of them to minimize the number of the overriden files to the essential,
- Allows to define a number of attempts for sign in and then add a delay before being able to sign in again,
- Resetting password form will NOT send email for inexisting accounts while displaying so, this un-allows checking for registered emails,
- Allows user to removes its allowing to use its data while maintaining its account, in this case the account will be marked as NOT enabled,
- Sends email when user changes (or resets) passsword to allow contact website if he/she has not initiated this action,
- integrates an **API** to authenticate/create/display/modify/delete users in json format,

[UserBundle dedicated web page](https://975l.com/en/pages/user-bundle).

[UserBundle API documentation](https://975l.com/apidoc/c975L/UserBundle.html).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Use [Composer](https://getcomposer.org) to install the library
```bash
    composer require c975l/user-bundle
```

Step 2: Enable the Bundle
-------------------------
Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new c975L\UserBundle\c975LUserBundle(),
        ];
    }
}
```

Step 3: Configure the Bundle
----------------------------
Check dependencies for their configuration:
- [Swiftmailer](https://github.com/symfony/swiftmailer-bundle)
- [Doctrine](https://github.com/doctrine/DoctrineBundle)
- [c975LEmailBundle](https://github.com/975L/EmailBundle)
- [Misd\PhoneNumberBundle](https://github.com/misd-service-development/phone-number-bundle)

If you use Address or Business fields, you have to add the following in your `app/config.yml` to enable phone and fax verification:
```yml
doctrine:
    dbal:
        types:
            phone_number: Misd\PhoneNumberBundle\Doctrine\DBAL\Types\PhoneNumberType
```

And finally in `app/security.yml`

```yml
security:
    encoders:
        #The entity you want to use
        c975L\UserBundle\Entity\User: bcrypt
    role_hierarchy:
        ROLE_MODERATOR: 'ROLE_USER'
        ROLE_ADMIN: [ROLE_MODERATOR, ROLE_USER]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_MODERATOR, ROLE_USER]
    providers:
        c975_l_userbundle:
            entity:
                #The entity you want to use
                class: c975L\UserBundle\Entity\User
    firewalls:
        main:
            pattern: ^/
            provider: c975_l_userbundle
            form_login:
                login_path: user_signin
                check_path: user_signin
                default_target_path: user_dashboard
                csrf_token_generator: security.csrf.token_manager
            remember_me:
                secret: '%secret%'
                lifetime: 31536000
                path: /
                secure: true
            anonymous: true
            logout:
                path: user_signout
                handlers: [c975L\UserBundle\Listener\LogoutListener]
```

Step 4: Create MySql table
--------------------------
Use `/Resources/sql/user.sql` to create the tables `user` and `user_archives`. The `DROP TABLE` are commented to avoid dropping by mistake. It will also create a stored procedure `sp_UserArchive()`.

Step 5: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_user:
    resource: "@c975LUserBundle/Controller/"
    type: annotation
    prefix: /
    #Multilingual website use the following
    #prefix: /{_locale}
    #defaults:   { _locale: '%locale%' }
    #requirements:
    #    _locale: en|fr|es
```

Step 6: install assets to web folder
------------------------------------
Install assets by running
```bash
php bin/console assets:install --symlink
```
It will create a link from folder `Resources/public/` in your web folder.

Overriding Templates
--------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LUserBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle files, then apply your needed changes.

You can also override:
- `app/Resources/c975LUserBundle/views/fragments/deleteAccountInfo.html.twig` that will list the implications, by deleting account, for user, displayed in the delete account page.
- `app/Resources/c975LUserBundle/views/fragments/dashboardActions.html.twig` to add your own actions (or whatever) in the dashboard i.e.
- `app/Resources/c975LUserBundle/views/fragments/avatar.html.twig` to modify the display of avatar (26/03/2018)

You can add a navbar menu via `{% include('@c975LUser/fragments/navbarMenu.html.twig') %}`. You can override it, if needed, or simply override `app/Resources/c975LUserBundle/views/fragments/navbarMenuActions.html.twig` to add actions above it.

Routes
------
The Routes availables are:
- user_signup
- user_signup_confirm
- user_signin
- user_config
- user_dashboard
- user_display
- user_export
- user_modify
- user_change_password
- user_reset_password
- user_reset_password_confirm
- user_signout
- user_delete
- user_public_profile

Entities
--------
You must choose an entity linked to your needs and specify it in the `app/security.yml` and `app/config.yml`. Available entities are the following:

- `c975L/UserBundle/Entity/UserLight`: light user with minimum requirements
- `c975L/UserBundle/Entity/User`: default user
- `c975L/UserBundle/Entity/UserAddress`: default user + address fields
- `c975L/UserBundle/Entity/UserBusiness`: default user + business/association fields
- `c975L/UserBundle/Entity/UserSocial`: default user + social network fields
- `c975L/UserBundle/Entity/UserFull`: default user + address + business + social + multilingual fields

To help you choose, the fields are the following:

LIGHT
- id
- allow_use
- identifier
- email
- creation
- enabled
- salt
- password
- token
- password_request
- roles

DEFAULT
- gender
- firstname
- lastname
- avatar
- latest_signin
- latest_signout
- locale

ADDRESS
- address
- address2
- postal
- town
- country
- phone
- fax

BUSINESS
- business_type
- business_name
- business_address
- business_address2
- business_postal
- business_town
- business_country
- business_siret
- business_vat
- business_phone
- business_fax

SOCIAL
- social_network
- social_id
- social_token
- social_picture

You can also create your own Class by overriding. In this case, you need to extend one of the Abstract classes with the following code:
```php
<?php
//Your Entity file i.e. src/AppBundle/Entity/User.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//Just add 'Abstract' to the name of the extended class
use c975L\UserBundle\Entity\UserAbstract;

/**
 * @ORM\Table(name="user", indexes={@ORM\Index(name="un_email", columns={"name", "email"})})
 * @ORM\Entity(repositoryClass="c975L\UserBundle\Repository\UserRepository")
 */
class User extends UserAbstract
{
    //Add your properties and methods
}
```

Extending Forms
---------------
You can extend `UserSignupType` and `UserProfileType`. To extend them, to include new properties or features, simply use the following code:
```php
<?php
//Your own form i.e. src/AppBundle/Form/UserProfileType
namespace AppBundle\Form;

use c975L\UserBundle\Form\UserProfileType as BaseProfileType;

class UserProfileType extends BaseProfileType
{
    //Builds the form
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //You can use the following to disable/enable fields
        $disabled = $options['data']->getAction() == 'modify' ? false : true;

        //Add the fields you need
    }

    public function getParent()
    {
        return 'c975L\UserBundle\Form\UserProfileType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_profile';
    }
}
```

Then you have to add it as a service in your `app/config/services.yml`:
```yml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: true
    AppBundle\Form\:
        resource: '../../src/AppBundle/Form/*'
```

And finally, you have to set it in your `app/config/config.yml`
```yml
c975_l_user:
    signupForm: 'AppBundle\Form\UserSignupType'
    profileForm: 'AppBundle\Form\UserProfileType'
```

Events
------
Multiples events are fired to help you fit your needs, they are all defined in `Event\UserEvent.php`. For example if you need to perform taks before deleting a user, you can create a Listener like this:

```php
<?php

namespace AppBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use c975L\UserBundle\Entity\UserAbstract;
use c975L\UserBundle\Event\UserEvent;

class UserDeleteListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            UserEvent::USER_DELETE => 'userDelete',
        );
    }

    public function userDelete($event)
    {
        $user = $event->getUser();

        if ($user instanceof \Symfony\Component\Security\Core\User\AdvancedUserInterface) {
            //Do your stuff...
        }
    }
}
```

Service
-------
You can inject `c975L\UserBundle\Service\UserServiceInterface` to access its methods. For example you can retrieve a user with its id, email, socialId, ...
```php
//Within a controller

    public function yourAction(UserServiceInterface $userService)
    {
        //With Id
        $user = $userService->findUserById(USER_ID);

        //With Email
        $user = $userService->findUserByEmail(USER_EMAIL);

        //With Identifier
        $user = $userService->findUserByIdentifier(USER_IDENTIFIER);

        //With SocialId
        $user = $userService->findUserBySocialId(USER_SOCIAL_ID);
    }

```

Sign in/Sign out link
---------------------
If you want to insert a link to sign in/sign out, i.e. in the footer, you can do it via this code:
```
{# Sign in/Sign out #}
<p class="text-center">
    {% include '@c975LUser/fragments/signinSignout.html.twig' %}
</p>
```

User Div data for javascript use
--------------------------------
If you want to insert a div containing the user's data, to be used by javascript, you can do it via the Twig extension:
```
{# User DivData #}
{{ user_divData() }}
```

Then you can access it via
```javascript
$(document).ready(function() {
    var firstname = $('#user').data('firstname');
});
```
Have a look at it to see the properties covered.

Custom redirect after sign in
-----------------------------
If you want to redirect to a specific page you can use [Request Parameters](https://symfony.com/doc/current/security/form_login.html#control-the-redirect-using-request-parameters) with the following code:
```php
//In a Controller file
return $this->redirectToRoute('user_signin', array('_target_path' => 'THE_ABSOLUTE_OR_RELATIVE_URL_TO_REDIRECT_TO'));
```

User's avatar
-------------
You can display the avatar linked to user's account (if enabled in config.yml) by calling the Twig extension where you want to place it:
```twig
{{ user_avatar() }}
{# Or with specifying its size, 128 by default #}
{{ user_avatar(64) }}
```

Twig extension
--------------
You can use Twig extensions to format VAT and Siret numbers.
```
{{ 'YOUR_VAT_NUMBER'|user_vat }}
{{ 'YOUR_SIRET_NUMBER'|user_siret }}
```

Using HwiOauth (Social network sign in)
=======================================
On the sign in form you can add links to sign in/sign up with social networks via [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle). If you want to this feature, simply add in your `app/config/config.yml`, the following:
```yml
c975_l_user:
    hwiOauth: ['facebook', 'google', 'live']
    social: true
```

And in you `app/config/services.yml`, the following:
```yml
services:
    c975L\UserBundle\Security\OAuthUserProvider:
        public: true
```

**c975L/UserBundle doesn't implement the connection with social networks but provides a bridge with HWIOAuthBundle, to display buttons on the sign in page and to store users in the DB. You have to configure HWIOAuthBundle by your own.** This will mainly consist in setting differents informations in config files. As an example, they are listed below, for Facebook, but other networks will work in the same way:
```yml
#routing.yml
hwi_oauth_redirect:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
    prefix:   /connect
hwi_oauth_connect:
    resource: "@HWIOAuthBundle/Resources/config/routing/connect.xml"
    prefix:   /connect
hwi_oauth_login:
    resource: "@HWIOAuthBundle/Resources/config/routing/login.xml"
    prefix:   /login
facebook_login:
    path: /login/facebook
```

```yml
#parameters.yml
#As a Best Practice, it is preferable to declare your secret parameters in parameters.yml file in place of config.yml.
#Then you can re-use them with "%facebook_app_id%".
parameters:
    facebook_app_id: 'YOUR_FACEBOOK_APP_ID'
    facebook_app_secret: 'YOUR_FACEBOOK_APP_SECRET'
```

```yml
#parameters.yml.dist
parameters:
    facebook_app_id: ~
    facebook_app_secret: ~
```

You will have to declare the account_connector `c975L\UserBundle\Security\OAuthUserProvider`
```yml
#config.yml
hwi_oauth:
    connect:
        confirmation: true
        account_connector: c975L\UserBundle\Security\OAuthUserProvider
    firewall_names: [main]
    resource_owners:
        facebook:
            type: facebook
            client_id: "%facebook_app_id%"
            client_secret: "%facebook_app_secret%"
            scope: "email"
            options:
                csrf: true
                display: popup
```

You will have to declare the oauth_user_provider `c975L\UserBundle\Security\OAuthUserProvider`
```yml
#security.yml
security:
    #...
    firewalls:
        main:
            oauth:
                resource_owners:
                    facebook: "/login/facebook"
                login_path: user_signin
                failure_path: user_signin
                use_forward: true
                default_target_path: user_dashboard
                oauth_user_provider:
                    service: c975L\UserBundle\Security\OAuthUserProvider
```

Social networks images
----------------------
c975L/UserBundle provides images for some of the social networks, they were linked in your web folder when you have installed the assets (see above). If the network you want to use has not an image yet, you can use the file `Resources/SocialNetwork/signin.svg`` to build one and make a PR to add it to the bundle :-).

You can also override  `Resources/views/fragments/socialNetworkImage.html.twig` with your own pictures set or to change the styles used.

As a "Bonus" if a user has signed up with its email address and then use a social network to signin, it will get its existing user account **IF** emails addresses are the same, otherwise, it will create another account.

Signing up with another social network, after having already signed up with a different one, will replace the current one by the new one.

Migration from FOSUserBundle
============================
If you want to migrate from FOSUserBundle, you have to do the following:

Remove from composer
```bash
composer remove friendsofsymfony/user-bundle
```

Remove from `AppKernel.php`
```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\UserBundle\FOSUserBundle(), //Remove this line
        // ...
    );
}
```

Migrate your database table, by using `Resources\sql\MigrateFosUser.sql`. It will create a `user_migrate` table, will modify all the needed fields, will add missing ones, then, when you are ready, you can rename your FOSUSerBundle table to `user_fosuserbundle` (or whatever you want) and rename the `user_migrate` one to `user`. **Fields `username` and `groups` are kept but not used, so you can delete them if you don't use them.**

API Documentation
=================
You can also use the API provided in c975LUserBundle with the following:

You have to install `https://github.com/lcobucci/jwt` (openssl extension is required) using `composer require lcobucci/jwt`.

Then create your RSA keys:
```bash
cd <your_root_project_dir>;
mkdir -p config/jwt;
openssl genrsa -out config/jwt/private.pem -aes256 4096;
#If it requires passPhrase then enter one and un-comment and run the following
#openssl rsa -in config/jwt/private.pem -out config/jwt/private2.pem;
#mv config/jwt/private.pem config/jwt/private.pem-back;
#mv config/jwt/private2.pem config/jwt/private.pem;
#rm config/jwt/private.pem-back;
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem;
```
Add those paths to `config\config_bundles.yaml` or using c975L\ConfigBundle :

c975LUser:
    privateKey: 'config/jwt/private.pem'
    publicKey: 'config/jwt/public.pem'
    api: true
    apiOnly: true #If you wish to only use API and not the web forms

Define the JSON end point in your `security.yaml`:
```yml
security:
    firewalls:
        main:
            json_login:
                check_path: user_api_authenticate
            anonymous: true
            guard:
                authenticators:
                    - c975L\UserBundle\Security\TokenAuthenticator
```

Then you can use the different Routes :

Except for `user_api_create` and `user_api_authenticate` you need to send the JWT (obtained via `user_api_authenticate`) in the header `Authorization: Bearer <token>` (recommended) or in the header `X-AUTH-TOKEN: <token>` for all the API Routes requests.

`/user/api/create`
------------------
`@Method({"HEAD", "POST"})`. To create the user, call the Route `user_api_create` with the POST data needed by the User entity chosen (see above). Fields `email` and `plainPassword` are mandatory any other will be added to the Entity if the Method exists. You also need to add a field `apiKey` which consists of `sha1($email . apiPassword)`, `apiPassword` is defined in the `user_config` Route. It is also recommended to define CORS access.

`/user/api/authenticate`
------------------------
`@Method({"HEAD", "POST"})`. To authenticate, call the Route `user_api_authenticate` with the JSON body `{"username": "<email>", "password": "<password>"}` in a `POST` request, with the header `Content-Type: application/json`, you will receive a token.

`/user/api/display/{identifier}`
--------------------------------
`@Method({"HEAD", "GET"})`, `{identifier} -> [0-9a-z]{32}`. To display the user, call the Route `user_api_display` with the `identifier` of the user. The user defined in JWT must have sufficients rights, as configured in `user_config` Route or be the user itself.

`/user/api/modify/{identifier}`
-------------------------------
`@Method({"HEAD", "PUT"})`, `{identifier} -> [0-9a-z]{32}`. To modify the user, call the Route `user_api_modify` with the `identifier` of the user and the POST data needed by the User entity chosen (see above). The user defined in JWT must have sufficients rights, as configured in `user_config` Route or be the user itself.

`/user/api/delete/{identifier}`
-------------------------------
`@Method({"HEAD", "DELETE"})`, `{identifier} -> [0-9a-z]{32}`. To delete the user, call the Route `user_api_delete` with the `identifier` of the user. the user will be archived if you have defined it in the Config parameters. The user defined in JWT must have sufficients rights, as configured in `user_config` Route or be the user itself.

`/user/api/add-role/{identifier}/{role}`
----------------------------------------
`@Method({"HEAD", "PUT"})`, `{identifier} -> [0-9a-z]{32}`, `{role} -> [a-zA-Z\_]+`. To add a Role to the user, call the Route `user_api_add_role` with the `identifier` of the user and the `role` you want to assign. The user defined in JWT must have sufficients rights, as configured in `user_config` Route.

`/user/api/delete-role/{identifier}/{role}`
-------------------------------------------
`@Method({"HEAD", "PUT"})`, `{identifier} -> [0-9a-z]{32}`, `{role} -> [a-zA-Z\_]+`. To display the user, call the Route `user_api_delete_role` with the `identifier` of the user and the `role` you want to delete. The user defined in JWT must have sufficients rights, as configured in `user_config` Route.

**If this project help you to reduce time to develop, you can [buy me a coffee](https://www.buymeacoffee.com/LaurentMarquet) :)**
