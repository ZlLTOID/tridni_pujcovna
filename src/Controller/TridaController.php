<?php

namespace App\Controller;

use App\Entity\Trida;
use App\Entity\Ucitel;
use App\Repository\TridaRepository;
use App\Repository\VecRepository;
use App\Repository\ZakRepository;
use App\Repository\ZapujceniRepository;
use App\Security\TridaVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/trida')]
class TridaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TridaRepository $tridaRepository,
        private readonly ZakRepository $zakRepository,
        private readonly VecRepository $vecRepository,
        private readonly ZapujceniRepository $zapujceniRepository,
    ) {
    }

    #[Route('/nova', name: 'app_trida_create', methods: ['GET'])]
    #[IsGranted('ROLE_UCITEL')]
    public function create(): Response
    {
        return $this->render('trida/create.html.twig');
    }

    #[Route('', name: 'app_trida_store', methods: ['POST'])]
    #[IsGranted('ROLE_UCITEL')]
    public function store(Request $request): Response
    {
        $nazev = trim((string) $request->request->get('nazev', ''));
        if ($nazev === '') {
            $this->addFlash('error', 'Zadej název třídy.');

            return $this->redirectToRoute('app_trida_create');
        }

        /** @var Ucitel $ucitel */
        $ucitel = $this->getUser();

        $trida = new Trida();
        $trida->setNazev($nazev);
        $trida->setUcitel($ucitel);

        $this->em->persist($trida);
        $this->em->flush();

        $this->addFlash('success', 'Třída „' . $nazev . '“ byla vytvořena.');

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/{id}', name: 'app_trida_detail', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function detail(int $id): Response
    {
        $trida = $this->tridaRepository->findById($id);
        if (!$trida) {
            throw $this->createNotFoundException('Třída nenalezena.');
        }

        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        return $this->render('trida/detail.html.twig', [
            'trida' => $trida,
            'zaci' => $this->zakRepository->findByTridaId($trida->getId()),
            'veci' => $this->vecRepository->findForTridaWithActiveLoan($trida->getId()),
            'zapujceni' => $this->zapujceniRepository->findForTridaId($trida->getId()),
        ]);
    }

    #[Route('/{id}/smazat', name: 'app_trida_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_UCITEL')]
    public function delete(int $id): Response
    {
        $trida = $this->tridaRepository->findById($id);
        if (!$trida) {
            throw $this->createNotFoundException('Třída nenalezena.');
        }

        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        $this->em->remove($trida);
        $this->em->flush();

        $this->addFlash('success', 'Třída byla smazána.');

        return $this->redirectToRoute('app_dashboard');
    }
}
