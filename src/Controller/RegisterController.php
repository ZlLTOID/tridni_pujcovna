<?php

namespace App\Controller;

use App\Entity\Ucitel;
use App\Repository\UcitelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UcitelRepository $ucitelRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        if ($request->isMethod('POST')) {
            $username = trim((string) $request->request->get('username', ''));
            $password = (string) $request->request->get('password', '');
            $passwordConfirm = (string) $request->request->get('password_confirm', '');
            $jmeno = trim((string) $request->request->get('jmeno', ''));
            $prijmeni = trim((string) $request->request->get('prijmeni', ''));

            if ($username === '' || $password === '' || $passwordConfirm === '' || $jmeno === '' || $prijmeni === '') {
                $this->addFlash('error', 'Vyplň prosím všechna pole.');

                return $this->redirectToRoute('app_register');
            }

            if ($password !== $passwordConfirm) {
                $this->addFlash('error', 'Hesla se neshodují. Zkus to znovu.');

                return $this->redirectToRoute('app_register');
            }

            if ($this->ucitelRepository->findByUsername($username)) {
                $this->addFlash('error', 'Toto přihlašovací jméno už existuje.');

                return $this->redirectToRoute('app_register');
            }

            $ucitel = new Ucitel();
            $ucitel->setUsername($username);
            $ucitel->setJmeno($jmeno);
            $ucitel->setPrijmeni($prijmeni);
            $ucitel->setRole('ucitel');
            $ucitel->setPasswordHash($this->passwordHasher->hashPassword($ucitel, $password));

            $this->em->persist($ucitel);
            $this->em->flush();

            $this->addFlash('success', 'Registrace proběhla. Teď se můžeš přihlásit.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
    }
}
