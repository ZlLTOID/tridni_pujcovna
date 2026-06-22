<?php

namespace App\Controller;

use App\Entity\Trida;
use App\Entity\Zak;
use App\Repository\TridaRepository;
use App\Repository\ZakRepository;
use App\Security\TridaVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ZakController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TridaRepository $tridaRepository,
        private readonly ZakRepository $zakRepository,
    ) {
    }

    #[Route('/trida/{tridaId}/zak/novy', name: 'app_zak_create', requirements: ['tridaId' => '\d+'], methods: ['GET'])]
    public function create(int $tridaId): Response
    {
        $trida = $this->getTridaOrRedirect($tridaId);
        if (!$trida instanceof Trida) {
            return $trida;
        }

        return $this->render('zak/create.html.twig', ['trida' => $trida]);
    }

    #[Route('/trida/{tridaId}/zak', name: 'app_zak_store', requirements: ['tridaId' => '\d+'], methods: ['POST'])]
    public function store(int $tridaId, Request $request): Response
    {
        $trida = $this->getTridaOrRedirect($tridaId);
        if (!$trida instanceof Trida) {
            return $trida;
        }

        $jmeno = trim((string) $request->request->get('jmeno', ''));
        $prijmeni = trim((string) $request->request->get('prijmeni', ''));

        if ($jmeno === '' || $prijmeni === '') {
            $this->addFlash('error', 'Vyplň jméno i příjmení žáka.');

            return $this->redirectToRoute('app_zak_create', ['tridaId' => $tridaId]);
        }

        $zak = new Zak();
        $zak->setJmeno($jmeno);
        $zak->setPrijmeni($prijmeni);
        $zak->setTrida($trida);

        $this->em->persist($zak);
        $this->em->flush();

        $this->addFlash('success', 'Žák byl přidán.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $tridaId]);
    }

    #[Route('/zak/{id}/upravit', name: 'app_zak_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(int $id): Response
    {
        $zak = $this->zakRepository->findById($id);
        if (!$zak) {
            throw $this->createNotFoundException('Žák nenalezen.');
        }

        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $zak->getTrida());

        return $this->render('zak/edit.html.twig', ['zak' => $zak]);
    }

    #[Route('/zak/{id}/upravit', name: 'app_zak_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $zak = $this->zakRepository->findById($id);
        if (!$zak) {
            throw $this->createNotFoundException('Žák nenalezen.');
        }

        $trida = $zak->getTrida();
        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        $jmeno = trim((string) $request->request->get('jmeno', ''));
        $prijmeni = trim((string) $request->request->get('prijmeni', ''));

        if ($jmeno === '' || $prijmeni === '') {
            $this->addFlash('error', 'Vyplň jméno i příjmení žáka.');

            return $this->redirectToRoute('app_zak_edit', ['id' => $id]);
        }

        $zak->setJmeno($jmeno);
        $zak->setPrijmeni($prijmeni);
        $this->em->flush();

        $this->addFlash('success', 'Údaje žáka byly uloženy.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $trida->getId()]);
    }

    #[Route('/zak/{id}/smazat', name: 'app_zak_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(int $id): Response
    {
        $zak = $this->zakRepository->findById($id);
        if (!$zak) {
            throw $this->createNotFoundException('Žák nenalezen.');
        }

        $trida = $zak->getTrida();
        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        $tridaId = $trida->getId();
        $this->em->remove($zak);
        $this->em->flush();

        $this->addFlash('success', 'Žák byl odebrán.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $tridaId]);
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
