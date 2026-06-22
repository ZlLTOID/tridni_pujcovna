<?php

namespace App\Controller;

use App\Entity\Ucitel;
use App\Repository\TridaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly TridaRepository $tridaRepository,
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        /** @var Ucitel $user */
        $user = $this->getUser();

        $tridy = $user->jeAdmin()
            ? $this->tridaRepository->findAllWithRelationsForAdmin()
            : $this->tridaRepository->findForUcitelId($user->getId());

        return $this->render('dashboard/index.html.twig', [
            'tridy' => $tridy,
        ]);
    }
}
