<?php

namespace App\Controller;

use App\Entity\Trida;
use App\Entity\Zapujceni;
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

class ZapujceniController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TridaRepository $tridaRepository,
        private readonly ZakRepository $zakRepository,
        private readonly VecRepository $vecRepository,
        private readonly ZapujceniRepository $zapujceniRepository,
    ) {
    }

    #[Route('/trida/{tridaId}/zapujcit', name: 'app_zapujceni_create', requirements: ['tridaId' => '\d+'], methods: ['GET'])]
    public function create(int $tridaId): Response
    {
        $trida = $this->getTridaOrRedirect($tridaId);
        if (!$trida instanceof Trida) {
            return $trida;
        }

        return $this->render('zapujceni/create.html.twig', [
            'trida' => $trida,
            'zaci' => $this->zakRepository->findByTridaId($tridaId),
            'veci' => $this->vecRepository->findForTridaPicker($tridaId),
        ]);
    }

    #[Route('/trida/{tridaId}/zapujcit', name: 'app_zapujceni_store', requirements: ['tridaId' => '\d+'], methods: ['POST'])]
    public function store(int $tridaId, Request $request): Response
    {
        $trida = $this->getTridaOrRedirect($tridaId);
        if (!$trida instanceof Trida) {
            return $trida;
        }

        $vecId = (int) $request->request->get('vec_id', 0);
        $zakId = (int) $request->request->get('zak_id', 0);
        $datum = (string) $request->request->get('datum_zapujceni', date('Y-m-d\TH:i'));
        $poznamka = trim((string) $request->request->get('poznamka', ''));

        $vec = $this->vecRepository->findById($vecId);
        $zak = $this->zakRepository->findById($zakId);

        if (!$vec || $vec->getTrida()->getId() !== $tridaId || $vec->jeZapujcena()) {
            $this->addFlash('error', 'Tuto věc nelze zapůjčit — je už půjčená nebo neexistuje.');

            return $this->redirectToRoute('app_zapujceni_create', ['tridaId' => $tridaId]);
        }

        if (!$zak || $zak->getTrida()->getId() !== $tridaId) {
            $this->addFlash('error', 'Vyber platného žáka z této třídy.');

            return $this->redirectToRoute('app_zapujceni_create', ['tridaId' => $tridaId]);
        }

        $zapujceni = new Zapujceni();
        $zapujceni->setDatumZapujceni(new \DateTimeImmutable($datum));
        $zapujceni->setVec($vec);
        $zapujceni->setZak($zak);
        $zapujceni->setPoznamka($poznamka !== '' ? $poznamka : null);
        $zapujceni->setAktivni(true);
        $vec->setZapujcena(true);

        $this->em->persist($zapujceni);
        $this->em->flush();

        $this->addFlash('success', 'Věc byla zapůjčena.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $tridaId]);
    }

    #[Route('/zapujceni/{id}/vratit', name: 'app_zapujceni_return', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function returnItem(int $id): Response
    {
        $zapujceni = $this->zapujceniRepository->findById($id);
        if (!$zapujceni || !$zapujceni->isAktivni()) {
            $this->addFlash('error', 'Zapůjčení nelze vrátit.');

            return $this->redirectToRoute('app_dashboard');
        }

        $trida = $zapujceni->getVec()->getTrida();
        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        $zapujceni->vratit();
        $this->em->flush();

        $this->addFlash('success', 'Věc byla vrácena.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $trida->getId()]);
    }

    private function getTridaOrRedirect(int $tridaId): Trida|Response
    {
        $trida = $this->tridaRepository->findById($tridaId);
        if (!$trida) {
            $this->addFlash('error', 'Třída nenalezena.');

            return $this->redirectToRoute('app_dashboard');
        }

        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        return $trida;
    }
}
