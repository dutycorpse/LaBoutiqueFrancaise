<?php

namespace App\Controller;

use DateTime;
use App\Classe\Mail;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Form\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordController extends AbstractController
{
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ( $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')) {
           $user = $entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));

           if ($user) {

            // 1 enregistrer en base la demande de reset password avec user , token ,createdAt
               $resest_password = new ResetPassword();
               $resest_password->setUser($user);
               $resest_password->setToken(uniqid());
               $resest_password->setCreatedAt(new \DateTime());

               $entityManager->persist($resest_password);
               $entityManager->flush();

            // 2 Envoyer un email a l'utilisateur avec un lien pour mettre a jour de le mdp

                $url = $this->generateUrl('update_password', [
                    'token' => $resest_password->getToken()
                ]);

                $content = "Bonjour ".$user->getFirstname()."<br/>Vous avez demandé à réinitialiser votre mot de passe sur le site La Boutique Françcaise.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>.";
                $mail = new Mail();
                $mail->send($user->getEmail(), $user->getFirstname().' '.$user->getLastname(), 'Réinitialiser votre mot de passe sur La Boutique Française', $content );

                $this->addFlash('notice', 'Vous allez recevoir dans quelques secondes un mail avec la procedure pour réinitialiser votre mot de passe.');
           } else {

                $this->addFlash('notice', 'Cette adresse email est inconnu.');

           }
        }


        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-de-passe/{token}", name="update_password")
     */
    public function update($token, EntityManagerInterface $entityManager, Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $resest_password = $entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if (!$resest_password) {
            return $this->redirectToRoute('reset_password');
        }
        //Vérifier si le createdAt = now - 3h
        $now = new \DateTime();
        if ($now > $resest_password->getCreatedAt()->modify('+ 3 hour')) {
        
            $this->addFlash('notice', 'Votre demande de mot de passe a expiré. Merci de la renouveller.');
            return $this->redirectToRoute('reset_password');

        }
       

        // rendre une vue avec mot de passe et confirmez votre mot de passe.
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            
            $new_pwd = $form->get('new_password')->getData();

            
            // encodage mdp

            $password = $encoder->encodePassword($resest_password->getUser(), $new_pwd);


            $resest_password->getUser()->setPassword($password);

    
            // flush en bdd 
            $entityManager->flush();
            // redirect vers connexion
            $this->addFlash('notice', 'Votre mot de passe a bien été mis a jour.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/update.html.twig', [
            'form' => $form->createView()
        ]);

        
        
    }
}
