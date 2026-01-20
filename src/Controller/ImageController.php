<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageFormType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ImageController extends AbstractController
{
    #[Route('/imagen-nueva', name: 'imagen-nueva')]
    public function addImage(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageFormType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'), // Ya lo tienes configurado en services.yaml
                        $newFilename
                    );
                    $filesystem = new Filesystem();
                    $filesystem->copy(
                        $this->getParameter('images_directory') . '/' . $newFilename,
                        $this->getParameter('portfolio_directory') . '/' . $newFilename,
                        true
                    );

                } catch (FileException $e) {
                    $this->addFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    return $this->redirectToRoute('imagen-nueva');
                }

// Guardamos el nombre del archivo en la entidad
                $image->setFile($newFilename);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->persist($image);
            $entityManager->flush();

            $this->addFlash('success', 'Imagen subida correctamente!');
            return $this->redirectToRoute('archieves'); // Redirige a la galería
        }

        return $this->render('page/imagen-nueva.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/image/like/{id}', methods: ['POST'])]
    public function like(Image $image, EntityManagerInterface $em): JsonResponse
    {
        $image->setNumLikes($image->getNumLikes() + 1);
        $em->flush();

        return $this->json([
            'numLikes' => $image->getNumLikes()
        ]);
    }

    #[Route('/image/download/{id}', methods: ['POST'])]
    public function download(Image $image, EntityManagerInterface $em): JsonResponse
    {
        $image->setNumDownloads($image->getNumDownloads() + 1);
        $em->flush();

        return $this->json([
            'numDownloads' => $image->getNumDownloads()
        ]);
    }

    #[Route('/image/view/{id}', name: 'image_view', methods: ['POST'])]
    public function view(int $id, EntityManagerInterface $em, ImageRepository $repo): JsonResponse
    {
        $image = $repo->find($id);
        if (!$image) {
            return $this->json(['error' => 'Imagen no encontrada'], 404);
        }

        $image->setNumViews($image->getNumViews() + 1);
        $em->flush();

        return $this->json(['numViews' => $image->getNumViews()]);
    }

    #[Route('/image/delete/{id}', name: 'image_delete', methods: ['POST'])]
    public function delete(Image $image, EntityManagerInterface $em): JsonResponse
    {
        $file = $this->getParameter('kernel.project_dir')
            .'/public/uploads/images/'.$image->getFile();

        if (file_exists($file)) {
            unlink($file);
        }

        $em->remove($image);
        $em->flush();

        return $this->json(['ok' => true]);
    }
}
