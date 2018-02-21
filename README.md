UserBundle
==========

Directly inspired from [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle), and migration of [c975L/UserFilesBundle](https://github.com/975L/UserFilesBundle/tree/master), UserBundle does the following:

- Stores users in database (using doctrine),
- Allows users to manage their profile and data,
- Displays a "challenge" for registration,
- Allows the possibility to disable registration (for registering only one or more users and then no more),
- provides forms for Sin in, Register, Modify profile, Change password, Reset password,
- Sends email about registration and password reset to the user via [c975LEmailBundle](https://github.com/975L/EmailBundle) as `c975LEmailBundle` provides the possibility to save emails in a database, there is an option to NOT do so via this Bundle,
- Integrates with [c975LToolbarBundle](https://github.com/975L/ToolbarBundle),


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
    #If registration is allowed or not
    registration: false #true(default)
    #(Optional) If you want to display the gravatar linked to the email user's account
    gravatar: true #null(default)
    #(Optional) If you want to add social networks login using https://github.com/hwi/HWIOAuthBundle
    hwiOauth: [] #i.e ['facebook', 'google', 'live'] null(default)
    #(Optional) If you want to save the email sent to user when deleting his/her account in the database linked to c975L/EmailBundle
    databaseEmail: true #false(default)
    #(Optional) If you want to archive the user in `user_archives` table (you need to create this table, see below)
    archiveUser: true #false(default)
```

And finally in `app/security.yml`

```yml
security:
    encoders:
        c975L\UserBundle\Entity\User: bcrypt
    role_hierarchy:
        ROLE_MODERATOR:   ROLE_USER
        ROLE_ADMIN:       [ROLE_MODERATOR, ROLE_USER]
        ROLE_SUPER_ADMIN: [ROLE_ADMIN, ROLE_MODERATOR, ROLE_USER]
    providers:
        c975_l_userbundle:
            entity:
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
            anonymous:    true
            logout_on_user_change: true
            logout:
                path: user_signout
                handlers: [c975L\UserBundle\Listeners\LogoutListener]
```

Step 4: Create MySql table
--------------------------
Use `/Resources/sql/user.sql` to create the table `user` if not already existing. The `DROP TABLE` is commented to avoid dropping by mistake.
You can also create the table `user_archives` + stored procedure `sp_UserArchive` to archive the user when deleting account, for this, copy/paste the code from file `/Resources/sql/user.sql`, then set config value `archiveUser` to true.

Step 5: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_user:
    resource: "@c975LUserBundle/Controller/"
    type:     annotation
    #Multilingual website use: prefix: /{_locale}
    prefix:   /
```

Overriding Templates
--------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LUserBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle files, then apply your needed changes.

You also have to override:
- `app/Resources/c975LUserBundle/views/emails/layout.html.twig` to set data related to your emails.
- `app/Resources/c975LUserBundle/views/fragments/registerAcceptanceInfo.html.twig` to display links (Terms of use, Privacy policy, etc.) displayed in the register form.
- `app/Resources/c975LUserBundle/views/fragments/deleteAccountInfo.html.twig` that will list the implications, by deleting account, for user, displayed in the delete account page.
- `app/Resources/c975LUserBundle/views/fragments/dashboardActions.html.twig` to add your own actions (or whatever) in the dashboard i.e.

You can add a navbar menu via `{% include('@c975LUser/fragments/navbarMenu.html.twig') %}`. You can override it, if needed, or simply override `/fragments/navbarMenuActions.html.twig` to add actions above it.

Routes
------
The Routes availables are:
- user_register
- user_register_confirm
- user_signin
- user_dashboard
- user_display
- user_modify
- user_change_password
- user_reset_password
- user_reset_password_confirm
- user_signout
- user_delete

Using HwiOauth (Social network sign in)
---------------------------------------
You can display links on the login page to sign in with social network/s. **This bundle doesn't implement this functionality**, it only displays button/s on the login page. You have to configure [HWIOAuthBundle](https://github.com/hwi/HWIOAuthBundle) by your own.
If you use it, simply indicate in `app/config/confg.yml` (see above)
```yml
c975_l_user:
    #Indicates the networks you want to appear on the login page
    hwiOauth: ['facebook', 'google', 'live'] #Default null
```
You also have to upload images on your website named `web/images/signin-[network].png` (width="200" height="50"), where `network` is the name defined in the config.yml file.

Overriding Entity
-----------------
To add more fields (address, etc.) to the Entity `User`, you need to extend `c975L/UserBundle/Entity/User`. It gives the following code:

Create the file `src/UserBundle/UserBundle.php`:
```php
<?php

namespace UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserBundle extends Bundle
{
    public function getParent()
    {
        return 'c975LUserBundle';
    }
}
```

Copy/paste the file `Entity/User.php` in `src/UserBundle/Entity/`
```php
<?php

//Change the namespace
namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use c975L\UserBundle\Entity\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpFoundation\Request;
use c975L\UserBundle\Validator\Constraints as UserBundleAssert;
use c975L\UserBundle\Validator\Constraints\Challenge;
//...

/**
 * @ORM\Entity
 */
class User extends BaseUser
{
    //Keep all the fields and functions and add your own
}
```

Overridding Forms
-----------------
To override Forms, create the file `src/UserBundle/UserBundle.php` as explained above and duplicate the Forms in `src/UserBundle/Form/User[FormName]Type.php`, (i.e. for Profile Form)
```php
<?php

//Change the namespace
namespace UserBundle\Form;

//...
use c975L\UserBundle\Form\UserProfileType as BaseProfileType;

class UserProfileType extends BaseProfileType
{
    //Do your stuff...
}

```

In `app/config/services.yml` add a service (i.e. for Profile Form):
```yml
services:
    app.user.profile:
        class: UserBundle\Form\ProfileType
        arguments: ['@security.token_storage']
        tags:
            - { name: form.type }
```

In `app/config/config.yml` change the `type` linked to the form (i.e. for Profile Form)
```yml
fos_user:
    profile:
        form:
            type:  UserBundle\Form\ProfileType
```

Overriding Controller
---------------------
To override Controller, create the file `src/UserBundle/UserBundle.php` as explained above and duplicate the Controller in `src/UserBundle/Controller/UserController.php`
```php
<?php

//Change the namespace
namespace UserBundle\Controller;

//...
use c975L\UserBundle\Controller\UserController as BaseController;

class UserController extends BaseController
{
//DELETE USER
    /**
     * @Route("/delete",
     *      name="user_delete_account")
     * @Method({"GET", "HEAD", "POST"})
     */
    public function deleteAccountAction(Request $request)
    {
        parent::deleteAccountAction($request);
        //Do your stuff...
    }
}
```

The method `deleteAccountUserFunction()` is there to easily allow adding functions for delete user. Simply Override it in the Controller as described above.
