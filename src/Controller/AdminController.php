<?php

namespace App\Controller;

use App\Entity\Ucitel;
use App\Repository\TridaRepository;
use App\Repository\UcitelRepository;
use App\Repository\ZapujceniRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UcitelRepository $ucitelRepository,
        private readonly TridaRepository $tridaRepository,
        private readonly ZapujceniRepository $zapujceniRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'app_admin')]
    public function index(): Response
    {
        $ucitele = $this->ucitelRepository->findAllOrdered();
        $tridy = $this->tridaRepository->findAllWithRelationsForAdmin();
        $zapujceni = $this->zapujceniRepository->findRecentForAdmin();

        $aktivniPoTridach = [];
        foreach ($tridy as $trida) {
            $aktivniPoTridach[$trida->getId()] = $this->zapujceniRepository->countActiveForTridaId($trida->getId());
        }

        return $this->render('admin/index.html.twig', [
            'ucitele' => $ucitele,
            'tridy' => $tridy,
            'zapujceni' => $zapujceni,
            'aktivniPoTridach' => $aktivniPoTridach,
        ]);
    }

    #[Route('/ucitel/{id}/heslo', name: 'app_admin_password', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function passwordForm(int $id): Response
    {
        $ucitel = $this->ucitelRepository->findById($id);
        if (!$ucitel) {
            throw $this->createNotFoundException('Učitel nenalezen.');
        }

        return $this->render('admin/password.html.twig', ['ucitel' => $ucitel]);
    }

    #[Route('/ucitel/{id}/heslo', name: 'app_admin_password_store', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function passwordStore(int $id, Request $request): Response
    {
        $ucitel = $this->ucitelRepository->findById($id);
        if (!$ucitel) {
            throw $this->createNotFoundException('Učitel nenalezen.');
        }

        $password = (string) $request->request->get('password', '');
        if ($password === '') {
            $this->addFlash('error', 'Zadej nové heslo.');

            return $this->redirectToRoute('app_admin_password', ['id' => $ucitel->getId()]);
        }

        $ucitel->setPasswordHash($this->passwordHasher->hashPassword($ucitel, $password));
        $this->em->flush();

        $this->addFlash('success', 'Heslo bylo změněno.');

        return $this->redirectToRoute('app_admin');
    }
}
