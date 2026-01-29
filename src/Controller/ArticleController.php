<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article')]
class ArticleController extends AbstractController
{
    #[Route('/', name: 'article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Vue publique : seulement les articles publiés
        $articles = $articleRepository->findBy(['published' => true]);

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/admin', name: 'article_admin', methods: ['GET'])]
    public function admin(ArticleRepository $articleRepository): Response
    {
        // Vue d'administration : tous les articles (publiés et brouillons)
        $articles = $articleRepository->findAll();

        return $this->render('article/admin.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/new', name: 'article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/historique', name: 'article_history', methods: ['GET'])]
    public function history(ArticleRepository $articleRepository): Response
    {
        // Historique : uniquement les articles dépubliés (brouillons)
        $articles = $articleRepository->findBy(['published' => false]);

        return $this->render('article/history.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/{id}', name: 'article_show', methods: ['GET'])]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    #[Route('/{id}/edit', name: 'article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/toggle', name: 'article_toggle', methods: ['POST'])]
    public function toggle(Article $article, EntityManagerInterface $entityManager): Response
    {
        // Inverse l'état publié/dépublié
        $article->setPublished(!$article->isPublished());

        // Sauvegarde en base
        $entityManager->flush();

        // Retour à la liste des articles
        return $this->redirectToRoute('article_index');
    }

    #[Route('/{id}', name: 'article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        // Inverse l'état publié/dépublié
        $article->setPublished(!$article->isPublished());

        // Sauvegarde en base
        $entityManager->flush();

        // Retour à la liste des articles
        return $this->redirectToRoute('article_index');
    }
}
