<?php

namespace App\Controller;

use App\Entity\Trida;
use App\Entity\Vec;
use App\Repository\TridaRepository;
use App\Repository\VecRepository;
use App\Security\TridaVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VecController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TridaRepository $tridaRepository,
        private readonly VecRepository $vecRepository,
        private readonly string $projectDir,
    ) {
    }

    #[Route('/trida/{tridaId}/vec/nova', name: 'app_vec_create', requirements: ['tridaId' => '\d+'], methods: ['GET'])]
    public function create(int $tridaId): Response
    {
        $trida = $this->getTridaOrRedirect($tridaId);
        if (!$trida instanceof Trida) {
            return $trida;
        }

        return $this->render('vec/create.html.twig', ['trida' => $trida]);
    }

    #[Route('/trida/{tridaId}/vec', name: 'app_vec_store', requirements: ['tridaId' => '\d+'], methods: ['POST'])]
    public function store(int $tridaId, Request $request): Response
    {
        $trida = $this->getTridaOrRedirect($tridaId);
        if (!$trida instanceof Trida) {
            return $trida;
        }

        $nazev = trim((string) $request->request->get('nazev', ''));
        if ($nazev === '') {
            $this->addFlash('error', 'Zadej název věci.');

            return $this->redirectToRoute('app_vec_create', ['tridaId' => $tridaId]);
        }

        $foto = null;
        /** @var UploadedFile|null $upload */
        $upload = $request->files->get('foto');
        if ($upload) {
            $ext = strtolower($upload->guessExtension() ?? $upload->getClientOriginalExtension());
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed, true)) {
                $this->addFlash('error', 'Fotka musí být obrázek (jpg, png, gif, webp).');

                return $this->redirectToRoute('app_vec_create', ['tridaId' => $tridaId]);
            }

            $filename = uniqid('vec_', true) . '.' . $ext;
            $upload->move($this->projectDir . '/public/uploads', $filename);
            $foto = '/uploads/' . $filename;
        }

        $vec = new Vec();
        $vec->setNazev($nazev);
        $vec->setFoto($foto);
        $vec->setTrida($trida);

        $this->em->persist($vec);
        $this->em->flush();

        $this->addFlash('success', 'Věc byla přidána.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $tridaId]);
    }

    #[Route('/vec/{id}/upravit', name: 'app_vec_edit', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function edit(int $id): Response
    {
        $vec = $this->vecRepository->findById($id);
        if (!$vec) {
            throw $this->createNotFoundException('Věc nenalezena.');
        }

        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $vec->getTrida());

        return $this->render('vec/edit.html.twig', ['vec' => $vec]);
    }

    #[Route('/vec/{id}/upravit', name: 'app_vec_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $vec = $this->vecRepository->findById($id);
        if (!$vec) {
            throw $this->createNotFoundException('Věc nenalezena.');
        }

        $trida = $vec->getTrida();
        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        $nazev = trim((string) $request->request->get('nazev', ''));
        if ($nazev === '') {
            $this->addFlash('error', 'Zadej název věci.');

            return $this->redirectToRoute('app_vec_edit', ['id' => $id]);
        }

        $vec->setNazev($nazev);

        if (!$vec->jeZapujcena()) {
            /** @var UploadedFile|null $upload */
            $upload = $request->files->get('foto');
            if ($upload) {
                $ext = strtolower($upload->guessExtension() ?? $upload->getClientOriginalExtension());
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowed, true)) {
                    $this->addFlash('error', 'Fotka musí být obrázek (jpg, png, gif, webp).');

                    return $this->redirectToRoute('app_vec_edit', ['id' => $id]);
                }

                if ($vec->getFoto()) {
                    $oldPath = $this->projectDir . '/public' . $vec->getFoto();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $filename = uniqid('vec_', true) . '.' . $ext;
                $upload->move($this->projectDir . '/public/uploads', $filename);
                $vec->setFoto('/uploads/' . $filename);
            } elseif ($request->request->getBoolean('smazat_foto') && $vec->getFoto()) {
                $oldPath = $this->projectDir . '/public' . $vec->getFoto();
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $vec->setFoto(null);
            }
        }

        $this->em->flush();

        $this->addFlash('success', 'Věc byla upravena.');

        return $this->redirectToRoute('app_trida_detail', ['id' => $trida->getId()]);
    }

    #[Route('/vec/{id}/smazat', name: 'app_vec_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(int $id): Response
    {
        $vec = $this->vecRepository->findById($id);
        if (!$vec) {
            throw $this->createNotFoundException('Věc nenalezena.');
        }

        $trida = $vec->getTrida();
        $this->denyAccessUnlessGranted(TridaVoter::MANAGE, $trida);

        if ($vec->jeZapujcena()) {
            $this->addFlash('error', 'Nelze smazat věc, která je právě zapůjčená.');

            return $this->redirectToRoute('app_trida_detail', ['id' => $trida->getId()]);
        }

        if ($vec->getFoto()) {
            $path = $this->projectDir . '/public' . $vec->getFoto();
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $tridaId = $trida->getId();
        $this->em->remove($vec);
        $this->em->flush();

        $this->addFlash('success', 'Věc byla smazána.');

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
