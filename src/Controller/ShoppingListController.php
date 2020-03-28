<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Event;
use App\Entity\ShoppingList;
use App\Form\Type\ShoppingListType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/groceries")
 */
class ShoppingListController extends AbstractController
{
    public function index()
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $adminEmail = $this->getParameter('app.admin_email');
    }

    /**
     * @Route("/list/{id}", name="shopping_list")
     */
    public function shoppingList(ShoppingList $shoppingList)
    {
        return $this->render('groceries/view.html.twig', [
            'shoppingList' => $shoppingList
        ]);
    }

    /**
     * @Route("/request/{id}", name="groceries_request", methods={"GET","POST"})
     */
    public function groceriesRequest(Request $request, Event $event): Response
    {
        $shoppingList = new ShoppingList();
        $shoppingList->setEvent($event);
        $shoppingList->setUser($this->getUser());

        // 10 articles limit
        $article1 = new Article();
        $article1->setName('Article 1 : ');
        $article1->setShoppingList($shoppingList);
        $shoppingList->getArticles()->add($article1);
        /*$article2 = new Article();
        $article2->setName('Article 2 : ');
        $shoppingList->getArticles()->add($article2);
        $article3 = new Article();
        $article3->setName('Article 3 : ');
        $shoppingList->getArticles()->add($article3);
        $article4 = new Article();
        $article4->setName('Article 4 : ');
        $shoppingList->getArticles()->add($article4);
        $article5 = new Article();
        $article5->setName('Article 5 : ');
        $shoppingList->getArticles()->add($article5);
        $article6 = new Article();
        $article6->setName('Article 6 : ');
        $shoppingList->getArticles()->add($article6);
        $article7 = new Article();
        $article7->setName('Article 7 : ');
        $shoppingList->getArticles()->add($article7);
        $article8 = new Article();
        $article8->setName('Article 8 : ');
        $shoppingList->getArticles()->add($article8);*/

        $form = $this->createForm(ShoppingListType::class, $shoppingList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($shoppingList);
            $entityManager->persist($article1);
            /*$entityManager->persist($article2);
            $entityManager->persist($article3);
            $entityManager->persist($article4);
            $entityManager->persist($article5);
            $entityManager->persist($article6);
            $entityManager->persist($article7);
            $entityManager->persist($article8);*/

            $entityManager->flush();

            $this->addFlash('success', 'Enregistré.');

            return $this->redirectToRoute('event_list');
        }

        return $this->render('groceries/create.html.twig', [
            'shoppingList' => $shoppingList,
            'event'        => $event,
            'form'         => $form->createView(),
        ]);
    }

     /**
     * @Route("/{id}/update", name="shopping_list_update", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function shoppingListUpdate(Request $request, ShoppingList $shoppingList)
    {
        $this->denyAccessUnlessGranted('edit', $shoppingList);

        $manager = $this->getDoctrine()->getManager();

        if (null === $shoppingList ){
            throw $this->createNotFoundException('No task found for id '.$shoppingList->getId);
        }

        $originalArticles = new ArrayCollection();

        // Create an ArrayCollection of the current Article objects in the database
        foreach ($shoppingList->getArticles() as $article) {
            $originalArticles->add($article);
        }
    
        $form = $this->createForm(ShoppingListType::class, $shoppingList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($originalArticles as $articletag) {
                if (false === $shoppingList->getArticles()->contains($article)) {
               
                    // if it was a many-to-one relationship, remove the relationship like this
                    $article->setShoppingList(null);
    
                    $manager->persist($article);
    
                    // if you wanted to delete the Tag entirely, you can also do that
                    // $entityManager->remove($tag);
                }
            }
    
            $manager->persist($article);
            $manager->flush();

            return $this->redirectToRoute('shopping_list', ['id' => $shoppingList->getId()]);
        }

        return $this->render(
            "groceries/update.html.twig",
            [
                "form" => $form->createView()
            ]
        );
    }

    /**
     * @Route("/{id}/delete", name="shopping_list_delete", methods={"GET","POST"})
     * @IsGranted("ROLE_USER")
     */
    public function shoppingListDelete(ShoppingList $shoppingList)
    {
        $this->denyAccessUnlessGranted('edit', $shoppingList);

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($shoppingList);
        $manager->flush();

        $this->addFlash("success", "La liste de courses a bien été supprimé");

        return $this->redirectToRoute('event_list');
    }
}
