<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): Response
    {

        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

           
            $user = $form->getData();

            $search_email = $manager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if (!$search_email) {
                $password = $encoder->encodePassword($user, $user->getPassword());

                $user->setPassword($password);
    
                $manager->persist($user);
                $manager->flush();

                $mail = new Mail();
                $content = "Bonjour".$user->getFirstname()."<br/>Bienvenue sur la première boutique made in france";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur La boutique Française', $content);

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès a présent vous connecter a votre compte." ;

            } else {

                $notification = "l'email que vous avez renseigné existe déjà.";
            }

           
            


        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification

        ]);
    }
}
