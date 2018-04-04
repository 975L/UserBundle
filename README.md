UserBundle
==========

Directly inspired from [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle), and migration of [c975L/UserFilesBundle](https://github.com/975L/UserFilesBundle/tree/master), UserBundle does the following:

- Stores users in database **(using doctrine)**,
- Provides multiples types of entities for User (see below),
- Allows extending those entities to add your own fields,
- Allows users to manage their profile and data,
- Validates data such as Siret, VAT number,
- Displays a "challenge" for sign up (no Captcha, etc.),
- Allows the possibility to disable sign up (for registering only one or more users and then no more),
- Provides forms for Sign in, Sign up, Modify profile, Change password and Reset password,
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

[User Bundle dedicated web page](https://975l.com/en/pages/user-bundle).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Use [Composer](https://getcomposer.org) to install the library
```bash
    composer require c975l/user-bundle
```

Step 2: Enable the Bundles
--------------------------
Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new c975L\EmailBundle\c975LEmailBundle(),
            new c975L\UserBundle\c975LUserBundle(),
        ];
    }
}
```

Step 3: Configure the Bundle
----------------------------
Check [Swiftmailer](https://github.com/symfony/swiftmailer-bundle), [Doctrine](https://github.com/doctrine/DoctrineBundle) and [c975l/EmailBundle](https://github.com/975L/EmailBundle) for their specific configuration.

Then, in the `app/config.yml` file of your project, define the following:

```yml
#UserBundle
c975_l_user:
    #Name of site to be displayed
    site: 'Example.com'
    #If signup is allowed or not
    signup: true #false(default)
    #User's role needed to enable access other user's data
    roleNeeded: 'ROLE_ADMIN'
    #The location of your Terms of uses to be displayed to user, it can be a Route with parameters or an absolute url
    touUrl: 'pageedit_display, {page: terms-of-use}'
    #(Optional) If you want to define the allowed number of attempts for sign in and then add a delay before being able to sign in again
    #This feature is linked to session not to accounts
    signinAttempts: 5 #0(default) Depending on your website 5 attempts is a "good" number
    #(Optional) If you want to display the avatar linked to user's account
    #It can be the Gravatar's linked email image (by default) or the social network's image if enabled
    avatar: true #false(default)
    #(Optional) If you want to add social networks signin using https://github.com/hwi/HWIOAuthBundle
    hwiOauth: [] #i.e ['facebook', 'google', 'live'] null(default)
    #(Optional) If you want to use the social fields
    social: true #false(default)
    #(Optional) If you want to save the email sent to user when deleting his/her account in the database linked to c975L/EmailBundle
    databaseEmail: true #false(default)
    #(Optional) If you want to archive the user in `user_archives` table
    archiveUser: true #false(default)
    #(Optional) If you want to use the address fields
    address: true #false(default)
    #(Optional) If you want to use the business fields
    business: true #false(default)
    #(Optional) If you want to use the multilingual field. Array of language and code on two letters
    multilingual: {} #i.e {'English': 'en', 'Français': 'fr', 'Español': 'es'} null(default)
    #(Optional) The entity you want to use
    entity: 'AppBundle\Entity\User' #null(default)
    #(Optional) If you want to use your own Signup form
    signupForm: 'AppBundle\Form\UserSignupType' #'c975L\UserBundle\Entity\User'(default)
    #(Optional) If you want to use your own Profile form
    profileForm: 'AppBundle\Form\UserProfileType' #null(default)
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
            logout_on_user_change: true
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
- user_dashboard
- user_display
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

- `c975L/UserBundle/Entity/User`: default user
- `c975L/UserBundle/Entity/UserAddress`: default user + address fields
- `c975L/UserBundle/Entity/UserBusiness`: default user + business/association fields
- `c975L/UserBundle/Entity/UserSocial`: default user + social network fields
- `c975L/UserBundle/Entity/UserFull`: default user + address + business + social + multilingual fields

To help you choose, the fields are the following:

DEFAULT
- id
- identifier
- email
- gender
- firstname
- lastname
- creation
- avatar
- enabled
- salt
- password
- latest_signin
- latest_signout
- token
- password_request
- roles
- locale

ADDRESS
- address
- address2
- postal
- town
- country

BUSINESS
- business_type
- business_name
- business_address
- business_address2
- business_postal
- business_town
- business_country
- business_siret
- business_tva

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

        if (is_subclass_of($user, 'c975L\UserBundle\Entity\UserAbstract')) {
            //Do your stuff...
        }
    }
}
```

Service
-------
There is a defined UserService, check the file `Service\UserService.php` for its methhods. For example you can retrieve a user with its id, email, socialId, ...
```php
//Within a controller
$userService = $this->get(\c975L\UserBundle\Service\UserService::class);

//With Id
$user = $userService->findUserById(USER_ID);

//With Email
$user = $userService->findUserByEmail(USER_EMAIL);

//With Identifier
$user = $userService->findUserByIdentifier(USER_IDENTIFIER);

//With SocialId
$user = $userService->findUserBySocialId(USER_SOCIAL_ID);
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
