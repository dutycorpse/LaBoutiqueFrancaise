<?php

namespace App\Controller;

use App\Classe\Cart;
use App\Entity\Address;
use App\Form\AddressType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountAddressController extends AbstractController
{
    /**
     * @Route("/compte/adresse", name="account_address")
     */
    public function index(): Response
    {


        return $this->render('account/address.html.twig');
    }

    /**
     * @Route("/compte/ajouter-une-adresse", name="account_address_add")
     */
    public function add(Cart $cart, Request $request, EntityManagerInterface $entityManager): Response
    {
        $address = new Address();

        $form = $this->createForm( AddressType::class, $address );


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            
            $address->setUser($this->getUser());

            $entityManager->persist($address);
            $entityManager->flush();
            
            // verifie les produtis panier voir si l'user vient du tunnel d'achat
            if($cart->get()){

                return $this->redirectToRoute( 'order');

            } else {

                return $this->redirectToRoute( 'account_address');

            }

        };

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


     /**
     * @Route("/compte/modifier-une-adresse/{id}", name="account_address_edit")
     */
    public function edit(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $address = $entityManager->getRepository( Address::class)->findOneById($id);

        // verifie si l'adress exist ou si l'addresse n'apartient pas a l'user
        if (!$address || $address->getUser() != $this->getUser()) {
            return $this->redirectToRoute('account_address');
        }

        $form = $this->createForm( AddressType::class, $address );


        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            
            $entityManager->flush();
            
            return $this->redirectToRoute( 'account_address');

        };

        return $this->render('account/address_form.html.twig', [
            'form' => $form->createView()
        ]);
    }


     /**
     * @Route("/compte/supprimer-une-adresse/{id}", name="account_address_delete")
     */
    public function delete(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $address = $entityManager->getRepository( Address::class)->findOneById($id);

        // verifie si l'adress exist ou si l'addresse n'apartient pas a l'user
        if ($address && $address->getUser() == $this->getUser()) {

            $entityManager->remove($address);
            $entityManager->flush();
        }

        
        return $this->redirectToRoute( 'account_address');
    }
}
