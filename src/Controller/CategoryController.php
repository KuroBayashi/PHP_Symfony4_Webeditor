<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category")
 */
class CategoryController extends AbstractController
{
    /**
     * @Route("/",
     *     name="category_index",
     *     methods="GET"
     * )
     */
    public function index(CategoryRepository $categoryRepository, ArticleRepository $articleRepository): Response
    {
        return $this->render('category/show.html.twig', [
            'id' => null,
            'articles' => $articleRepository->findBy(['category' => null]),
            'categories' => $categoryRepository->findBy(['parent' => null]),
        ]);
    }

    /**
     * @Route("/new/{id}",
     *     name="category_new",
     *     methods="GET|POST",
     *     defaults={"id": null}
     * )
     */
    public function new(Request $request, Category $parent = null): Response
    {
        $category = new Category();

        if ($parent){
            $category->setParent($parent);
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();

                $this->addFlash('success', "La catégorie a bien été enregistré.");

                return $this->redirectToRoute('category_show', [
                    'id' => $category->getId()
                ]);
            }
            else {
                $this->addFlash('danger', "Formulaire invalide.");
            }
        }

        // View options
        $vars = ['form' => $form->createView()];
        if ($parent)
            $vars['toolbox']['back'] = $parent->getId();

        return $this->render('category/new.html.twig', $vars);
    }

    /**
     * @Route("/{id}",
     *     name="category_show",
     *     methods="GET"
     * )
     */
    public function show(Category $category = null): Response
    {
        if ($category == null) {
            $this->addFlash('danger', "La catégorie demandé n'existe pas.");
            return $this->redirectToRoute("category_index");
        }

        $vars = [
            'id' => $category->getId(),
            'title' => $category->getTitle(),
            'articles' => $category->getArticles(),
            'categories' => $category->getSubCategories()
        ];

        if ($category->getDescription())
            $vars['description'] = $category->getDescription();

        if ($category->getParent())
            $vars['toolbox']['back'] = $category->getParent()->getId();

        return $this->render('category/show.html.twig', $vars);
    }

    /**
     * @Route("/{id}/edit",
     *     name="category_edit",
     *     methods="GET|POST"
     * )
     */
    public function edit(Request $request, Category $category = null): Response
    {
        if (!$category) {
            $this->addFlash('danger', "La catégorie demandé n'existe pas.");
            return $this->redirectToRoute('category_index');
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', "La catégorie a bien été modifié.");

                return $this->redirectToRoute('category_show', [
                    'id' => $category->getId()
                ]);
            }
            else {
                $this->addFlash('danger', "Formulaire invalide.");
            }
        }

        // View options
        $vars = ['category' => $category, 'form' => $form->createView()];
        if ($category->getParent())
            $vars['toolbox']['back'] = $category->getParent()->getId();

        return $this->render('category/edit.html.twig', $vars);
    }

    /**
     * @Route("/{id}",
     *     name="category_delete",
     *     methods="DELETE"
     * )
     */
    public function delete(Request $request, Category $category = null): Response
    {
        if ($category && $this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);

            $id = null;
            $parent = $category->getParent();
            if ($parent)
                $id = $parent->getId();

            try {
                $em->flush();
                $this->addFlash('success', "La catégorie a bien été supprimé.");
            } catch(\Exception $e) {
                $this->addFlash(
                    'error',
                    "Impossible de supprimer cette catégorie car elle possède au moins un article ou une sous-catégorie."
                );
            }

            return $this->redirectToRoute('caegory_show', [
                'id' => $id
            ]);
        }

        $this->addFlash('danger', "La catégorie demandé n'existe pas.");

        return $this->redirectToRoute('category_index');
    }
}
