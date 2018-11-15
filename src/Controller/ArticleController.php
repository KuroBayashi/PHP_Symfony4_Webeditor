<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/new/{id}",
     *     name="article_new",
     *     methods="GET|POST",
     *     defaults={"id": null}
     * )
     */
    public function new(Request $request, Category $category = null): Response
    {
        $article = new Article();

        if ($category)
            $article->setCategory($category);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $article->setUpdatedAt(new \DateTime());
                $em->persist($article);
                $em->flush();

                $this->addFlash('success', "L'article a bien été enregistré.");

                return $this->redirectToRoute('article_show', [
                    'id' => $article->getId()
                ]);
            }
            else {
                $this->addFlash('danger', "Formulaire invalide.");
            }
        }

        // View options
        $vars = ['form' => $form->createView()];
        if ($category)
            $vars['toolbox']['back'] = $category->getId();

        return $this->render('article/new.html.twig', $vars);
    }

    /**
     * @Route("/{id}",
     *     name="article_show",
     *     methods="GET"
     * )
     */
    public function show(Article $article = null): Response
    {
        if (!$article) {
            $this->addFlash('danger', "L'article demandé n'existe pas.");
            return $this->redirectToRoute('home');
        }

        // View options
        $vars = ['article' => $article];
        if ($article->getCategory())
            $vars['toolbox']['back'] = $article->getCategory()->getId();

        return $this->render('article/show.html.twig', $vars);
    }

    /**
     * @Route("/{id}/edit",
     *     name="article_edit",
     *     methods="GET|POST"
     * )
     */
    public function edit(Request $request, Article $article = null): Response
    {
        if (!$article) {
            $this->addFlash('danger', "L'article demandé n'existe pas.");
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $article->setUpdatedAt(new \DateTime());
                $em->persist($article);
                $em->flush();

                $this->addFlash('success', "L'article a bien été modifié.");

                return $this->redirectToRoute('article_show', [
                    'id' => $article->getId()
                ]);
            }
            else {
                $this->addFlash('danger', "Formulaire invalide.");
            }
        }

        // View options
        $vars = ['article' => $article, 'form' => $form->createView()];
        if ($article->getCategory())
            $vars['toolbox']['back'] = $article->getCategory()->getId();

        return $this->render('article/edit.html.twig', $vars);
    }

    /**
     * @Route("/{id}",
     *     name="article_delete",
     *     methods="DELETE"
     * )
     */
    public function delete(Request $request, Article $article = null): Response
    {
        if ($article && $this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();

            $id = null;
            $category = $article->getCategory();
            if ($category)
                $id = $category->getId();

            $em->remove($article);
            $em->flush();

            $this->addFlash('success', "L'article a bien été supprimé.");

            return $this->redirectToRoute('category_show', [
                'id' => $id
            ]);
        }

        $this->addFlash('danger', "L'article demandé n'existe pas.");

        return $this->redirectToRoute('home');
    }
}
